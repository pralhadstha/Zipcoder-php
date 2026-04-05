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

        if (! is_array($addressItems) || $addressItems === []) {
            throw new NoResult("No results found for postal code '{$query->postalCode}' in JP.");
        }

        $postalCode = is_string($data['postalCode'] ?? null) ? $data['postalCode'] : $code;
        $locale = $this->locale;

        $addresses = [];
        foreach ($addressItems as $item) {
            if (! is_array($item)) {
                continue;
            }
            /** @var array<string, string> $localeData */
            $localeData = is_array($item[$locale] ?? null) ? $item[$locale] : [];

            $addresses[] = new Address(
                postalCode: $postalCode,
                countryCode: 'JP',
                countryName: 'Japan',
                state: $localeData['prefecture'] ?? null,
                stateCode: isset($item['prefectureCode']) && is_string($item['prefectureCode']) ? $item['prefectureCode'] : null,
                city: $localeData['address1'] ?? null,
                district: $localeData['address2'] ?? null,
                provider: $this->getName(),
            );
        }

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'jp-postal-code';
    }
}
