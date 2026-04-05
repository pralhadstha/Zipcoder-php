<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Provider\GeoNames;
use Pralhad\Zipcoder\Query;

final class GeoNamesProviderTest extends TestCase
{
    use MockHttpClientTrait;

    #[Test]
    public function lookup_returns_addresses_from_fixture(): void
    {
        $client = $this->createMockClientFromFixture('geonames_jp_1000001.json');
        $provider = new GeoNames($client, $this->createMockRequestFactory(), 'testuser');

        $result = $provider->lookup(Query::create('100-0001', 'JP'));

        $this->assertCount(1, $result);
        $this->assertFalse($result->isEmpty());
    }

    #[Test]
    public function lookup_maps_fields_correctly(): void
    {
        $client = $this->createMockClientFromFixture('geonames_jp_1000001.json');
        $provider = new GeoNames($client, $this->createMockRequestFactory(), 'testuser');

        $result = $provider->lookup(Query::create('100-0001', 'JP'));
        $address = $result->first();

        $this->assertSame('100-0001', $address->postalCode);
        $this->assertSame('JP', $address->countryCode);
        $this->assertSame('Chiyoda', $address->city);
        $this->assertSame('Tokyo', $address->state);
        $this->assertSame('13', $address->stateCode);
        $this->assertSame(35.6762, $address->latitude);
        $this->assertSame(139.7503, $address->longitude);
        $this->assertSame('geonames', $address->provider);
    }

    #[Test]
    public function throws_no_result_on_empty_postalcodes(): void
    {
        $client = $this->createMockClientFromString('{"postalcodes": []}');
        $provider = new GeoNames($client, $this->createMockRequestFactory(), 'testuser');

        $this->expectException(NoResult::class);
        $provider->lookup(Query::create('99999', 'XX'));
    }

    #[Test]
    public function get_name(): void
    {
        $client = $this->createMockClientFromString('{}');
        $provider = new GeoNames($client, $this->createMockRequestFactory(), 'testuser');

        $this->assertSame('geonames', $provider->getName());
    }
}
