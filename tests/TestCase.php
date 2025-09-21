<?php

declare(strict_types=1);

namespace FluentTransactionBuilder\Tests;

use FluentTransactionBuilder\Facades\TransactionBuilder;
use FluentTransactionBuilder\TransactionBuilderServiceProvider;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    use MockeryPHPUnitIntegration;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            TransactionBuilderServiceProvider::class,
        ];
    }

    /**
     * @return string[]
     */
    protected function getPackageAliases($app): array
    {
        return [
            'FluentTransactionBuilder' => TransactionBuilder::class,
        ];
    }
}
