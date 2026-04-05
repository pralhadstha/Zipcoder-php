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

        if ($postalcodes === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.");
        }

        $addresses = array_map(
            fn (array $item): Address => new Address(
                postalCode: (string) ($item['postalcode'] ?? $query->postalCode),
                countryCode: (string) ($item['countryCode'] ?? $query->countryCode),
                city: $item['placeName'] ?? null,
                state: $item['adminName1'] ?? null,
                stateCode: $item['adminCode1'] ?? null,
                province: $item['adminName2'] ?? null,
                latitude: isset($item['lat']) ? (float) $item['lat'] : null,
                longitude: isset($item['lng']) ? (float) $item['lng'] : null,
                provider: $this->getName(),
            ),
            $postalcodes,
        );

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'geonames';
    }
}
