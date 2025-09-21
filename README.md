# Fluent Transaction Builder

A lightweight fluent wrapper around Laravel `DB::transaction()` with support for retries, on-failure callbacks, and result access.

## Installation

```bash
composer require nurrony/fluent-transaction-builder
```

## Methods
- `build()`: Create a new instance of the FluentTransactionBuilder.
- `attempts(int $attempts)`: The number of attempts to run the transaction. - default is 1.
- `run(callable $callback)`: The callback to be executed within the transaction.
- `onFailure(callable $callback)`: The callback to be executed if the transaction fails after all attempts.
- `disableThrow()`: Disable throwing exceptions on failure. - default is false
- `result()`: Get the result of the transaction. If the transaction fails, it will return null if `disableThrow()` is called, or throw an exception otherwise.

## Usage

```php
$result = FluentTransactionBuilder::build()
    ->attempts(3) // number of attempts
    ->run(function () {
        // your transaction logic
        return 'done';
    })
    ->result();
```

### On Failure Callback

```php
$result = FluentTransactionBuilder::build()
    ->run(function () {
        throw new \Exception("fail");
    })
    ->onFailure(function ($exception) {
        logger()->error($exception->getMessage());
    })
    ->disableThrow() // optional if you want to disable throwing exceptions since you already have onFailure callback
    ->result();
```

### Nested Transactions

```php
$result = FluentTransactionBuilder::build()
    ->run(function () {
        // outer transaction logic

        FluentTransactionBuilder::build()
            ->attempts(2) // number of attempts
            ->run(function () {
                // inner transaction logic
            })
            ->onFailure(function ($exception) {
                logger()->error($exception->getMessage());
            })
            ->result();
    })
    ->result();
```

You can also do this:

```php
$result = FluentTransactionBuilder::build()
    ->run(function () {
        // outer transaction logic

        DB::transaction(function () {
            // inner transaction logic
        });
    })
    ->result();
```

## 💫 Contributing

> **Your contributions are very welcome!** If you'd like to improve this package, simply create a pull request with your changes. Your efforts help enhance its functionality and documentation.

> If you find this package useful, please consider ⭐ it to show your support!

## 📜 License
Transaction Builder for Laravel is an open-sourced software licensed under the **[MIT license](LICENSE)**.
