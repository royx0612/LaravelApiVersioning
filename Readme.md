# Laravel API Versioning

A Laravel Artisan command that helps you quickly scaffold versioned API components, including:

- Controller
- Form Request
- Resource
- Service
- (Optional) Test class
- (Optional) Route
- (Optional) Policy

## Installation

```bash
composer require royx0612/laravel-api-versioning --dev
```

## Configuration

You can publish the config file and stubs:

```bash
php artisan vendor:publish --tag=laravel-api-versioning
```

This will publish:

- `config/versioned.php`
- `stubs/vendor/laravel-api-versioning/`

If you want to customize the stubs, you may modify the files inside `stubs/vendor/laravel-api-versioning/` and update your config path accordingly.

## Usage

```bash
php artisan make:api-controller v1 UserController --with-test --with-route --with-policy
```

This command will create:

- `App\Http\Controllers\Api\V1\UserController`
- `App\Http\Requests\Api\V1\UserRequest`
- `App\Http\Resources\Api\V1\UserResource`
- `App\Services\V1\UserService`
- (Optional) `Tests\Feature\Api\V1\UserTest`
- (Optional) Append route to `routes/api.php`
- (Optional) `App\Policies\V1\UserPolicy`

## Options

| Option          | Description                     |
|-----------------|---------------------------------|
| `--with-test`   | Create a test file              |
| `--with-route`  | Append route automatically      |
| `--with-policy` | Generate a policy file          |
| `--force`       | Overwrite existing files        |

## Custom Stub Path

You can customize stub path in `config/versioned.php`:

```php
'stub_path' => 'stubs/vendor/laravel-api-versioning',
'policy_namespace_prefix' => 'App\Policies',
```

## License

MIT

Author: [royx0612](https://github.com/royx0612)
