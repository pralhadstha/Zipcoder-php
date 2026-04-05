<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Result;

final readonly class Address
{
    public function __construct(
        public string $postalCode,
        public string $countryCode,
        public ?string $countryName = null,
        public ?string $state = null,
        public ?string $stateCode = null,
        public ?string $province = null,
        public ?string $city = null,
        public ?string $district = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public string $provider = '',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            postalCode: is_scalar($data['postalCode'] ?? null) ? (string) $data['postalCode'] : '',
            countryCode: is_scalar($data['countryCode'] ?? null) ? (string) $data['countryCode'] : '',
            countryName: isset($data['countryName']) && is_string($data['countryName']) ? $data['countryName'] : null,
            state: isset($data['state']) && is_string($data['state']) ? $data['state'] : null,
            stateCode: isset($data['stateCode']) && is_string($data['stateCode']) ? $data['stateCode'] : null,
            province: isset($data['province']) && is_string($data['province']) ? $data['province'] : null,
            city: isset($data['city']) && is_string($data['city']) ? $data['city'] : null,
            district: isset($data['district']) && is_string($data['district']) ? $data['district'] : null,
            latitude: is_numeric($data['latitude'] ?? null) ? (float) $data['latitude'] : null,
            longitude: is_numeric($data['longitude'] ?? null) ? (float) $data['longitude'] : null,
            provider: is_scalar($data['provider'] ?? null) ? (string) $data['provider'] : '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'postalCode' => $this->postalCode,
            'countryCode' => $this->countryCode,
            'countryName' => $this->countryName,
            'state' => $this->state,
            'stateCode' => $this->stateCode,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'provider' => $this->provider,
        ];
    }
}
