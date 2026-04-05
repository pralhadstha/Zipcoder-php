<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Provider;

use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Exception\HttpError;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

abstract class AbstractHttpProvider implements Provider
{
    public function __construct(
        protected readonly ClientInterface $client,
        protected readonly RequestFactoryInterface $requestFactory,
    ) {}

    /**
     * Fetch a URL and decode the JSON response.
     *
     * @return array<string, mixed>
     *
     * @throws HttpError
     */
    protected function fetchJson(string $url): array
    {
        try {
            $request = $this->requestFactory->createRequest('GET', $url);
            $response = $this->client->sendRequest($request);

            $body = (string) $response->getBody();
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                throw new HttpError("HTTP {$statusCode} from {$this->getName()}: {$body}");
            }

            $decoded = json_decode($body, true);

            if (! is_array($decoded)) {
                throw new HttpError("Invalid JSON response from {$this->getName()}");
            }

            return $decoded;
        } catch (ClientExceptionInterface $e) {
            throw new HttpError(
                "HTTP request to {$this->getName()} failed: {$e->getMessage()}",
                previous: $e,
            );
        }
    }
}
