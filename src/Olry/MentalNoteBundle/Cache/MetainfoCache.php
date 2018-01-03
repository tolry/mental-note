<?php

namespace AppBundle\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface as Psr16Interface;
use Symfony\Component\Cache\Simple\Psr6Cache;

/**
 * @author Tobias Olry <tobias.olry@gmail.com>
 */
class MetainfoCache
{
    /**
     * @var Psr16Interface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = new Psr6Cache($cache);
    }

    public function set($url, $property, $value)
    {
        if (empty($url)) {
            return;
        }

        $this->cache->set($this->createKey($property, $url), $value);
    }

    public function get($url, $property)
    {
        if (empty($url)) {
            return;
        }

        $this->cache->get($this->createKey($property, $url));
    }

    private function createKey($property, $url)
    {
        return sprintf('metainfo.%s.%s', sha1($url), $property);
    }
}
