<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Http;

use Pralhad\Zipcoder\Exception\HttpError;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

final class CurlPsr18Client implements ClientInterface, RequestFactoryInterface
{
    public function __construct(
        private readonly int $timeout = 10,
        private readonly int $connectTimeout = 5,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $ch = curl_init();

        /** @phpstan-ignore argument.type */
        curl_setopt_array($ch, [
            CURLOPT_URL => (string) $request->getUri(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $headers = [];
        foreach ($request->getHeaders() as $name => $values) {
            $headers[] = $name.': '.implode(', ', $values);
        }

        if ($headers !== []) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new HttpError("cURL error: {$error}");
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        /** @var string $response */
        $responseHeaders = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        $parsedHeaders = $this->parseHeaders($responseHeaders);

        return new SimpleResponse($statusCode, $parsedHeaders, new SimpleStream($body));
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        $uriObject = $uri instanceof UriInterface ? $uri : new SimpleUri((string) $uri);

        return new SimpleRequest($method, $uriObject);
    }

    /**
     * @return array<string, list<string>>
     */
    private function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        $lines = explode("\r\n", trim($rawHeaders));

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $name = trim($name);
                $value = trim($value);
                $headers[$name][] = $value;
            }
        }

        return $headers;
    }
}
