<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder;

use Pralhad\Zipcoder\Exception\InvalidArgument;

final readonly class Query
{
    /**
     * @param  string  $postalCode  The postal code to look up
     * @param  string  $countryCode  ISO 3166-1 alpha-2 country code (e.g., "JP", "US", "NP")
     * @param  string|null  $locale  Optional locale hint (e.g., "en", "ja")
     */
    public function __construct(
        public string $postalCode,
        public string $countryCode,
        public ?string $locale = null,
    ) {
        if ($postalCode === '') {
            throw new InvalidArgument('Postal code cannot be empty.');
        }

        if (strlen($countryCode) !== 2) {
            throw new InvalidArgument('Country code must be a 2-letter ISO 3166-1 alpha-2 code.');
        }
    }

    /**
     * Static factory with auto-uppercase country code.
     */
    public static function create(string $postalCode, string $countryCode, ?string $locale = null): self
    {
        return new self(
            postalCode: $postalCode,
            countryCode: strtoupper($countryCode),
            locale: $locale,
        );
    }

    /**
     * Returns postal code with spaces and hyphens stripped.
     * Useful for APIs that require numeric-only codes.
     */
    public function normalizedPostalCode(): string
    {
        return preg_replace('/[\s\-]/', '', $this->postalCode) ?? $this->postalCode;
    }
}
