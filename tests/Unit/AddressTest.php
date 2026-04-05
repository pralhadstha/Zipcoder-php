<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Result\Address;

final class AddressTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_fields(): void
    {
        $address = new Address(
            postalCode: '90210',
            countryCode: 'US',
            countryName: 'United States',
            state: 'California',
            stateCode: 'CA',
            province: null,
            city: 'Beverly Hills',
            district: null,
            latitude: 34.0901,
            longitude: -118.4065,
            provider: 'zippopotamus',
        );

        $this->assertSame('90210', $address->postalCode);
        $this->assertSame('US', $address->countryCode);
        $this->assertSame('United States', $address->countryName);
        $this->assertSame('California', $address->state);
        $this->assertSame('CA', $address->stateCode);
        $this->assertNull($address->province);
        $this->assertSame('Beverly Hills', $address->city);
        $this->assertNull($address->district);
        $this->assertSame(34.0901, $address->latitude);
        $this->assertSame(-118.4065, $address->longitude);
        $this->assertSame('zippopotamus', $address->provider);
    }

    #[Test]
    public function optional_fields_default_to_null(): void
    {
        $address = new Address(postalCode: '90210', countryCode: 'US');

        $this->assertNull($address->countryName);
        $this->assertNull($address->state);
        $this->assertNull($address->stateCode);
        $this->assertNull($address->province);
        $this->assertNull($address->city);
        $this->assertNull($address->district);
        $this->assertNull($address->latitude);
        $this->assertNull($address->longitude);
        $this->assertSame('', $address->provider);
    }

    #[Test]
    public function from_array_creates_address_with_all_fields(): void
    {
        $data = [
            'postalCode' => '1000014',
            'countryCode' => 'JP',
            'countryName' => 'Japan',
            'state' => 'Tokyo',
            'stateCode' => '13',
            'province' => null,
            'city' => 'Chiyoda',
            'district' => 'Nagatacho',
            'latitude' => 35.6762,
            'longitude' => 139.7503,
            'provider' => 'geonames',
        ];

        $address = Address::fromArray($data);

        $this->assertSame('1000014', $address->postalCode);
        $this->assertSame('JP', $address->countryCode);
        $this->assertSame('Japan', $address->countryName);
        $this->assertSame('Tokyo', $address->state);
        $this->assertSame('13', $address->stateCode);
        $this->assertNull($address->province);
        $this->assertSame('Chiyoda', $address->city);
        $this->assertSame('Nagatacho', $address->district);
        $this->assertSame(35.6762, $address->latitude);
        $this->assertSame(139.7503, $address->longitude);
        $this->assertSame('geonames', $address->provider);
    }

    #[Test]
    public function from_array_handles_missing_optional_fields(): void
    {
        $address = Address::fromArray([
            'postalCode' => '90210',
            'countryCode' => 'US',
        ]);

        $this->assertSame('90210', $address->postalCode);
        $this->assertSame('US', $address->countryCode);
        $this->assertNull($address->countryName);
        $this->assertNull($address->state);
        $this->assertNull($address->latitude);
        $this->assertNull($address->longitude);
        $this->assertSame('', $address->provider);
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $address = new Address(
            postalCode: '90210',
            countryCode: 'US',
            countryName: 'United States',
            state: 'California',
            stateCode: 'CA',
            city: 'Beverly Hills',
            latitude: 34.0901,
            longitude: -118.4065,
            provider: 'zippopotamus',
        );

        $array = $address->toArray();

        $this->assertSame('90210', $array['postalCode']);
        $this->assertSame('US', $array['countryCode']);
        $this->assertSame('United States', $array['countryName']);
        $this->assertSame('California', $array['state']);
        $this->assertSame('CA', $array['stateCode']);
        $this->assertNull($array['province']);
        $this->assertSame('Beverly Hills', $array['city']);
        $this->assertNull($array['district']);
        $this->assertSame(34.0901, $array['latitude']);
        $this->assertSame(-118.4065, $array['longitude']);
        $this->assertSame('zippopotamus', $array['provider']);
    }

    #[Test]
    public function roundtrip_from_array_to_array(): void
    {
        $data = [
            'postalCode' => '1000014',
            'countryCode' => 'JP',
            'countryName' => 'Japan',
            'state' => 'Tokyo',
            'stateCode' => '13',
            'province' => null,
            'city' => 'Chiyoda',
            'district' => 'Nagatacho',
            'latitude' => 35.6762,
            'longitude' => 139.7503,
            'provider' => 'geonames',
        ];

        $address = Address::fromArray($data);

        $this->assertSame($data, $address->toArray());
    }

    #[Test]
    public function latitude_and_longitude_cast_to_float(): void
    {
        $address = Address::fromArray([
            'postalCode' => '90210',
            'countryCode' => 'US',
            'latitude' => '34.0901',
            'longitude' => '-118.4065',
        ]);

        $this->assertIsFloat($address->latitude);
        $this->assertIsFloat($address->longitude);
        $this->assertSame(34.0901, $address->latitude);
        $this->assertSame(-118.4065, $address->longitude);
    }
}
