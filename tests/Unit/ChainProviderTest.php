<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Contract\Provider;
use Pralhad\Zipcoder\Exception\HttpError;
use Pralhad\Zipcoder\Exception\InvalidArgument;
use Pralhad\Zipcoder\Exception\NoResult;
use Pralhad\Zipcoder\Exception\ZipcoderException;
use Pralhad\Zipcoder\Provider\Chain;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\Address;
use Pralhad\Zipcoder\Result\AddressCollection;
use Psr\Log\LoggerInterface;

final class ChainProviderTest extends TestCase
{
    #[Test]
    public function returns_result_from_first_successful_provider(): void
    {
        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);
        $chain = new Chain([
            $this->createSuccessProvider('first', $expected),
            $this->createSuccessProvider('second', AddressCollection::empty()),
        ]);

        $result = $chain->lookup(Query::create('90210', 'US'));

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function falls_back_when_first_throws_no_result(): void
    {
        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);
        $chain = new Chain([
            $this->createFailingProvider('first', new NoResult('No result')),
            $this->createSuccessProvider('second', $expected),
        ]);

        $result = $chain->lookup(Query::create('90210', 'US'));

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function falls_back_when_first_throws_http_error(): void
    {
        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);
        $chain = new Chain([
            $this->createFailingProvider('first', new HttpError('Network error')),
            $this->createSuccessProvider('second', $expected),
        ]);

        $result = $chain->lookup(Query::create('90210', 'US'));

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function skips_empty_collection_to_next(): void
    {
        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);
        $chain = new Chain([
            $this->createSuccessProvider('first', AddressCollection::empty()),
            $this->createSuccessProvider('second', $expected),
        ]);

        $result = $chain->lookup(Query::create('90210', 'US'));

        $this->assertSame($expected, $result);
    }

    #[Test]
    public function throws_no_result_when_all_fail(): void
    {
        $chain = new Chain([
            $this->createFailingProvider('first', new NoResult('No result 1')),
            $this->createFailingProvider('second', new HttpError('Error 2')),
        ]);

        $this->expectException(NoResult::class);
        $chain->lookup(Query::create('00000', 'XX'));
    }

    #[Test]
    public function last_exception_is_set_as_previous(): void
    {
        $lastError = new HttpError('Last error');
        $chain = new Chain([
            $this->createFailingProvider('first', new NoResult('First error')),
            $this->createFailingProvider('second', $lastError),
        ]);

        try {
            $chain->lookup(Query::create('00000', 'XX'));
            $this->fail('Expected NoResult exception');
        } catch (NoResult $e) {
            $this->assertSame($lastError, $e->getPrevious());
        }
    }

    #[Test]
    public function logs_warning_on_provider_failure(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('failing'),
                $this->callback(fn (array $ctx) => $ctx['postalCode'] === '90210' && $ctx['countryCode'] === 'US'),
            );

        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);
        $chain = new Chain([
            $this->createFailingProvider('failing', new NoResult('Nope')),
            $this->createSuccessProvider('success', $expected),
        ], $logger);

        $chain->lookup(Query::create('90210', 'US'));
    }

    #[Test]
    public function logs_debug_on_success(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with(
                $this->stringContains('success'),
                $this->callback(fn (array $ctx) => $ctx['postalCode'] === '90210'),
            );

        $expected = new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')]);
        $chain = new Chain([
            $this->createSuccessProvider('success', $expected),
        ], $logger);

        $chain->lookup(Query::create('90210', 'US'));
    }

    #[Test]
    public function does_not_catch_invalid_argument_exception(): void
    {
        $chain = new Chain([
            $this->createInvalidArgumentProvider('broken'),
            $this->createSuccessProvider('backup', new AddressCollection([new Address(postalCode: '90210', countryCode: 'US')])),
        ]);

        $this->expectException(InvalidArgument::class);
        $chain->lookup(Query::create('90210', 'US'));
    }

    #[Test]
    public function get_name_returns_chain(): void
    {
        $chain = new Chain([
            $this->createSuccessProvider('test', AddressCollection::empty()),
        ]);

        $this->assertSame('chain', $chain->getName());
    }

    #[Test]
    public function empty_providers_throws_invalid_argument(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Chain([]);
    }

    private function createSuccessProvider(string $name, AddressCollection $result): Provider
    {
        return new class($name, $result) implements Provider
        {
            public function __construct(
                private readonly string $name,
                private readonly AddressCollection $result,
            ) {}

            public function lookup(Query $query): AddressCollection
            {
                return $this->result;
            }

            public function getName(): string
            {
                return $this->name;
            }
        };
    }

    private function createFailingProvider(string $name, ZipcoderException $exception): Provider
    {
        return new class($name, $exception) implements Provider
        {
            public function __construct(
                private readonly string $name,
                private readonly ZipcoderException $exception,
            ) {}

            public function lookup(Query $query): AddressCollection
            {
                throw $this->exception;
            }

            public function getName(): string
            {
                return $this->name;
            }
        };
    }

    private function createInvalidArgumentProvider(string $name): Provider
    {
        return new class($name) implements Provider
        {
            public function __construct(
                private readonly string $name,
            ) {}

            public function lookup(Query $query): AddressCollection
            {
                throw new InvalidArgument('Bad input');
            }

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}
