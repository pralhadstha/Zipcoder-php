<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use Pralhad\Zipcoder\Http\SimpleRequest;
use Pralhad\Zipcoder\Http\SimpleResponse;
use Pralhad\Zipcoder\Http\SimpleStream;
use Pralhad\Zipcoder\Http\SimpleUri;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait MockHttpClientTrait
{
    private function createMockClientFromFixture(string $fixtureFile, int $statusCode = 200): ClientInterface
    {
        $body = file_get_contents(__DIR__.'/../Fixtures/'.$fixtureFile);

        return new class($body, $statusCode) implements ClientInterface
        {
            public function __construct(
                private readonly string $body,
                private readonly int $statusCode,
            ) {}

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return new SimpleResponse(
                    $this->statusCode,
                    [],
                    new SimpleStream($this->body),
                );
            }
        };
    }

    private function createMockClientFromString(string $body, int $statusCode = 200): ClientInterface
    {
        return new class($body, $statusCode) implements ClientInterface
        {
            public function __construct(
                private readonly string $body,
                private readonly int $statusCode,
            ) {}

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return new SimpleResponse(
                    $this->statusCode,
                    [],
                    new SimpleStream($this->body),
                );
            }
        };
    }

    private function createMockRequestFactory(): RequestFactoryInterface
    {
        return new class implements RequestFactoryInterface
        {
            public function createRequest(string $method, $uri): RequestInterface
            {
                return new SimpleRequest(
                    $method,
                    new SimpleUri((string) $uri),
                );
            }
        };
    }
}
