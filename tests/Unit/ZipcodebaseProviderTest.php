<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Provider\Zipcodebase;
use Pralhad\Zipcoder\Query;

final class ZipcodebaseProviderTest extends TestCase
{
    use MockHttpClientTrait;

    #[Test]
    public function lookup_returns_addresses_from_fixture(): void
    {
        $client = $this->createMockClientFromFixture('zipcodebase_us_10005.json');
        $provider = new Zipcodebase($client, $this->createMockRequestFactory(), 'test-key');

        $result = $provider->lookup(Query::create('10005', 'US'));

        $this->assertCount(1, $result);
    }

    #[Test]
    public function lookup_maps_fields_correctly(): void
    {
        $client = $this->createMockClientFromFixture('zipcodebase_us_10005.json');
        $provider = new Zipcodebase($client, $this->createMockRequestFactory(), 'test-key');

        $result = $provider->lookup(Query::create('10005', 'US'));
        $address = $result->first();

        $this->assertSame('10005', $address->postalCode);
        $this->assertSame('US', $address->countryCode);
        $this->assertSame('New York City', $address->city);
        $this->assertSame('New York', $address->state);
        $this->assertSame('NY', $address->stateCode);
        $this->assertNull($address->province);
        $this->assertSame(40.7063, $address->latitude);
        $this->assertSame(-74.0089, $address->longitude);
        $this->assertSame('zipcodebase', $address->provider);
    }

    #[Test]
    public function throws_no_result_on_empty_results(): void
    {
        $client = $this->createMockClientFromString('{"results": {}}');
        $provider = new Zipcodebase($client, $this->createMockRequestFactory(), 'test-key');

        $this->expectException(NoResult::class);
        $provider->lookup(Query::create('00000', 'XX'));
    }

    #[Test]
    public function get_name(): void
    {
        $client = $this->createMockClientFromString('{}');
        $provider = new Zipcodebase($client, $this->createMockRequestFactory(), 'test-key');

        $this->assertSame('zipcodebase', $provider->getName());
    }
}
