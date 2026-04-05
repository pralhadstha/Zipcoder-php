<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Provider;

use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class GeoNames extends AbstractHttpProvider
{
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        private readonly string $username,
    ) {
        parent::__construct($client, $requestFactory);
    }

    public function lookup(Query $query): AddressCollection
    {
        $url = sprintf(
            'https://api.geonames.org/postalCodeLookupJSON?postalcode=%s&country=%s&username=%s',
            urlencode($query->normalizedPostalCode()),
            urlencode($query->countryCode),
            urlencode($this->username),
        );

        $data = $this->fetchJson($url);

        $postalcodes = $data['postalcodes'] ?? [];

        if (! is_array($postalcodes) || $postalcodes === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.");
        }

        $addresses = [];
        foreach ($postalcodes as $item) {
            if (! is_array($item)) {
                continue;
            }
            $addresses[] = new Address(
                postalCode: is_scalar($item['postalcode'] ?? null) ? (string) $item['postalcode'] : $query->postalCode,
                countryCode: is_scalar($item['countryCode'] ?? null) ? (string) $item['countryCode'] : $query->countryCode,
                city: isset($item['placeName']) && is_string($item['placeName']) ? $item['placeName'] : null,
                state: isset($item['adminName1']) && is_string($item['adminName1']) ? $item['adminName1'] : null,
                stateCode: isset($item['adminCode1']) && is_string($item['adminCode1']) ? $item['adminCode1'] : null,
                province: isset($item['adminName2']) && is_string($item['adminName2']) ? $item['adminName2'] : null,
                latitude: is_numeric($item['lat'] ?? null) ? (float) $item['lat'] : null,
                longitude: is_numeric($item['lng'] ?? null) ? (float) $item['lng'] : null,
                provider: $this->getName(),
            );
        }

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'geonames';
    }
}
