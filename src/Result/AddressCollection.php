<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Result;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, Address>
 */
final class AddressCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<Address>  $addresses
     */
    public function __construct(
        private readonly array $addresses = [],
    ) {}

    public static function empty(): self
    {
        return new self;
    }

    public function isEmpty(): bool
    {
        return $this->addresses === [];
    }

    public function first(): ?Address
    {
        return $this->addresses[0] ?? null;
    }

    public function count(): int
    {
        return count($this->addresses);
    }

    /**
     * @return ArrayIterator<int, Address>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->addresses);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            fn (Address $address): array => $address->toArray(),
            $this->addresses,
        );
    }
}
