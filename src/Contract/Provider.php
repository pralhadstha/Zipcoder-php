<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Contract;

use Pralhad\Zipcoder\Exception\ZipcoderException;
use Pralhad\Zipcoder\Query;
use Pralhad\Zipcoder\Result\AddressCollection;

interface Provider
{
    /**
     * Look up addresses for a postal code.
     *
     * @throws ZipcoderException
     */
    public function lookup(Query $query): AddressCollection;

    /**
     * Returns the unique name identifier for this provider.
     */
    public function getName(): string;
}
