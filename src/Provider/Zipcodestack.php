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

        if (! is_array($results) || $results === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.");
        }

        $places = $results[$query->postalCode] ?? [];

        if (! is_array($places) || $places === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in {$query->countryCode}.");
        }

        $addresses = [];
        foreach ($places as $item) {
            if (! is_array($item)) {
                continue;
            }
            $addresses[] = new Address(
                postalCode: is_scalar($item['postal_code'] ?? null) ? (string) $item['postal_code'] : $query->postalCode,
                countryCode: is_scalar($item['country_code'] ?? null) ? (string) $item['country_code'] : $query->countryCode,
                city: isset($item['city']) && is_string($item['city']) ? $item['city'] : null,
                state: isset($item['state']) && is_string($item['state']) ? $item['state'] : null,
                province: isset($item['province']) && is_string($item['province']) ? $item['province'] : null,
                latitude: is_numeric($item['latitude'] ?? null) ? (float) $item['latitude'] : null,
                longitude: is_numeric($item['longitude'] ?? null) ? (float) $item['longitude'] : null,
                provider: $this->getName(),
            );
        }

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'zipcodestack';
    }
}
