<?php

declare(strict_types=1);

namespace AppBundle\Factory;

use AppBundle\Cache\MetainfoCache;
use AppBundle\Url\MetaInfo;

/**
 * @author Tobias Olry <tobias.olry@gmail.com>
 */
class MetainfoFactory
{
    private $cache;

    public function __construct(MetainfoCache $cache)
    {
        $this->cache = $cache;
    }

    public function create($url): MetaInfo
    {
        return new MetaInfo($url, $this->cache);
    }
}
