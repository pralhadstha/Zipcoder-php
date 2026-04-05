<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Provider;

use Pralhad\Zipcoder\Exception\HttpError;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class JpPostalCode extends AbstractHttpProvider
{
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        private readonly string $locale = 'en',
    ) {
        parent::__construct($client, $requestFactory);
    }

    public function lookup(Query $query): AddressCollection
    {
        if ($query->countryCode !== 'JP') {
            throw new NoResult("JpPostalCode only supports Japan (JP), got '{$query->countryCode}'.");
        }

        $code = $query->normalizedPostalCode();

        $url = sprintf(
            'https://jp-postal-code-api.ttskch.com/api/v1/%s.json',
            urlencode($code),
        );

        try {
            $data = $this->fetchJson($url);
        } catch (HttpError $e) {
            if (str_contains($e->getMessage(), 'HTTP 404')) {
                throw new NoResult("No results found for postal code '{$query->postalCode}' in JP.", previous: $e);
            }
            throw $e;
        }

        $addressItems = $data['addresses'] ?? [];

        if ($addressItems === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in JP.");
        }

        $postalCode = (string) ($data['postalCode'] ?? $code);

        $addresses = array_map(
            fn (array $item): Address => new Address(
                postalCode: $postalCode,
                countryCode: 'JP',
                countryName: 'Japan',
                state: $item[$this->locale]['prefecture'] ?? null,
                stateCode: $item['prefectureCode'] ?? null,
                city: $item[$this->locale]['address1'] ?? null,
                district: $item[$this->locale]['address2'] ?? null,
                provider: $this->getName(),
            ),
            $addressItems,
        );

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'jp-postal-code';
    }
}
