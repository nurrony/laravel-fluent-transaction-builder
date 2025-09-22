<?php

declare(strict_types=1);

namespace FluentTransactionBuilder\Tests;

use Exception;
use FluentTransactionBuilder\Facades\TransactionBuilder;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class TransactionBuilderTest extends TestCase
{
    #[Test]
    public function test_successful_transaction_returns_result(): void
    {
        /* SETUP */
        $expected = 'test result';
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::on(fn ($callback) => is_callable($callback)), 1)
            ->andReturn($expected);

        /* EXECUTE */
        $transaction = TransactionBuilder::build()->execute(fn () => $expected);

        /* ASSERT */
        $this->assertSame($expected, $transaction->end());
    }

    #[Test]
    public function test_custom_attempts_are_used(): void
    {
        /* SETUP */
        $expected = 42;
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type('Closure'), 5)
            ->andReturn($expected);

        /* EXECUTE */
        $transaction = TransactionBuilder::build()
            ->retry(5)
            ->execute(fn () => $expected);

        /* ASSERT */
        $this->assertSame($expected, $transaction->end());
    }

    #[Test]
    public function test_exception_in_run_will_be_thrown_by_result(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);

        /* EXECUTE */
        $transaction = TransactionBuilder::build()->execute(fn () => null);

        /* ASSERT */
        $this->expectExceptionObject($exception);
        $transaction->end();
    }

    #[Test]
    public function test_disable_throw_suppresses_exception_and_returns_null(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);

        /* EXECUTE */
        $transaction = TransactionBuilder::build()
            ->disableThrow()
            ->execute(fn () => null);

        /* ASSERT */
        $this->assertNull($transaction->end());
    }

    #[Test]
    public function test_on_failure_callback_is_invoked_with_exception_when_disable_throw(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);
        $called = false;
        $caught = null;

        /* EXECUTE */
        TransactionBuilder::build()
            ->execute(fn () => null)
            ->onException(function (Throwable $e) use (&$called, &$caught) {
                $called = true;
                $caught = $e;
            })
            ->disableThrow()
            ->end();

        /* ASSERT */
        $this->assertTrue($called);
        $this->assertSame($exception, $caught, 'test exception');
    }

    #[Test]
    public function test_on_failure_callback_is_invoked_with_exception(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);
        $called = false;
        $caught = null;

        /* EXECUTE */
        TransactionBuilder::build()
            ->execute(fn () => null)
            ->onException(function (Throwable $e) use (&$called, &$caught) {
                $called = true;
                $caught = $e;
            });

        /* ASSERT */
        $this->assertTrue($called);
        $this->assertSame($exception, $caught, 'test exception');
    }

    #[Test]
    public function test_on_failure_not_called_when_no_exception(): void
    {
        /* SETUP */
        $expected = 'test result';
        DB::shouldReceive('transaction')
            ->once()
            ->andReturn($expected);
        $called = false;

        /* EXECUTE */
        TransactionBuilder::build()
            ->execute(fn () => $expected)
            ->onException(fn () => $called = true)
            ->end();

        /* ASSERT */
        $this->assertFalse($called);
    }

    #[Test]
    public function test_run_returns_null_if_callback_returns_null(): void
    {
        /* SETUP */
        DB::shouldReceive('transaction')
            ->once()
            ->andReturn(null);

        /* EXECUTE */
        $transaction = TransactionBuilder::build()->execute(function () {});

        /* ASSERT */
        $this->assertNull($transaction->end());
    }

    #[Test]
    public function test_disable_throw_after_run_suppresses_exception(): void
    {
        /* SETUP */
        $exception = new Exception('test exception');
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($exception);

        /* EXECUTE */
        $transaction = TransactionBuilder::build()
            ->execute(fn () => null)
            ->disableThrow();

        /* ASSERT */
        $this->assertNull($transaction->end());
    }

    #[Test]
    public function test_methods_are_chainable(): void
    {
        /* SETUP */
        $transaction = TransactionBuilder::build();

        /* EXECUTE & ASSERT */
        $this->assertSame($transaction, $transaction->retry(2));
        $this->assertSame($transaction, $transaction->disableThrow());
        $this->assertSame($transaction, $transaction->execute(fn () => 'x'));
        $this->assertSame($transaction, $transaction->onException(fn () => null));
    }

    #[Test]
    public function test_nested_transaction_is_handled_properly(): void
    {
        /* SETUP */
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::on(fn ($callback) => is_callable($callback)), 1)
            ->andReturnUsing(function ($outerCallback) {
                return $outerCallback();
            });
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::on(fn ($callback) => is_callable($callback)), 1)
            ->andReturn('nested');

        /* EXECUTE */
        $transaction = TransactionBuilder::build()->execute(function () {
            return TransactionBuilder::build()->execute(fn () => 'nested')->end();
        });

        /* ASSERT */
        $this->assertSame('nested', $transaction->end());
    }

    #[Test]
    public function test_exception_in_nested_transaction_is_handled(): void
    {
        /* SETUP */
        $ex = new Exception('nested boom');
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($outerCallback) {
                return $outerCallback();
            });
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow($ex);
        $caught = null;

        /* EXECUTE */
        $transaction = TransactionBuilder::build()->execute(function () use (&$caught) {
            return TransactionBuilder::build()
                ->execute(fn () => null)
                ->onException(function (Throwable $e) use (&$caught) {
                    $caught = $e;
                })
                ->disableThrow()
                ->end();
        });

        /* ASSERT */
        $this->assertSame($ex, $caught);
        $this->assertNull($transaction->end());
    }

    #[Test]
    public function test_multiple_transactions_can_be_run(): void
    {
        /* SETUP */
        DB::shouldReceive('transaction')
            ->twice()
            ->andReturn('first', 'second');
        $transaction = TransactionBuilder::build();

        /* EXECUTE */
        $first = $transaction->execute(fn () => 'first')->end();
        $second = $transaction->execute(fn () => 'second')->end();

        /* ASSERT */
        $this->assertSame('first', $first);
        $this->assertSame('second', $second);
    }
}
