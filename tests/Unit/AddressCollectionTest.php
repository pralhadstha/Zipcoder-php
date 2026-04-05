<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;

final class AddressCollectionTest extends TestCase
{
    #[Test]
    public function empty_factory_returns_empty_collection(): void
    {
        $collection = AddressCollection::empty();

        $this->assertCount(0, $collection);
    }

    #[Test]
    public function is_empty_on_empty_collection(): void
    {
        $collection = AddressCollection::empty();

        $this->assertTrue($collection->isEmpty());
    }

    #[Test]
    public function is_empty_on_non_empty_collection(): void
    {
        $collection = new AddressCollection([
            new Address(postalCode: '90210', countryCode: 'US'),
        ]);

        $this->assertFalse($collection->isEmpty());
    }

    #[Test]
    public function first_returns_null_on_empty(): void
    {
        $collection = AddressCollection::empty();

        $this->assertNull($collection->first());
    }

    #[Test]
    public function first_returns_first_address(): void
    {
        $first = new Address(postalCode: '90210', countryCode: 'US');
        $second = new Address(postalCode: '10001', countryCode: 'US');
        $collection = new AddressCollection([$first, $second]);

        $this->assertSame($first, $collection->first());
    }

    #[Test]
    public function count_returns_number_of_addresses(): void
    {
        $collection = new AddressCollection([
            new Address(postalCode: '90210', countryCode: 'US'),
            new Address(postalCode: '10001', countryCode: 'US'),
            new Address(postalCode: '1000014', countryCode: 'JP'),
        ]);

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function iterable_in_foreach(): void
    {
        $addresses = [
            new Address(postalCode: '90210', countryCode: 'US'),
            new Address(postalCode: '10001', countryCode: 'US'),
        ];
        $collection = new AddressCollection($addresses);

        $result = [];
        foreach ($collection as $address) {
            $result[] = $address;
        }

        $this->assertSame($addresses, $result);
    }

    #[Test]
    public function to_array_serializes_all_addresses(): void
    {
        $collection = new AddressCollection([
            new Address(postalCode: '90210', countryCode: 'US', city: 'Beverly Hills'),
            new Address(postalCode: '10001', countryCode: 'US', city: 'New York'),
        ]);

        $array = $collection->toArray();

        $this->assertCount(2, $array);
        $this->assertSame('Beverly Hills', $array[0]['city']);
        $this->assertSame('New York', $array[1]['city']);
    }
}
