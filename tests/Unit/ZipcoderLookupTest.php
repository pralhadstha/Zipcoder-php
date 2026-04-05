<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Exception\ProviderNotRegistered;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;
use Pralhad\Zipcoder\ZipcoderLookup;

final class ZipcoderLookupTest extends TestCase
{
    #[Test]
    public function register_and_lookup_uses_first_provider(): void
    {
        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);
        $provider = $this->createMockProvider('test', $expected);

        $lookup = new ZipcoderLookup;
        $lookup->registerProvider($provider);

        $result = $lookup->lookup(Query::create('90210', 'US'));

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function using_returns_named_provider(): void
    {
        $provider = $this->createMockProvider('geonames', AddressCollection::empty());

        $lookup = new ZipcoderLookup;
        $lookup->registerProvider($provider);

        $this->assertSame($provider, $lookup->using('geonames'));
    }

    #[Test]
    public function using_throws_for_unknown_provider(): void
    {
        $this->expectException(ProviderNotRegistered::class);

        $lookup = new ZipcoderLookup;
        $lookup->using('nonexistent');
    }

    #[Test]
    public function lookup_throws_when_no_providers(): void
    {
        $this->expectException(ProviderNotRegistered::class);

        $lookup = new ZipcoderLookup;
        $lookup->lookup(Query::create('90210', 'US'));
    }

    #[Test]
    public function get_registered_providers(): void
    {
        $lookup = new ZipcoderLookup;
        $lookup->registerProvider($this->createMockProvider('alpha', AddressCollection::empty()));
        $lookup->registerProvider($this->createMockProvider('beta', AddressCollection::empty()));

        $this->assertSame(['alpha', 'beta'], $lookup->getRegisteredProviders());
    }

    private function createMockProvider(string $name, AddressCollection $result): Provider
    {
        return new class($name, $result) implements Provider
        {
            public function __construct(
                private readonly string $name,
                private readonly AddressCollection $result,
            ) {}

            public function lookup(Query $query): AddressCollection
            {
                return $this->result;
            }

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}
