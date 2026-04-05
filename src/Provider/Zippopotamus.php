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

        if (! is_array($places) || $places === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.");
        }

        $postCode = is_string($data['post code'] ?? null) ? $data['post code'] : $query->postalCode;
        $countryCode = is_string($data['country abbreviation'] ?? null) ? $data['country abbreviation'] : $query->countryCode;
        $countryName = isset($data['country']) && is_string($data['country']) ? $data['country'] : null;

        $addresses = [];
        foreach ($places as $place) {
            if (! is_array($place)) {
                continue;
            }
            $addresses[] = new Address(
                postalCode: $postCode,
                countryCode: $countryCode,
                countryName: $countryName,
                city: isset($place['place name']) && is_string($place['place name']) ? $place['place name'] : null,
                state: isset($place['state']) && is_string($place['state']) ? $place['state'] : null,
                stateCode: isset($place['state abbreviation']) && is_string($place['state abbreviation']) ? $place['state abbreviation'] : null,
                latitude: is_numeric($place['latitude'] ?? null) ? (float) $place['latitude'] : null,
                longitude: is_numeric($place['longitude'] ?? null) ? (float) $place['longitude'] : null,
                provider: $this->getName(),
            );
        }

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'zippopotamus';
    }
}
