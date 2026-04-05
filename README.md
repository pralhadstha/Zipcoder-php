# Zipcoder

**A PHP library to convert postal codes and zip codes into structured addresses using multiple geocoding APIs with automatic fallback.**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pralhadstha/zipcoder-php.svg?style=flat-square)](https://packagist.org/packages/pralhadstha/zipcoder-php)
[![PHP Version](https://img.shields.io/packagist/php-v/pralhadstha/zipcoder-php.svg?style=flat-square)](https://packagist.org/packages/pralhadstha/zipcoder-php)
[![License](https://img.shields.io/packagist/l/pralhadstha/zipcoder-php.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/pralhadstha/zipcoder-php.svg?style=flat-square)](https://packagist.org/packages/pralhadstha/zipcoder-php)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat-square)](https://phpstan.org/)

---

Zipcoder turns any postal code into a normalized address — city, state, province, country, and coordinates — by querying postal code lookup APIs behind a unified interface. If one provider fails or has no data, the next one in the chain picks up automatically. No more vendor lock-in to a single zip code API.

**Use cases:** shipping & logistics address autofill, checkout form validation, postal code to city/state resolution, international address lookup, and geocoding from zip codes.

## Key Features

- **5 built-in postal code providers** — GeoNames, Zippopotamus, Zipcodestack, Zipcodebase, and JpPostalCode
- **Automatic fallback** — Chain of Responsibility pattern tries providers in order until one succeeds
- **PSR-16 caching** — decorator wraps any provider to cache results and reduce API calls
- **PSR-18 HTTP** — bring your own HTTP client (Guzzle, Symfony HttpClient, etc.) or use the included zero-dependency curl client
- **Normalized results** — every provider returns the same `Address` structure regardless of the underlying API format
- **100+ countries** — covers postal codes worldwide; Japan-specific provider with English, Japanese, and Kana output
- **Zero required dependencies** — only PSR interfaces; all implementations are optional
- **Extensible** — implement the `Provider` interface to add any postal code API
- **PHP 8.2+** — readonly classes, constructor promotion, strict types throughout
- **PHPStan level 9** — fully statically analyzed

## Supported Providers

| Provider | Countries | Auth Required | Free Tier | Best For |
|----------|-----------|---------------|-----------|----------|
| [GeoNames](https://www.geonames.org/) | 100+ | Free username | 10,000/day | Primary global provider |
| [Zippopotamus](https://zippopotam.us/) | ~60 | None | Unlimited | Quick lookups, zero config |
| [Zipcodestack](https://zipcodestack.com/) | 210+ | API key | 300/month | Broadest country coverage |
| [Zipcodebase](https://zipcodebase.com/) | 100+ | API key | 10,000/month | Good middle-ground |
| [JpPostalCode](https://github.com/ttskch/jp-postal-code-api) | Japan | None | Unlimited | Japan addresses in EN/JA/Kana |

## Requirements

- PHP 8.2 or higher
- `ext-curl` (if using the built-in `CurlPsr18Client`)
- A PSR-18 HTTP client (e.g., Guzzle 7) or use the included curl client

## Installation

```bash
composer require pralhadstha/zipcoder-php
```

For production, Guzzle is recommended as the HTTP client:

```bash
composer require pralhadstha/zipcoder-php guzzlehttp/guzzle
```

## Quick Start

Look up a US zip code and get the city and state in 4 lines:

```php
use Pralhad\Zipcoder\Http\CurlPsr18Client;
use Pralhad\Zipcoder\Provider\Zippopotamus;
use Pralhad\Zipcoder\Query;

$http = new CurlPsr18Client();
$provider = new Zippopotamus($http, $http);

$result = $provider->lookup(Query::create('90210', 'US'));

$address = $result->first();
echo $address->city;      // "Beverly Hills"
echo $address->state;     // "California"
echo $address->stateCode; // "CA"
echo $address->latitude;  // 34.0901
echo $address->longitude; // -118.4065
```

### With Guzzle

```php
use GuzzleHttp\Client;
use Pralhad\Zipcoder\Provider\GeoNames;
use Pralhad\Zipcoder\Query;

$guzzle = new Client(['timeout' => 10]);
$provider = new GeoNames($guzzle, $guzzle, username: 'your_geonames_username');

$result = $provider->lookup(Query::create('100-0001', 'JP'));
echo $result->first()->city; // "Chiyoda"
```

### With Automatic Fallback (Chain)

```php
use Pralhad\Zipcoder\Http\CurlPsr18Client;
use Pralhad\Zipcoder\Provider\Chain;
use Pralhad\Zipcoder\Provider\GeoNames;
use Pralhad\Zipcoder\Provider\JpPostalCode;
use Pralhad\Zipcoder\Provider\Zippopotamus;
use Pralhad\Zipcoder\Query;

$http = new CurlPsr18Client();

$provider = new Chain([
    new JpPostalCode($http, $http),                       // Japan: free, best data
    new Zippopotamus($http, $http),                        // 60 countries: free, fast
    new GeoNames($http, $http, 'your_geonames_username'),  // 100+ countries: free tier
]);

// Japan postal code uses JpPostalCode provider
$result = $provider->lookup(Query::create('100-0014', 'JP'));

// For US zip code, JpPostalCode skips (not JP), Zippopotamus handles it
$result = $provider->lookup(Query::create('90210', 'US'));
```

## Usage

### Creating Queries

A `Query` represents a postal code lookup request. It validates input and normalizes the postal code:

```php
use Pralhad\Zipcoder\Query;

$query = Query::create('90210', 'US');
$query->postalCode;           // "90210"
$query->countryCode;          // "US"
$query->normalizedPostalCode(); // "90210" (strips hyphens and spaces)

// Japanese postal codes with hyphens are normalized
$query = Query::create('100-0014', 'JP');
$query->normalizedPostalCode(); // "1000014"
```

### Working with Results

Every provider returns an `AddressCollection` containing normalized `Address` objects:

```php
$result = $provider->lookup(Query::create('10005', 'US'));

// Access the first result
$address = $result->first();
$address->postalCode;   // "10005"
$address->countryCode;  // "US"
$address->countryName;  // "United States" (if available)
$address->city;         // "New York City"
$address->state;        // "New York"
$address->stateCode;    // "NY"
$address->province;     // Province (if available)
$address->district;     // District (if available)
$address->latitude;     // 40.7063
$address->longitude;    // -74.0089
$address->provider;     // "zipcodebase" (which provider returned this)

// Iterate all results
foreach ($result as $address) {
    echo "{$address->city}, {$address->state}\n";
}

// Collection helpers
$result->count();    // Number of addresses
$result->isEmpty();  // true if no results
$result->toArray();  // Convert all addresses to arrays
```

### Providers

<details>
<summary><strong>GeoNames</strong> — 100+ countries, free username registration</summary>

```php
use Pralhad\Zipcoder\Provider\GeoNames;

// Register at https://www.geonames.org/login to get a free username
$provider = new GeoNames($httpClient, $requestFactory, username: 'your_username');
$result = $provider->lookup(Query::create('100-0001', 'JP'));
```

Returns: `postalCode`, `countryCode`, `city`, `state`, `stateCode`, `province`, `latitude`, `longitude`
</details>

<details>
<summary><strong>Zippopotamus</strong> — ~60 countries, no authentication needed</summary>

```php
use Pralhad\Zipcoder\Provider\Zippopotamus;

// No API key or username needed
$provider = new Zippopotamus($httpClient, $requestFactory);
$result = $provider->lookup(Query::create('90210', 'US'));
```

Returns: `postalCode`, `countryCode`, `countryName`, `city`, `state`, `stateCode`, `latitude`, `longitude`
</details>

<details>
<summary><strong>Zipcodestack</strong> — 210+ countries, broadest coverage</summary>

```php
use Pralhad\Zipcoder\Provider\Zipcodestack;

// Get an API key at https://zipcodestack.com
$provider = new Zipcodestack($httpClient, $requestFactory, apiKey: 'your_api_key');
$result = $provider->lookup(Query::create('44600', 'NP'));
```

Returns: `postalCode`, `countryCode`, `city`, `state`, `province`, `latitude`, `longitude`
</details>

<details>
<summary><strong>Zipcodebase</strong> — 100+ countries, 10k requests/month free</summary>

```php
use Pralhad\Zipcoder\Provider\Zipcodebase;

// Get an API key at https://zipcodebase.com
$provider = new Zipcodebase($httpClient, $requestFactory, apiKey: 'your_api_key');
$result = $provider->lookup(Query::create('10005', 'US'));
```

Returns: `postalCode`, `countryCode`, `city`, `state`, `stateCode`, `province`, `latitude`, `longitude`
</details>

<details>
<summary><strong>JpPostalCode</strong> — Japan-only, free, supports English/Japanese/Kana</summary>

```php
use Pralhad\Zipcoder\Provider\JpPostalCode;

// English output (default)
$provider = new JpPostalCode($httpClient, $requestFactory, locale: 'en');
$result = $provider->lookup(Query::create('100-0014', 'JP'));
$result->first()->city; // "Chiyoda-ku"

// Japanese output
$provider = new JpPostalCode($httpClient, $requestFactory, locale: 'ja');
$result = $provider->lookup(Query::create('100-0014', 'JP'));
$result->first()->city; // "千代田区"

// Kana output
$provider = new JpPostalCode($httpClient, $requestFactory, locale: 'kana');
$result = $provider->lookup(Query::create('100-0014', 'JP'));
$result->first()->city; // "チヨダク"
```

Returns: `postalCode`, `countryCode` (JP), `countryName` (Japan), `state`, `stateCode`, `city`, `district`

> Automatically skips non-JP queries in a chain — no API call is made.
</details>

### Chain Provider (Automatic Fallback)

The `Chain` provider implements the Chain of Responsibility pattern. It tries each provider in order and returns the first successful result. If a provider throws an error or returns no data, it moves to the next one.

```php
use Pralhad\Zipcoder\Provider\Chain;
use Psr\Log\LoggerInterface;

$chain = new Chain(
    providers: [
        new JpPostalCode($http, $http),
        new Zippopotamus($http, $http),
        new GeoNames($http, $http, 'username'),
        new Zipcodebase($http, $http, 'api_key'),
        new Zipcodestack($http, $http, 'api_key'),
    ],
    logger: $psrLogger, // Optional PSR-3 logger for debugging fallback behavior
);

$result = $chain->lookup(Query::create('44600', 'NP'));
```

**How fallback works:**

1. Each provider is tried in the order given
2. If a provider returns addresses, that result is returned immediately
3. If a provider throws `NoResult` or `HttpError`, the chain logs a warning and continues
4. If a provider returns an empty collection, the chain skips to the next
5. `InvalidArgument` exceptions (programming errors) are **not** caught — they bubble up
6. If all providers fail, a `NoResult` exception is thrown

### Cache Provider (PSR-16)

The `Cache` decorator wraps any provider with PSR-16 caching to avoid redundant API calls:

```php
use Pralhad\Zipcoder\Provider\Cache;
use Psr\SimpleCache\CacheInterface;

// Wrap any provider (or a chain) with caching
$cached = new Cache(
    provider: $chain,
    cache: $psrCache,    // Any PSR-16 cache (Laravel, Symfony, php-cache, etc.)
    ttl: 86400,          // Cache for 24 hours (default)
);

// First call: hits the API, stores result in cache
$result = $cached->lookup(Query::create('90210', 'US'));

// Second call: returns from cache, no API call
$result = $cached->lookup(Query::create('90210', 'US'));
```

Cache key format: `zipcoder:{COUNTRY_CODE}:{NORMALIZED_CODE}` (e.g., `zipcoder:US:90210`, `zipcoder:JP:1000014`)

### ZipcoderLookup (Provider Registry)

`ZipcoderLookup` is a convenience aggregator to register and access providers by name:

```php
use Pralhad\Zipcoder\ZipcoderLookup;

$zipcoder = new ZipcoderLookup();
$zipcoder->registerProvider($cachedChain);
$zipcoder->registerProvider(new Zippopotamus($http, $http));

// Use the first registered provider
$result = $zipcoder->lookup(Query::create('90210', 'US'));

// Use a specific provider by name
$result = $zipcoder->using('zippopotamus')->lookup(Query::create('90210', 'US'));

// List registered providers
$zipcoder->getRegisteredProviders(); // ['cache(chain)', 'zippopotamus']
```

### Full Production Example

```php
use Pralhad\Zipcoder\Http\CurlPsr18Client;
use Pralhad\Zipcoder\Provider\Cache;
use Pralhad\Zipcoder\Provider\Chain;
use Pralhad\Zipcoder\Provider\GeoNames;
use Pralhad\Zipcoder\Provider\JpPostalCode;
use Pralhad\Zipcoder\Provider\Zipcodebase;
use Pralhad\Zipcoder\Provider\Zipcodestack;
use Pralhad\Zipcoder\Provider\Zippopotamus;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\ZipcoderLookup;

$http = new CurlPsr18Client(timeout: 10);

$chain = new Chain([
    new JpPostalCode($http, $http),
    new Zippopotamus($http, $http),
    new GeoNames($http, $http, 'your_username'),
    new Zipcodebase($http, $http, 'your_api_key'),
    new Zipcodestack($http, $http, 'your_api_key'),
]);

$provider = new Cache($chain, $yourPsr16Cache, ttl: 3600);

$zipcoder = new ZipcoderLookup();
$zipcoder->registerProvider($provider);

$result = $zipcoder->lookup(Query::create('100-0014', 'JP'));
$address = $result->first();

echo "{$address->city}, {$address->state}, {$address->countryCode}";
// "Chiyoda-ku, Tokyo, JP"
```

## Creating a Custom Provider

Implement the `Provider` interface or extend `AbstractHttpProvider` to integrate any postal code API:

```php
use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Provider\AbstractHttpProvider;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;

final class MyApiProvider extends AbstractHttpProvider
{
    public function lookup(Query $query): AddressCollection
    {
        $url = "https://my-api.com/lookup?code={$query->postalCode}&country={$query->countryCode}";
        $data = $this->fetchJson($url);

        $addresses = array_map(
            fn (array $item) => new Address(
                postalCode: $query->postalCode,
                countryCode: $query->countryCode,
                city: $item['city'] ?? null,
                state: $item['region'] ?? null,
                latitude: isset($item['lat']) ? (float) $item['lat'] : null,
                longitude: isset($item['lng']) ? (float) $item['lng'] : null,
                provider: $this->getName(),
            ),
            $data['results'] ?? [],
        );

        return new AddressCollection($addresses);
    }

    public function getName(): string
    {
        return 'my-api';
    }
}
```

Then add it to your chain:

```php
$chain = new Chain([
    new MyApiProvider($http, $http),
    new GeoNames($http, $http, 'username'),
]);
```

## Error Handling

Zipcoder uses a clear exception hierarchy:

| Exception | When | Caught by Chain? |
|-----------|------|------------------|
| `NoResult` | No address found for the postal code | Yes (falls back) |
| `HttpError` | Network failure or HTTP error | Yes (falls back) |
| `ProviderNotRegistered` | Unknown provider name in `ZipcoderLookup::using()` | No |
| `InvalidArgument` | Invalid input (empty postal code, bad country code) | No (programming error) |

```php
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Exception\HttpError;
use Pralhad\Zipcoder\Exception\InvalidArgument;

try {
    $result = $provider->lookup(Query::create('99999', 'XX'));
} catch (NoResult $e) {
    // No provider could resolve this postal code
    echo "Not found: {$e->getMessage()}";
} catch (HttpError $e) {
    // All providers had network/HTTP errors
    echo "Service error: {$e->getMessage()}";
} catch (InvalidArgument $e) {
    // Bad input — fix the query
    echo "Invalid input: {$e->getMessage()}";
}
```

## HTTP Client Options

Zipcoder accepts any PSR-18 compatible HTTP client. The first constructor argument is the `ClientInterface`, the second is the `RequestFactoryInterface`. Many clients (Guzzle 7, the included curl client) implement both.

| Client | Install | Example |
|--------|---------|---------|
| Built-in curl | Included | `new CurlPsr18Client(timeout: 10)` |
| Guzzle 7 | `composer require guzzlehttp/guzzle` | `new \GuzzleHttp\Client(['timeout' => 10])` |
| Symfony HttpClient | `composer require symfony/http-client` | PSR-18 adapter |

## Testing

```bash
# Run unit tests (mocked HTTP, no API calls)
vendor/bin/phpunit --testsuite Unit

# Run static analysis
vendor/bin/phpstan analyse

# Format code
vendor/bin/pint
```

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-provider`)
3. Write tests for any new functionality
4. Ensure all tests pass (`vendor/bin/phpunit`)
5. Run static analysis (`vendor/bin/phpstan analyse`)
6. Format your code (`vendor/bin/pint`)
7. Submit a pull request

## License

Zipcoder is open-sourced software licensed under the [MIT License](LICENSE).

Copyright (c) 2026 Pralhad Kumar Shrestha
