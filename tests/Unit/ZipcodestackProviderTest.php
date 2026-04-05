<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Provider\Zipcodestack;
use Pralhad\Zipcoder\Query;

final class ZipcodestackProviderTest extends TestCase
{
    use MockHttpClientTrait;

    #[Test]
    public function lookup_returns_addresses_from_fixture(): void
    {
        $client = $this->createMockClientFromFixture('zipcodestack_np_44600.json');
        $provider = new Zipcodestack($client, $this->createMockRequestFactory(), 'test-key');

        $result = $provider->lookup(Query::create('44600', 'NP'));

        $this->assertCount(1, $result);
    }

    #[Test]
    public function lookup_maps_fields_correctly(): void
    {
        $client = $this->createMockClientFromFixture('zipcodestack_np_44600.json');
        $provider = new Zipcodestack($client, $this->createMockRequestFactory(), 'test-key');

        $result = $provider->lookup(Query::create('44600', 'NP'));
        $address = $result->first();

        $this->assertSame('44600', $address->postalCode);
        $this->assertSame('NP', $address->countryCode);
        $this->assertSame('Lalitpur', $address->city);
        $this->assertSame('Bagmati', $address->state);
        $this->assertSame('Central', $address->province);
        $this->assertSame(27.6717, $address->latitude);
        $this->assertSame(85.4298, $address->longitude);
        $this->assertSame('zipcodestack', $address->provider);
    }

    #[Test]
    public function throws_no_result_on_empty_results(): void
    {
        $client = $this->createMockClientFromString('{"results": {}}');
        $provider = new Zipcodestack($client, $this->createMockRequestFactory(), 'test-key');

        $this->expectException(NoResult::class);
        $provider->lookup(Query::create('00000', 'XX'));
    }

    #[Test]
    public function get_name(): void
    {
        $client = $this->createMockClientFromString('{}');
        $provider = new Zipcodestack($client, $this->createMockRequestFactory(), 'test-key');

        $this->assertSame('zipcodestack', $provider->getName());
    }
}
