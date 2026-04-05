<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Http;

use Psr\Http\Message\StreamInterface;

/**
 * @internal
 */
final class SimpleStream implements StreamInterface
{
    public function __construct(
        private readonly string $content,
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }

    public function close(): void {}

    public function detach()
    {
        return null;
    }

    public function getSize(): int
    {
        return strlen($this->content);
    }

    public function tell(): int
    {
        return 0;
    }

    public function eof(): bool
    {
        return true;
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void {}

    public function rewind(): void {}

    public function isWritable(): bool
    {
        return false;
    }

    public function write(string $string): int
    {
        return 0;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read(int $length): string
    {
        return substr($this->content, 0, $length);
    }

    public function getContents(): string
    {
        return $this->content;
    }

    public function getMetadata(?string $key = null): mixed
    {
        return null;
    }
}
