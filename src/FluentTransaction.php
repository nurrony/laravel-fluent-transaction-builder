<?php

declare(strict_types=1);

namespace FluentTransactionBuilder;

use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Transaction class to handle database transactions with retry attempts,
 * on-failure callbacks, and result retrieval.
 *
 * @class Transaction
 */
final class FluentTransaction
{
    protected int $attempts = 1;

    protected bool $shouldThrow = true;

    protected ?Closure $callback = null;

    protected ?Closure $onException = null;

    protected mixed $result = null;

    protected ?Throwable $exception = null;

    /**
     * Create a new instance of the FluentTransaction class.
     */
    public static function build(): self
    {
        return new self;
    }

    /**
     * Set the number of attempts for the transaction and return the instance.
     *
     * @param  int  $attempts  attemps of try database transactions
     * @return $this
     */
    public function attempts(int $attempts): self
    {
        $this->attempts = $attempts;

        return $this;
    }

    /**
     * Set the callback to be executed within the transaction.
     *
     * @param  Closure(): self  $callback
     * @return $this
     */
    public function run(?Closure $callback = null): self
    {
        $this->callback = $callback;

        // Execute the callback within a transaction and in case of failure, save the exception.
        try {
            $this->result = DB::transaction($callback, $this->attempts);
        } catch (Throwable $e) {
            $this->exception = $e;
        }

        return $this;
    }

    /**
     * Set the callback to be executed on failure (If we caught an exception in the run method).
     *
     * @param  Closure(): void  $onException
     * @return $this
     */
    public function onException(Closure $onException): self
    {
        $this->onException = $onException;

        // The caught exception is passed to the onException callback as function($e) { ... } so that you can handle/use it.
        if ($this->exception) {
            $onException($this->exception);
        }

        return $this;
    }

    /**
     * Set the shouldThrow flag to false, preventing the exception from being thrown.
     *
     * @return $this
     */
    public function disableThrow(): self
    {
        $this->shouldThrow = false;

        return $this;
    }

    /**
     * Get the result of the transaction or throw the exception if any exception was occurred.
     *
     * @throws Throwable
     */
    public function result(): mixed
    {
        // If an exception was caught and the shouldThrow flag is set to true, throw the exception.
        if ($this->exception && $this->shouldThrow) {
            throw $this->exception;
        }

        return $this->result;
    }
}
