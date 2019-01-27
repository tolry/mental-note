<?php

declare(strict_types=1);
// @author Tobias Olry <tobias.olry@web.de>

namespace AppBundle\Url;

use AppBundle\Cache\MetainfoCache;
use AppBundle\Entity\Category;
use Guzzle\Common\Event;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Service\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;

class MetaInfo
{
    private $info;
    private $headers;
    private $cache;
    private $imageUrl;

    private static $videoDomains = [
        'youtube',
        'vimeo',
        'netflix',
        'myvideo',
        'clipfish',
    ];

    private static $imageDomains = [
        'imgur',
        'flickr',
        '500px',
        'fotocommunity',
        'deviantart',
    ];

    private static $musicDomains = [
        'bandcamp',
        'spotify',
        'myspace',
        'itun',
        'itunes',
        'soundcloud',
    ];

    private static $purchaseDomains = [
        'amazon',
        'ebay',
        'gearbest',
        'aliexpress',
        'zalando',
        'tchibo',
        'etsy',
    ];

    private static $noGoogleUserAgent = [
        'medium',
    ];

    public function __construct(string $url, MetainfoCache $cache)
    {
        $url = $this->translate($url);

        $this->cache = $cache;
        $this->info = new Info($url);
    }

    /**
     * @return bool|string
     */
    public function getImageUrl()
    {
        if ($this->imageUrl !== null) {
            return $this->imageUrl;
        }

        if ($this->isImage()) {
            $this->imageUrl = $this->info->url;

            return $this->info->url;
        }

        $xpaths = [
            '//meta[@property="og:image"]/@content',
            '//meta[@property="twitter:image"]/@content',
            '//link[@rel="image_src"]/@href',
            '//*[@id="comic"]//img/@src',
            '//img[@id="cover-img"]/@src',
        ];

        foreach ($xpaths as $xpath) {
            $result = $this->getXpath($xpath);
            if (!empty($result)) {
                $this->imageUrl = $result;

                return $result;
            }
        }

        return false;
    }

    public function getTitle(): ?string
    {
        return $this->getXpath('//head/title');
    }

    public function getDefaultCategory(): string
    {
        if (in_array($this->info->sld, self::$videoDomains, true)) {
            return Category::WATCH;
        }

        if (in_array($this->info->sld, self::$imageDomains, true)) {
            return Category::LOOK_AT;
        }

        if (in_array($this->info->sld, self::$musicDomains, true)) {
            return Category::LISTEN;
        }

        if (in_array($this->info->sld, self::$purchaseDomains, true)) {
            return Category::PURCHASE;
        }

        return Category::READ;
    }

    public function isImage(): bool
    {
        if (in_array($this->info->fileExtension, ['jpeg', 'jpg', 'png', 'gif'], true)) {
            return true;
        }

        return stripos($this->getHeader('content_type', ''), 'image/') === 0;
    }

    public function isHtml(): bool
    {
        return stripos($this->getHeader('content_type', ''), 'text/html') === 0;
    }

    protected function getXpath(string $xpath): ?string
    {
        if (!$this->isHtml()) {
            return null;
        }

        try {
            $crawler = new Crawler($this->fetchHtml($this->info->url));

            return trim($crawler->filterXPath($xpath)->first()->text());
        } catch (\Exception $e) {
            return null;
        }
    }

    private function translate(string $url): string
    {
        $info = new Info($url);
        if ($info->host === 'i.imgur.com') {
            $newPath = str_replace('.' . $info->fileExtension, '', $info->path);

            return 'http://imgur.com' . $newPath;
        }

        return $url;
    }

    private function fetchHtml(string $url): ?string
    {
        $html = $this->cache->get($url, 'html');

        if ($html) {
            return $html;
        }

        $guzzle = new GuzzleClient($url);
        $guzzle->setUserAgent(
            $this->getUserAgent(
                $guzzle->getDefaultUserAgent()
            )
        );

        $guzzle->getEventDispatcher()->addListener(
            'request.error',
            function(Event $event): void {
                $event->stopPropagation();
            }
        );

        $response = $guzzle->get()->send();
        if (!$response->isSuccessful()) {
            return null;
        }

        $html = $response->getBody(true);
        $this->cache->set($url, 'html', $html);
        $this->cache->set($url, 'headers', $response->getInfo());

        return $html;
    }

    private function getHeader(string $key, $default = null): ?string
    {
        if (empty($this->headers)) {
            $this->headers = $this->fetchHeaders();
        }

        return isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }

    private function fetchHeaders(): array
    {
        $headers = $this->cache->get($this->info->url, 'headers');

        if ($headers) {
            return $headers;
        }

        $guzzle = new GuzzleClient($this->info->url);
        $guzzle->setUserAgent($this->getUserAgent($guzzle->getDefaultUserAgent()));
        $guzzle->getEventDispatcher()
            ->addListener(
                'request.error',
                function(Event $event): void {
                    $event->stopPropagation();
                }
            );

        try {
            $response = $guzzle
                ->head(null, null, ['timeout' => 3])
                ->send();
        } catch (CurlException $e) {
            $response = false;
        }

        if (!$response || !$response->isSuccessful()) {
            $response = $guzzle->get()->send();
        }

        if ($response->isSuccessful()) {
            $headers = $response->getInfo();
            $this->cache->set($this->info->url, 'headers', $headers);

            $html = $response->getBody(true);
            if (!empty($html)) {
                $this->cache->set($this->info->url, 'html', $html);
            }

            return $headers;
        }

        return [];
    }

    private function getUserAgent(string $defaultUserAgent): string
    {
        if (in_array($this->info->sld, self::$noGoogleUserAgent, true)) {
            return $defaultUserAgent;
        }

        return 'Mozilla/5.0 (compatible; MentalNoteBot/1.0; +https://github.com/tolry/mental-note/';
    }
}
