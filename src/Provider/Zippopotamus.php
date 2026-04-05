<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Provider;

use Pralhad\Zipcoder\Exception\HttpError;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;

final class Zippopotamus extends AbstractHttpProvider
{
    public function lookup(Query $query): AddressCollection
    {
        $url = sprintf(
            'https://api.zippopotam.us/%s/%s',
            urlencode(strtolower($query->countryCode)),
            urlencode($query->postalCode),
        );

        try {
            $data = $this->fetchJson($url);
        } catch (HttpError $e) {
            if (str_contains($e->getMessage(), 'HTTP 404')) {
                throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.", previous: $e);
            }
            throw $e;
        }

        $places = $data['places'] ?? [];

        if ($places === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.");
        }

        $postCode = (string) ($data['post code'] ?? $query->postalCode);
        $countryCode = (string) ($data['country abbreviation'] ?? $query->countryCode);
        $countryName = $data['country'] ?? null;

        $addresses = array_map(
            fn (array $place): Address => new Address(
                postalCode: $postCode,
                countryCode: $countryCode,
                countryName: $countryName,
                city: $place['place name'] ?? null,
                state: $place['state'] ?? null,
                stateCode: $place['state abbreviation'] ?? null,
                latitude: isset($place['latitude']) ? (float) $place['latitude'] : null,
                longitude: isset($place['longitude']) ? (float) $place['longitude'] : null,
                provider: $this->getName(),
            ),
            $places,
        );

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'zippopotamus';
    }
}
