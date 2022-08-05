<?php

namespace App\Tests\Url;

use App\Cache\MetainfoCache;
use App\Entity\Category;
use App\Url\MetaInfo;
use PHPUnit\Framework\TestCase;

/**
 * @author Tobias Olry <tobias.olry@gmail.com>
 */
class MetaInfoTest extends TestCase
{
    public function testDefaultCategory()
    {
        $metaInfoCache = $this->createMock(MetainfoCache::class);

        $testData = [
            'https://www.some-domain.de/foo' => Category::READ,
            'https://www.vimeo.com/foo' => Category::WATCH,
            'https://some.multi.subdomain.vimeo.com/foo' => Category::WATCH,
            'https://www.flickr.de/even-wrong-tld.jpg' => Category::LOOK_AT,
            'https://www.spotify.com/foo?with-query#handHashbang' => Category::LISTEN,
            'https://amazon.de/nosubdomain' => Category::PURCHASE,
        ];

        foreach ($testData as $url => $category) {
            $metaInfo = new MetaInfo($url, $metaInfoCache);
            $this->assertEquals($metaInfo->getDefaultCategory(), $category);
        }
    }
}
