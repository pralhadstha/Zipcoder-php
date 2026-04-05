<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Provider\Zippopotamus;
use Pralhad\Zipcoder\Query;

final class ZippopotamusProviderTest extends TestCase
{
    use MockHttpClientTrait;

    #[Test]
    public function lookup_returns_addresses_from_fixture(): void
    {
        $client = $this->createMockClientFromFixture('zippopotamus_us_90210.json');
        $provider = new Zippopotamus($client, $this->createMockRequestFactory());

        $result = $provider->lookup(Query::create('90210', 'US'));

        $this->assertCount(1, $result);
    }

    #[Test]
    public function lookup_maps_fields_correctly(): void
    {
        $client = $this->createMockClientFromFixture('zippopotamus_us_90210.json');
        $provider = new Zippopotamus($client, $this->createMockRequestFactory());

        $result = $provider->lookup(Query::create('90210', 'US'));
        $address = $result->first();

        $this->assertSame('90210', $address->postalCode);
        $this->assertSame('US', $address->countryCode);
        $this->assertSame('United States', $address->countryName);
        $this->assertSame('Beverly Hills', $address->city);
        $this->assertSame('California', $address->state);
        $this->assertSame('CA', $address->stateCode);
        $this->assertSame(34.0901, $address->latitude);
        $this->assertSame(-118.4065, $address->longitude);
        $this->assertSame('zippopotamus', $address->provider);
    }

    #[Test]
    public function throws_no_result_on_404(): void
    {
        $client = $this->createMockClientFromString('{}', 404);
        $provider = new Zippopotamus($client, $this->createMockRequestFactory());

        $this->expectException(NoResult::class);
        $provider->lookup(Query::create('00000', 'XX'));
    }

    #[Test]
    public function get_name(): void
    {
        $client = $this->createMockClientFromString('{}');
        $provider = new Zippopotamus($client, $this->createMockRequestFactory());

        $this->assertSame('zippopotamus', $provider->getName());
    }
}
