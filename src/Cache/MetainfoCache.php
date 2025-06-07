<?php

declare(strict_types=1);

namespace App\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * @author Tobias Olry <tobias.olry@gmail.com>
 */
class MetainfoCache
{
    public function __construct(private readonly CacheItemPoolInterface $cache)
    {
    }

    public function set(string $url, string $property, $value): void
    {
        if (empty($url)) {
            return;
        }

        if (is_array($value)) {
            $value = array_filter(
                $value,
                static fn (mixed $item): bool => !is_callable($item),
            );
        }

        $item = $this->cache->getItem($this->createKey($property, $url));
        $item->set($value);

        $this->cache->save($item);
    }

    public function get(string $url, string $property)
    {
        if (empty($url)) {
            return null;
        }

        $item = $this->cache->getItem($this->createKey($property, $url));

        return $item->isHit() ? $item->get() : null;
    }

    private function createKey(string $property, string $url)
    {
        return sprintf('metainfo.%s.%s', sha1($url), $property);
    }
}
