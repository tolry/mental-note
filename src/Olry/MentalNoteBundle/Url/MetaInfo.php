<?php
/*
 *
 * @author Tobias Olry <tobias.olry@web.de>
 */

namespace Olry\MentalNoteBundle\Url;

use Symfony\Component\DomCrawler\Crawler;
use Olry\MentalNoteBundle\Entity\Category;

class MetaInfo
{
    private $info;

    private $html;

    private $imageUrl = null;

    private static $videoDomains = array(
        'youtube',
        'vimeo',
        'netflix',
        'myvideo',
        'clipfish',
    );

    private static $imageDomains = array(
        'imgur',
        'flickr',
        '500px',
        'fotocommunity',
        'deviantart',
    );

    private static $musicDomains = array(
        'bandcamp',
        'spotify',
        'myspace',
        'itun',
        'itunes',
    );

    private static $purchaseDomains = array(
        'amazon',
        'ebay',
        'gearbest',
        'aliexpress',
        'zalando',
        'tchibo',
    );

    private function translate($url)
    {
        $info = new Info($url);
        if ($info->host == 'i.imgur.com') {
            $newPath = str_replace('.' . $info->fileExtension, '', $info->path);

            return "http://imgur.com" . $newPath;
        }

        return $url;
    }

    public function __construct($url)
    {
        $url = $this->translate($url);

        $this->info = new Info($url);
        if ($this->info->isHtml()) {
            $this->html = file_get_contents($url);
        }
    }

    protected function getXpath($xpath)
    {
        if (!$this->info->isHtml()) {
            return null;
        }

        try {
            $crawler = new Crawler($this->html);

            return trim($crawler->filterXPath($xpath)->first()->text());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        if ($this->imageUrl !== null) {
            return $this->imageUrl;
        }

        if ($this->info->isImage()) {
            return $this->info->url;
        }

        $xpaths = array(
            '//meta[@property="og:image"]/@content',
            '//meta[@property="twitter:image"]/@content',
            '//link[@rel="image_src"]/@href',
            '//*[@id="comic"]//img/@src',
        );

        foreach ($xpaths as $xpath) {
            $result = $this->getXpath($xpath);
            if (! empty($result)) {
                return $result;
            }
        }

        return false;
    }

    public function getTitle()
    {
        return $this->getXpath('//head/title');
    }

    public function getDefaultCategory()
    {
        if (in_array($this->info->sld, self::$videoDomains)) {
            return Category::WATCH;
        }

        if (in_array($this->info->sld, self::$imageDomains)) {
            return Category::LOOK_AT;
        }

        if (in_array($this->info->sld, self::$musicDomains)) {
            return Category::LISTEN;
        }

        if (in_array($this->info->sld, self::$purchaseDomains)) {
            return Category::PURCHASE;
        }

        return Category::READ;
    }

    /**
     * Get info.
     *
     * @return Info
     */
    public function getInfo()
    {
        return $this->info;
    }
}
