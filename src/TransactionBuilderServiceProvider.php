<?php

declare(strict_types=1);

namespace FluentTransactionBuilder;

use FluentTransactionBuilder\Facades\TransactionBuilder;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for FluentTransactionBuilder
 *
 * @class TransactionBuilderServiceProvider
 */
final class TransactionBuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}

    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
         * When Facade is called, it will return an instance of FluentTransactionBuilder.
         */
        $this->app->bind('fluent-transaction-builder', function () {
            return new FluentTransaction;
        });

        /*
         * Register the facade alias.
         */
        AliasLoader::getInstance()->alias('FluentTransactionBuilder', TransactionBuilder::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return ['fluent-transaction-builder'];
    }
}
