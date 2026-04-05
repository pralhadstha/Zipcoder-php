<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Provider;

use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Exception\ZipcoderException;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\AddressCollection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Chain implements Provider
{
    /**
     * @param  list<Provider>  $providers
     */
    public function __construct(
        private readonly array $providers,
        private readonly LoggerInterface $logger = new NullLogger,
    ) {
        if ($providers === []) {
            throw new \InvalidArgumentException('Chain requires at least one provider.');
        }
    }

    public function lookup(Query $query): AddressCollection
    {
        $lastException = null;

        foreach ($this->providers as $provider) {
            try {
                $result = $provider->lookup($query);

                if (! $result->isEmpty()) {
                    $this->logger->debug("PostalCode lookup succeeded with {$provider->getName()}", [
                        'postalCode' => $query->postalCode,
                        'countryCode' => $query->countryCode,
                    ]);

                    return $result;
                }
            } catch (ZipcoderException $e) {
                $lastException = $e;
                $this->logger->warning("PostalCode provider {$provider->getName()} failed", [
                    'postalCode' => $query->postalCode,
                    'countryCode' => $query->countryCode,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw new NoResult(
            "No provider could resolve postal code {$query->postalCode} for {$query->countryCode}.",
            previous: $lastException,
        );
    }

    public function getName(): string
    {
        return 'chain';
    }
}
