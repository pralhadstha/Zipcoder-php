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
            postalCode: (string) ($data['postalCode'] ?? ''),
            countryCode: (string) ($data['countryCode'] ?? ''),
            countryName: $data['countryName'] ?? null,
            state: $data['state'] ?? null,
            stateCode: $data['stateCode'] ?? null,
            province: $data['province'] ?? null,
            city: $data['city'] ?? null,
            district: $data['district'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            provider: (string) ($data['provider'] ?? ''),
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
