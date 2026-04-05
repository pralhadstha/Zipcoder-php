<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Provider;

use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\AddressCollection;
use Psr\SimpleCache\CacheInterface;

final class Cache implements Provider
{
    public function __construct(
        private readonly Provider $provider,
        private readonly CacheInterface $cache,
        private readonly int $ttl = 86400,
    ) {}

    public function lookup(Query $query): AddressCollection
    {
        $key = 'zipcoder:'.strtoupper($query->countryCode).':'.$query->normalizedPostalCode();

        $cached = $this->cache->get($key);

        if ($cached instanceof AddressCollection) {
            return $cached;
        }

        $result = $this->provider->lookup($query);

        $this->cache->set($key, $result, $this->ttl);

        return $result;
    }

    public function getName(): string
    {
        return 'cache('.$this->provider->getName().')';
    }
}
