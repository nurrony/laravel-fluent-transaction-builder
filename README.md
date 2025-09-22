# Laravel Fluent Transaction Builder

[![Coverage Status](https://coveralls.io/repos/github/nurrony/laravel-fluent-transaction-builder/badge.svg?branch=main)](https://coveralls.io/github/nurrony/laravel-fluent-transaction-builder?branch=main)

A lightweight fluent wrapper around Laravel `DB::transaction()` with support for retries, on-exception callbacks, and result access.

## Installation

Add this following in your `composer.json` until it is ready to release

```json
{
  ...
  "repositories": [
    {
      "type": "package",
      "package": {
        "name": "nurrony/laravel-fluent-transaction-builder",
        "version": "1.0.0-alpha1",
        "source": {
          "url": "https://github.com/nurrony/laravel-fluent-transaction-builder.git",
          "type": "git",
          "reference": "main"
        }
      }
    }
  ],
  "require": {
    "nurrony/laravel-fluent-transaction-builder": "1.0.0-alpha1"
  },
  ...
}

```

## Methods
- `build()`: Create a new instance of the FluentTransactionBuilder.
- `retry(int $retryCount)`: The number of retry count to run the transaction. - default is 1.
- `execute(callable $callback)`: The callback to be executed within the transaction.
- `onException(callable $callback)`: The callback to be executed if the transaction fails after all retry.
- `disableThrow()`: Disable throwing exceptions on failure. - default is false
- `end()`: Get the result of the transaction. If the transaction fails, it will return `null` if `disableThrow()` is called, or `throw an exception` otherwise.

## How to use it

```php
$result = FluentTransactionBuilder::build()
    ->retry(3) // number of retry
    ->execute(function () {
        // your transaction logic
        return 'done';
    })
    ->end();
```

### On Exception Callback

```php
$result = FluentTransactionBuilder::build()
    ->execute(function () {
        throw new \Exception("fail");
    })
    ->onException(function ($exception) {
        logger()->error($exception->getMessage());
    })
    ->disableThrow() // optional if you want to disable throwing exceptions since you already have onException callback
    ->end();
```

### Nested Transactions

```php
$result = FluentTransactionBuilder::build()
    ->execute(function () {
        // outer transaction logic

        FluentTransactionBuilder::build()
            ->retry(2)
            ->execute(function () {
                // inner transaction logic
            }) ->onException(function ($exception) {
                logger()->error($exception->getMessage());
            })->end();
    })->end();
```

You can also do this:

```php
$result = FluentTransactionBuilder::build()
    ->execute(function () {
        // outer transaction logic

        DB::transaction(function () {
            // inner transaction logic
        });
    })
    ->end();
```

## 🫵 Contributing

> **Your contributions are very welcome!** If you'd like to improve this package, simply create a pull request with your changes. Your efforts help enhance its functionality and documentation.

> If you find this package useful, please consider ⭐ it to show your support!

## 📜 License
Fluent Transaction Builder for Laravel is an open-sourced software licensed under the **[MIT license](LICENSE)**.
