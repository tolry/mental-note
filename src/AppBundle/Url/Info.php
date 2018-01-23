<?php

declare(strict_types = 1);
// @author Tobias Olry <tobias.olry@web.de>

namespace AppBundle\Url;

class Info
{
    public $url;

    public $scheme;
    public $host;
    public $port;
    public $user;
    public $pass;
    public $path;
    public $query = [];
    public $fragment;

    public $fileExtension;

    public $tld;
    public $sld;
    public $subdomain;

    public function __construct(string $url)
    {
        $this->url = $url;

        $urlInfo = parse_url($url);

        if (!$urlInfo) {
            throw new \Exception("could not parse url ${url}");
        }

        $urlInfo = array_merge([
            'scheme' => null,
            'host' => null,
            'port' => null,
            'user' => null,
            'pass' => null,
            'path' => null,
            'query' => null,
            'fragment' => null,
        ], $urlInfo);

        $this->scheme = $urlInfo['scheme'];
        $this->host = $urlInfo['host'];
        $this->port = $urlInfo['port'];
        $this->user = $urlInfo['user'];
        $this->pass = $urlInfo['pass'];
        $this->path = $urlInfo['path'];
        if (!empty($urlInfo['query'])) {
            parse_str($urlInfo['query'], $this->query);
        }
        $this->fragment = $urlInfo['fragment'];

        if (!empty($this->path)) {
            $pathParts = explode('.', $this->path);
            if (count($pathParts) > 1) {
                $this->fileExtension = array_pop($pathParts);
            }
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

    public function getDomain(): string
    {
        return $this->sld . '.' . $this->tld;
    }
}
