<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Provider;

use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class Zipcodestack extends AbstractHttpProvider
{
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        private readonly string $apiKey,
    ) {
        parent::__construct($client, $requestFactory);
    }

    public function lookup(Query $query): AddressCollection
    {
        $url = sprintf(
            'https://app.zipcodestack.com/v1/search?codes=%s&country=%s&apikey=%s',
            urlencode($query->postalCode),
            urlencode($query->countryCode),
            urlencode($this->apiKey),
        );

        $data = $this->fetchJson($url);

        $results = $data['results'] ?? [];

        if ($results === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.");
        }

        $places = $results[$query->postalCode] ?? [];

        if ($places === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.");
        }

        $addresses = array_map(
            fn (array $item): Address => new Address(
                postalCode: (string) ($item['postal_code'] ?? $query->postalCode),
                countryCode: (string) ($item['country_code'] ?? $query->countryCode),
                city: $item['city'] ?? null,
                state: $item['state'] ?? null,
                province: $item['province'] ?? null,
                latitude: isset($item['latitude']) ? (float) $item['latitude'] : null,
                longitude: isset($item['longitude']) ? (float) $item['longitude'] : null,
                provider: $this->getName(),
            ),
            $places,
        );

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'zipcodestack';
    }
}
