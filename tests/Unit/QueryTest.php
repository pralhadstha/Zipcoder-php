<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Pralhad\Zipcoder\Exception\InvalidArgument;
use Pralhad\Zipcoder\Query;

final class QueryTest extends TestCase
{
    #[Test]
    public function create_sets_postal_code_and_country_code(): void
    {
        $query = Query::create('90210', 'US');

        $this->assertSame('90210', $query->postalCode);
        $this->assertSame('US', $query->countryCode);
    }

    #[Test]
    public function create_uppercases_country_code(): void
    {
        $query = Query::create('90210', 'us');

        $this->assertSame('US', $query->countryCode);
    }

    #[Test]
    public function empty_postal_code_throws_invalid_argument(): void
    {
        $this->expectException(InvalidArgument::class);

        Query::create('', 'US');
    }

    #[Test]
    public function single_char_country_code_throws_invalid_argument(): void
    {
        $this->expectException(InvalidArgument::class);

        Query::create('90210', 'U');
    }

    #[Test]
    public function three_char_country_code_throws_invalid_argument(): void
    {
        $this->expectException(InvalidArgument::class);

        Query::create('90210', 'USA');
    }

    #[Test]
    public function normalized_postal_code_strips_hyphens(): void
    {
        $query = Query::create('100-0014', 'JP');

        $this->assertSame('1000014', $query->normalizedPostalCode());
    }

    #[Test]
    public function normalized_postal_code_strips_spaces(): void
    {
        $query = Query::create('SW1A 1AA', 'GB');

        $this->assertSame('SW1A1AA', $query->normalizedPostalCode());
    }

    #[Test]
    public function locale_defaults_to_null(): void
    {
        $query = Query::create('90210', 'US');

        $this->assertNull($query->locale);
    }

    #[Test]
    public function locale_can_be_set(): void
    {
        $query = Query::create('100-0014', 'JP', 'ja');

        $this->assertSame('ja', $query->locale);
    }
}
