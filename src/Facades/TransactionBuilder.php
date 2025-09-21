<?php

declare(strict_types=1);

namespace FluentTransactionBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for FluentTransactionBuilder.
 *
 * @method static Transaction build() - Creates a new instance of the Transaction class so that you can chain methods on it.
 *
 * @class FluentTransactionBuilder
 */
final class TransactionBuilder extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'fluent-transaction-builder';
    }
}
