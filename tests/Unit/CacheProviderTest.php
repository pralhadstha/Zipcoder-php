<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Provider\Cache;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;
use Psr\SimpleCache\CacheInterface;

final class CacheProviderTest extends TestCase
{
    #[Test]
    public function returns_cached_result_on_hit(): void
    {
        $cached = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn($cached);

        $provider = $this->createMock(Provider::class);
        $provider->expects($this->never())->method('lookup');
        $provider->method('getName')->willReturn('test');

        $cacheProvider = new Cache($provider, $cache);
        $result = $cacheProvider->lookup(Query::create('90210', 'US'));

        $this->assertSame($cached, $result);
    }

    #[Test]
    public function delegates_to_provider_on_cache_miss(): void
    {
        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);
        $cache->method('set')->willReturn(true);

        $provider = $this->createMock(Provider::class);
        $provider->expects($this->once())->method('lookup')->willReturn($expected);
        $provider->method('getName')->willReturn('test');

        $cacheProvider = new Cache($provider, $cache);
        $result = $cacheProvider->lookup(Query::create('90210', 'US'));

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function stores_result_in_cache_after_miss(): void
    {
        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);
        $cache->expects($this->once())
            ->method('set')
            ->with('zipcoder:US:90210', $expected, 86400)
            ->willReturn(true);

        $provider = $this->createMock(Provider::class);
        $provider->method('lookup')->willReturn($expected);
        $provider->method('getName')->willReturn('test');

        $cacheProvider = new Cache($provider, $cache);
        $cacheProvider->lookup(Query::create('90210', 'US'));
    }

    #[Test]
    public function cache_key_format(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->with('zipcoder:JP:1000014')
            ->willReturn(null);
        $cache->method('set')->willReturn(true);

        $provider = $this->createMock(Provider::class);
        $provider->method('lookup')->willReturn(AddressCollection::empty());
        $provider->method('getName')->willReturn('test');

        $cacheProvider = new Cache($provider, $cache);
        $cacheProvider->lookup(Query::create('100-0014', 'JP'));
    }

    #[Test]
    public function custom_ttl_is_passed(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturn(null);
        $cache->expects($this->once())
            ->method('set')
            ->with($this->anything(), $this->anything(), 3600)
            ->willReturn(true);

        $provider = $this->createMock(Provider::class);
        $provider->method('lookup')->willReturn(AddressCollection::empty());
        $provider->method('getName')->willReturn('test');

        $cacheProvider = new Cache($provider, $cache, ttl: 3600);
        $cacheProvider->lookup(Query::create('90210', 'US'));
    }

    #[Test]
    public function get_name_includes_wrapped_provider(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $provider = $this->createMock(Provider::class);
        $provider->method('getName')->willReturn('geonames');

        $cacheProvider = new Cache($provider, $cache);

        $this->assertSame('cache(geonames)', $cacheProvider->getName());
    }
}
