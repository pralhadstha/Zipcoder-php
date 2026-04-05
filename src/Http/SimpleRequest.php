<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
final class SimpleRequest implements RequestInterface
{
    /** @var array<string, list<string>> */
    private array $headers = [];

    private StreamInterface $body;

    public function __construct(
        private readonly string $method,
        private readonly UriInterface $uri,
    ) {
        $this->body = new SimpleStream('');
    }

    public function getRequestTarget(): string
    {
        $target = $this->uri->getPath();
        $query = $this->uri->getQuery();

        if ($query !== '') {
            $target .= '?'.$query;
        }

        return $target ?: '/';
    }

    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        return clone $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): RequestInterface
    {
        return clone $this;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        return clone $this;
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
        $clone = clone $this;
        $clone->headers[strtolower($name)] = is_array($value) ? array_values($value) : [$value];

        return $clone;
    }

    public function withAddedHeader(string $name, $value): static
    {
        $clone = clone $this;
        $existing = $clone->headers[strtolower($name)] ?? [];
        $clone->headers[strtolower($name)] = array_values(array_merge($existing, is_array($value) ? $value : [$value]));

        return $clone;
    }

    public function withoutHeader(string $name): static
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);

        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): static
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
}
