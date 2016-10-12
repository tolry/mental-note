<?php
/*
 *
 * @author Tobias Olry <tobias.olry@web.de>
 */


namespace Olry\MentalNoteBundle\Url;

use Guzzle\Service\Client as GuzzleClient;
use Guzzle\Http\Message\BadResponseException;
use Guzzle\Common\Event;

class Info
{
    public $url;

    public $scheme;
    public $host;
    public $port;
    public $user;
    public $pass;
    public $path;
    public $query = array();
    public $fragment;

    public $fileExtension;

    public $tld;
    public $sld;
    public $subdomain;

    private $info;

    public function __construct($url)
    {
        $this->url = $url;

        $urlInfo = parse_url($url);

        if (!$urlInfo) {
            throw new \Exception("could not parse url $url");
        }

        $urlInfo = array_merge(array(
            'scheme' => null,
            'host' => null,
            'port' => null,
            'user' => null,
            'pass' => null,
            'path' => null,
            'query' => null,
            'fragment' => null,
        ), $urlInfo);

        $this->scheme = $urlInfo['scheme'];
        $this->host = $urlInfo['host'];
        $this->port = $urlInfo['port'];
        $this->user = $urlInfo['user'];
        $this->pass = $urlInfo['pass'];
        $this->path = $urlInfo['path'];
        parse_str($urlInfo['query'], $this->query);
        $this->fragment = $urlInfo['fragment'];

        $pathParts = explode('.', $this->path);
        if (count($pathParts) > 1) {
            $this->fileExtension = array_pop($pathParts);
        }

        $hostParts = explode('.', $this->host);
        $this->tld = array_pop($hostParts);
        if (!empty($hostParts)) {
            $this->sld = array_pop($hostParts);
        }
        if (!empty($hostParts)) {
            $this->subdomain = array_pop($hostParts);
        }

    }

    public function isImage()
    {
        if (in_array($this->fileExtension, array('jpeg', 'jpg', 'png', 'gif'))) {
            return true;
        }

        if (stripos($this->getInfo('content_type'), 'image/') === 0) {
            return true;
        }

        return false;
    }

    public function isHtml()
    {
        if (stripos($this->getInfo('content_type'), 'text/html') === 0) {
            return true;
        }

        return false;
    }

    public function getDomain()
    {
        return  $this->sld . '.' . $this->tld;
    }

    public function getInfo($key, $default = null)
    {
        if (empty($this->info)) {
            $this->info = array();

            $guzzle = new GuzzleClient($this->url);
            $guzzle->getEventDispatcher()->addListener('request.error', function(Event $event) {
                $event->stopPropagation();
            });

            $response = $guzzle->head()->send();
            if (!$response->isSuccessful()) {
                $response = $guzzle->get()->send();
            }

            if ($response->isSuccessful()) {
                $this->info = $response->getInfo();
            }
        }

        return isset($this->info[$key]) ? $this->info[$key] : $default;
    }
}
