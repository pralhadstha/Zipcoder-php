<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder;

use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Exception\ProviderNotRegistered;
use Pralhad\Zipcoder\Result\AddressCollection;

final class ZipcoderLookup
{
    /** @var array<string, Provider> */
    private array $providers = [];

    public function registerProvider(Provider $provider): self
    {
        $this->providers[$provider->getName()] = $provider;

        return $this;
    }

    /**
     * Get a specific provider by name.
     *
     * @throws ProviderNotRegistered
     */
    public function using(string $providerName): Provider
    {
        if (! isset($this->providers[$providerName])) {
            throw new ProviderNotRegistered("Provider '{$providerName}' is not registered.");
        }

        return $this->providers[$providerName];
    }

    /**
     * Look up using the first registered provider (typically chain).
     *
     * @throws ProviderNotRegistered
     */
    public function lookup(Query $query): AddressCollection
    {
        $provider = reset($this->providers);

        if ($provider === false) {
            throw new ProviderNotRegistered('No providers are registered.');
        }

        return $provider->lookup($query);
    }

    /**
     * @return list<string>
     */
    public function getRegisteredProviders(): array
    {
        return array_keys($this->providers);
    }
}
