<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Provider\JpPostalCode;
use Pralhad\Zipcoder\Query;

final class JpPostalCodeProviderTest extends TestCase
{
    use MockHttpClientTrait;

    #[Test]
    public function lookup_returns_addresses_from_fixture(): void
    {
        $client = $this->createMockClientFromFixture('jppostalcode_jp_1000014.json');
        $provider = new JpPostalCode($client, $this->createMockRequestFactory());

        $result = $provider->lookup(Query::create('100-0014', 'JP'));

        $this->assertCount(1, $result);
    }

    #[Test]
    public function lookup_maps_english_fields_correctly(): void
    {
        $client = $this->createMockClientFromFixture('jppostalcode_jp_1000014.json');
        $provider = new JpPostalCode($client, $this->createMockRequestFactory(), 'en');

        $result = $provider->lookup(Query::create('100-0014', 'JP'));
        $address = $result->first();

        $this->assertSame('1000014', $address->postalCode);
        $this->assertSame('JP', $address->countryCode);
        $this->assertSame('Japan', $address->countryName);
        $this->assertSame('Tokyo', $address->state);
        $this->assertSame('13', $address->stateCode);
        $this->assertSame('Chiyoda-ku', $address->city);
        $this->assertSame('Nagatacho', $address->district);
        $this->assertSame('jp-postal-code', $address->provider);
    }

    #[Test]
    public function throws_no_result_for_non_jp_country(): void
    {
        $client = $this->createMockClientFromString('{}');
        $provider = new JpPostalCode($client, $this->createMockRequestFactory());

        $this->expectException(NoResult::class);
        $provider->lookup(Query::create('90210', 'US'));
    }

    #[Test]
    public function normalizes_postal_code(): void
    {
        $client = $this->createMockClientFromFixture('jppostalcode_jp_1000014.json');
        $provider = new JpPostalCode($client, $this->createMockRequestFactory());

        $result = $provider->lookup(Query::create('100-0014', 'JP'));
        $address = $result->first();

        $this->assertSame('1000014', $address->postalCode);
    }

    #[Test]
    public function get_name(): void
    {
        $client = $this->createMockClientFromString('{}');
        $provider = new JpPostalCode($client, $this->createMockRequestFactory());

        $this->assertSame('jp-postal-code', $provider->getName());
    }
}
