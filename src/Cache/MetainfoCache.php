<?php

declare(strict_types=1);

namespace App\Cache;

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

    public function set(string $url, string $property, $value): void
    {
        if (empty($url)) {
            return;
        }

        $this->cache->set($this->createKey($property, $url), $value);
    }

    public function get(string $url, string $property)
    {
        if (empty($url)) {
            return;
        }

        return $this->cache->get($this->createKey($property, $url));
    }

    private function createKey(string $property, string $url)
    {
        return sprintf('metainfo.%s.%s', sha1($url), $property);
    }
}
