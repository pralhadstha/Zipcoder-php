<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Http;

use Psr\Http\Message\UriInterface;

/**
 * @internal
 */
final class SimpleUri implements UriInterface
{
    /** @var array<string, int|string|null> */
    private readonly array $parts;

    public function __construct(
        private readonly string $uri,
    ) {
        $this->parts = parse_url($uri) ?: [];
    }

    public function __toString(): string
    {
        return $this->uri;
    }

    public function getScheme(): string
    {
        return (string) ($this->parts['scheme'] ?? '');
    }

    public function getAuthority(): string
    {
        $host = $this->getHost();
        if ($host === '') {
            return '';
        }

        $authority = $host;
        $userInfo = $this->getUserInfo();
        if ($userInfo !== '') {
            $authority = $userInfo.'@'.$authority;
        }

        $port = $this->getPort();
        if ($port !== null) {
            $authority .= ':'.$port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return (string) ($this->parts['user'] ?? '');
    }

    public function getHost(): string
    {
        return (string) ($this->parts['host'] ?? '');
    }

    public function getPort(): ?int
    {
        return isset($this->parts['port']) ? (int) $this->parts['port'] : null;
    }

    public function getPath(): string
    {
        return (string) ($this->parts['path'] ?? '');
    }

    public function getQuery(): string
    {
        return (string) ($this->parts['query'] ?? '');
    }

    public function getFragment(): string
    {
        return (string) ($this->parts['fragment'] ?? '');
    }

    public function withScheme(string $scheme): UriInterface
    {
        return clone $this;
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        return clone $this;
    }

    public function withHost(string $host): UriInterface
    {
        return clone $this;
    }

    public function withPort(?int $port): UriInterface
    {
        return clone $this;
    }

    public function withPath(string $path): UriInterface
    {
        return clone $this;
    }

    public function withQuery(string $query): UriInterface
    {
        return clone $this;
    }

    public function withFragment(string $fragment): UriInterface
    {
        return clone $this;
    }
}
