<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
final class SimpleResponse implements ResponseInterface
{
    /**
     * @param  array<string, list<string>>  $headers
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly array $headers,
        private readonly StreamInterface $body,
    ) {}

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        return clone $this;
    }

    public function getReasonPhrase(): string
    {
        return '';
    }

    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    public function withProtocolVersion(string $version): static
    {
        return clone $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    public function withHeader(string $name, $value): static
    {
        return clone $this;
    }

    public function withAddedHeader(string $name, $value): static
    {
        return clone $this;
    }

    public function withoutHeader(string $name): static
    {
        return clone $this;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): static
    {
        return clone $this;
    }
}
