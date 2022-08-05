<?php

declare(strict_types=1);
// @author Tobias Olry <tobias.olry@web.de>

namespace App\Url;

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

        if (empty($urlInfo['host'])) {
            throw new \Exception("url '${url}' without host, could not parse");
        }

        $this->scheme = $urlInfo['scheme'];
        $this->host = $urlInfo['host'];
        $this->port = $urlInfo['port'];
        $this->user = $urlInfo['user'];
        $this->pass = $urlInfo['pass'];
        $this->path = $urlInfo['path'];
        $this->query = $this->parseQuery($urlInfo['query']);
        $this->fragment = $urlInfo['fragment'];
        $this->fileExtension = $this->parseFileExtension($this->path);

        [$this->tld, $this->sld, $this->subdomain] = $this->parseDomains($this->host);
    }

    public function getDomain(): string
    {
        return $this->sld . '.' . $this->tld;
    }

    private function parseQuery(?string $query): array
    {
        if (empty($query)) {
            return [];
        }

        parse_str($query, $queryArray);

        return $queryArray;
    }

    private function parseFileExtension(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $pathParts = explode('.', $path);
        if (count($pathParts) === 0) {
            return null;
        }

        return array_pop($pathParts);
    }

    private function parseDomains(string $host): array
    {
        $sld = $subdomain = null;
        $parts = explode('.', $host);

        $tld = array_pop($parts);
        if (!empty($parts)) {
            $sld = array_pop($parts);
        }
        if (!empty($parts)) {
            $subdomain = array_pop($parts);
        }

        return [$tld, $sld, $subdomain];
    }
}
