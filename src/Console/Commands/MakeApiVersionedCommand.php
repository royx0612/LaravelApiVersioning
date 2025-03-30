<?php

namespace Royx0612\LaravelApiVersioning\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeApiVersionedCommand extends Command
{
    protected $signature = 'make:api-controller {version} {name} {--with-test} {--with-route} {--with-policy} {--force}';
    protected $description = '建立版本化 API Controller、Request、Resource、Service 架構，並可選擇建立測試與路由';

    public function handle()
    {
        $version = Str::studly($this->argument('version'));
        $name = Str::studly($this->argument('name'));
        $fs = new Filesystem();

        $this->createController($fs, $version, $name);
        $this->createRequest($fs, $version, $name);
        $this->createResource($fs, $version, $name);
        $this->createService($fs, $version, $name);

        if ($this->option('with-test')) {
            $this->createTest($fs, $version, $name);
        }

        if ($this->option('with-route')) {
            $this->appendRoute($fs, $version, $name);
        }

        if ($this->option('with-policy')) {
            $this->createPolicy($fs, $version, $name);
        }
    }

    protected function getStubPath(string $type): string
    {
        // 首先檢查專案內的自定義 stub 路徑
        $customPath = base_path(config('versioned.stub_path', 'stubs/versioned') . "/api-{$type}.stub");

        // 如果自定義路徑不存在，使用套件內建的 stub 路徑
        if (!file_exists($customPath)) {
            return base_path("/vendor/royx0612/laravel-api-versioning/src/stubs/versioned/api-{$type}.stub");
        }

        return $customPath;
    }

    protected function createController(Filesystem $fs, string $version, string $name): void
    {
        $namespace = "App\\Http\\Controllers\\Api\\{$version}";
        $path = app_path("Http/Controllers/Api/{$version}/{$name}.php");

        if (!$this->option('force') && $fs->exists($path)) {
            $this->warn("[SKIP] Controller 已存在：{$path}");
            return;
        }

        $fs->ensureDirectoryExists(dirname($path));

        $stubPath = $this->getStubPath('controller');
        if (!$fs->exists($stubPath)) {
            $this->error("[ERROR] Controller stub 檔案不存在：{$stubPath}");
            return;
        }

        $stub = $fs->get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ name }}', '{{ version }}'],
            [$namespace, $name, $version],
            $stub
        );

        $fs->put($path, $stub);
        $this->info("[OK] Controller 建立完成：{$path}");
    }

    protected function createRequest(Filesystem $fs, string $version, string $name): void
    {
        $class = "{$name}Request";
        $namespace = "App\\Http\\Requests\\Api\\{$version}";
        $path = app_path("Http/Requests/Api/{$version}/{$class}.php");

        if (!$this->option('force') && $fs->exists($path)) {
            $this->warn("[SKIP] Request 已存在：{$path}");
            return;
        }

        $fs->ensureDirectoryExists(dirname($path));

        $stubPath = $this->getStubPath('request');
        if (!$fs->exists($stubPath)) {
            $this->error("[ERROR] Request stub 檔案不存在：{$stubPath}");
            return;
        }

        $stub = $fs->get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $class],
            $stub
        );

        $fs->put($path, $stub);
        $this->info("[OK] Request 建立完成：{$path}");
    }

    protected function createResource(Filesystem $fs, string $version, string $name): void
    {
        $class = "{$name}Resource";
        $namespace = "App\\Http\\Resources\\Api\\{$version}";
        $path = app_path("Http/Resources/Api/{$version}/{$class}.php");

        if (!$this->option('force') && $fs->exists($path)) {
            $this->warn("[SKIP] Resource 已存在：{$path}");
            return;
        }

        $fs->ensureDirectoryExists(dirname($path));

        $stubPath = $this->getStubPath('resource');
        if (!$fs->exists($stubPath)) {
            $this->error("[ERROR] Resource stub 檔案不存在：{$stubPath}");
            return;
        }

        $stub = $fs->get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ name }}'],
            [$namespace, $name],
            $stub
        );

        $fs->put($path, $stub);
        $this->info("[OK] Resource 建立完成：{$path}");
    }

    protected function createService(Filesystem $fs, string $version, string $name): void
    {
        $class = "{$name}Service";
        $namespace = "App\\Services\\{$version}";
        $path = app_path("Services/{$version}/{$class}.php");

        if (!$this->option('force') && $fs->exists($path)) {
            $this->warn("[SKIP] Service 已存在：{$path}");
            return;
        }

        $fs->ensureDirectoryExists(dirname($path));

        $stubPath = $this->getStubPath('service');
        if (!$fs->exists($stubPath)) {
            $this->error("[ERROR] Service stub 檔案不存在：{$stubPath}");
            return;
        }

        $stub = $fs->get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ version }}'],
            [$namespace, $class, $version],
            $stub
        );

        $fs->put($path, $stub);
        $this->info("[OK] Service 建立完成：{$path}");
    }

    protected function createTest(Filesystem $fs, string $version, string $name): void
    {
        $class = "{$name}Test";
        $namespace = "Tests\\Feature\\Api\\{$version}";
        $path = base_path("tests/Feature/Api/{$version}/{$class}.php");

        if (!$this->option('force') && $fs->exists($path)) {
            $this->warn("[SKIP] Test 已存在：{$path}");
            return;
        }

        $fs->ensureDirectoryExists(dirname($path));

        $uri = '/api/' . $version . '/' . Str::kebab(Str::replaceLast('Controller', '', $name));

        $stubPath = $this->getStubPath('test');
        if (!$fs->exists($stubPath)) {
            $this->error("[ERROR] Test stub 檔案不存在：{$stubPath}");
            return;
        }

        $stub = $fs->get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ uri }}'],
            [$namespace, $class, $uri],
            $stub
        );

        $fs->put($path, $stub);
        $this->info("[OK] 測試建立完成：{$path}");
    }

    protected function createPolicy(Filesystem $fs, string $version, string $name): void
    {
        $model = Str::replaceLast('Controller', '', $name);
        $class = "{$model}Policy";
        $namespace = config('versioned.policy_namespace_prefix') . "\\{$version}";
        $path = app_path("Policies/{$version}/{$class}.php");

        if (!$this->option('force') && $fs->exists($path)) {
            $this->warn("[SKIP] Policy 已存在：{$path}");
            return;
        }

        $fs->ensureDirectoryExists(dirname($path));

        $stubPath = $this->getStubPath('policy');
        if (!$fs->exists($stubPath)) {
            $this->error("[ERROR] Policy stub 檔案不存在：{$stubPath}");
            return;
        }

        $stub = $fs->get($stubPath);
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}'],
            [$namespace, $class, $model],
            $stub
        );

        $fs->put($path, $stub);
        $this->info("[OK] Policy 建立完成：{$path}");
    }

    protected function appendRoute(Filesystem $fs, string $version, string $name): void
    {
        $routePath = base_path("routes/api.php");
        $uri = Str::kebab(Str::replaceLast('Controller', '', $name));

        $stubPath = $this->getStubPath('route');
        if (!$fs->exists($stubPath)) {
            $this->error("[ERROR] Route stub 檔案不存在：{$stubPath}");
            return;
        }

        $stub = $fs->get($stubPath);
        $stub = str_replace(
            ['{{ version }}', '{{ name }}', '{{ uri }}'],
            [$version, $name, $uri],
            $stub
        );

        file_put_contents($routePath, $stub, FILE_APPEND);
        $this->info("[OK] 已寫入路由：routes/api.php");
    }
}
