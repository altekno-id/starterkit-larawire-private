<?php

declare(strict_types=1);

const STARTER_PROVIDER = 'Altekno\\StarterKit\\Providers\\Starter\\StarterServiceProvider';
const STARTER_NAMESPACE = 'Altekno\\StarterKit\\';
const STARTER_AUTOLOAD_PATH = 'starterkit/src/';
const STARTER_PUBLISH_SCRIPT = '@php artisan starter:publish-assets --ansi';

$starterRoot = __DIR__;
$hostRoot = dirname($starterRoot);
$arguments = array_slice($argv, 1);

try {
    assertLaravelHost($hostRoot, $starterRoot);

    $composerPath = $hostRoot.'/composer.json';
    $providersPath = $hostRoot.'/bootstrap/providers.php';
    $bootstrapPath = $hostRoot.'/bootstrap/app.php';
    $gitignorePath = $hostRoot.'/.gitignore';
    $envExamplePath = $hostRoot.'/.env.example';
    $envPath = $hostRoot.'/.env';

    $composer = readJson($composerPath);
    $bootstrap = connectedBootstrap(
        readRequiredFile($bootstrapPath),
        readRequiredFile($starterRoot.'/examples/bootstrap-app.php'),
    );
    $providers = connectedProviders(readRequiredFile($providersPath));

    ensureDependencies($hostRoot, $composer);

    $composer = readJson($composerPath);
    $composer['autoload']['psr-4'][STARTER_NAMESPACE] = STARTER_AUTOLOAD_PATH;
    $composer['scripts']['post-autoload-dump'] = array_values(array_unique([
        ...($composer['scripts']['post-autoload-dump'] ?? []),
        STARTER_PUBLISH_SCRIPT,
    ]));

    writeJson($composerPath, $composer);
    writeIfChanged($bootstrapPath, $bootstrap);
    writeIfChanged($providersPath, $providers);
    ensureIgnored($gitignorePath, '/starterkit/');

    if (! is_file($envPath)) {
        if (! is_file($envExamplePath)) {
            throw new RuntimeException('File .env dan .env.example tidak ditemukan pada Laravel host.');
        }

        if (! copy($envExamplePath, $envPath)) {
            throw new RuntimeException('Tidak dapat membuat .env dari .env.example.');
        }

        output('CREATE  .env');
    }

    mergeEnvironment($envExamplePath);
    mergeEnvironment($envPath);

    run($hostRoot, ['composer', 'dump-autoload', '--no-interaction']);

    if (environmentValue($envPath, 'APP_KEY') === '') {
        run($hostRoot, [PHP_BINARY, 'artisan', 'key:generate', '--ansi']);
    }

    run($hostRoot, [
        PHP_BINARY,
        'artisan',
        'starterkit:install',
        ...$arguments,
    ]);

    output('');
    output('Starterkit terpasang. Seluruh command berikutnya dijalankan dari root Laravel host.');
} catch (Throwable $exception) {
    fwrite(STDERR, PHP_EOL.'ERROR  '.$exception->getMessage().PHP_EOL);
    exit(1);
}

/**
 * @return array<string, mixed>
 */
function readJson(string $path): array
{
    $decoded = json_decode(readRequiredFile($path), true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($decoded)) {
        throw new RuntimeException("JSON tidak valid: {$path}");
    }

    return $decoded;
}

/**
 * @param  array<string, mixed>  $data
 */
function writeJson(string $path, array $data): void
{
    $encoded = json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ).PHP_EOL;

    writeIfChanged($path, $encoded);
}

function readRequiredFile(string $path): string
{
    $contents = @file_get_contents($path);

    if ($contents === false) {
        throw new RuntimeException("File wajib tidak dapat dibaca: {$path}");
    }

    return $contents;
}

function writeIfChanged(string $path, string $contents): void
{
    if (is_file($path) && file_get_contents($path) === $contents) {
        output('SKIP    '.relativeHostPath($path));

        return;
    }

    if (file_put_contents($path, $contents, LOCK_EX) === false) {
        throw new RuntimeException("Tidak dapat menulis file: {$path}");
    }

    output('UPDATE  '.relativeHostPath($path));
}

function assertLaravelHost(string $hostRoot, string $starterRoot): void
{
    $required = [
        $hostRoot.'/artisan',
        $hostRoot.'/composer.json',
        $hostRoot.'/bootstrap/app.php',
        $hostRoot.'/bootstrap/providers.php',
    ];

    foreach ($required as $path) {
        if (! is_file($path)) {
            throw new RuntimeException(
                'Clone starterkit harus berada tepat pada <laravel>/starterkit. '
                ."File host tidak ditemukan: {$path}",
            );
        }
    }

    if (basename($starterRoot) !== 'starterkit') {
        throw new RuntimeException(
            "Folder clone wajib bernama 'starterkit', saat ini: ".basename($starterRoot),
        );
    }
}

/**
 * @param  array<string, mixed>  $composer
 */
function ensureDependencies(string $hostRoot, array $composer): void
{
    $required = array_keys((array) ($composer['require'] ?? []));
    $missing = array_values(array_diff([
        'livewire/livewire',
        'laravel-lang/common',
    ], $required));

    if ($missing === []) {
        output('SKIP    dependency runtime sudah tersedia');

        return;
    }

    run($hostRoot, [
        'composer',
        'require',
        ...$missing,
        '--no-interaction',
        '--no-scripts',
    ]);
}

function connectedProviders(string $contents): string
{
    if (str_contains($contents, 'StarterServiceProvider::class')) {
        return $contents;
    }

    if (! str_contains($contents, 'return [')) {
        throw new RuntimeException(
            'bootstrap/providers.php tidak memakai struktur Laravel yang didukung. '
            .'Lihat starterkit/examples/providers.php untuk integrasi manual.',
        );
    }

    $use = 'use '.STARTER_PROVIDER.';';
    $contents = preg_replace('/<\?php\s*/', "<?php\n\n{$use}\n", $contents, 1, $count);

    if ($contents === null || $count !== 1) {
        throw new RuntimeException('Gagal menambahkan import provider starterkit.');
    }

    return preg_replace(
        '/return\s*\[\s*/',
        "return [\n    StarterServiceProvider::class,\n",
        $contents,
        1,
    ) ?? throw new RuntimeException('Gagal mendaftarkan provider starterkit.');
}

function connectedBootstrap(string $current, string $connector): string
{
    if (str_contains($current, 'StarterBootstrap::registerRoutes()')
        && str_contains($current, 'StarterBootstrap::configureMiddleware(')
        && str_contains($current, 'StarterBootstrap::configureExceptions(')) {
        return $current;
    }

    if (! isFreshLaravelBootstrap($current)) {
        throw new RuntimeException(
            'bootstrap/app.php sudah memiliki kustomisasi yang tidak aman untuk ditimpa otomatis. '
            .'Gabungkan starterkit/examples/bootstrap-app.php secara manual, lalu jalankan ulang installer.',
        );
    }

    return $connector;
}

function isFreshLaravelBootstrap(string $contents): bool
{
    if (! str_contains($contents, 'Application::configure(basePath: dirname(__DIR__))')
        || ! str_contains($contents, "web: __DIR__.'/../routes/web.php'")
        || ! str_contains($contents, "commands: __DIR__.'/../routes/console.php'")
        || ! str_contains($contents, "health: '/up'")) {
        return false;
    }

    $unsupported = [
        'api:',
        'channels:',
        'pages:',
        'using:',
        'withBroadcasting(',
        'withSchedule(',
        'withCommands(',
        'withProviders(',
        'alias([',
        'append(',
        'prepend(',
        'render(',
        'report(',
    ];

    foreach ($unsupported as $needle) {
        if (str_contains($contents, $needle)) {
            return false;
        }
    }

    return true;
}

function ensureIgnored(string $path, string $entry): void
{
    $contents = is_file($path) ? readRequiredFile($path) : '';
    $lines = preg_split('/\R/', $contents) ?: [];

    if (in_array($entry, $lines, true)) {
        output('SKIP    .gitignore');

        return;
    }

    $contents = rtrim($contents).PHP_EOL.$entry.PHP_EOL;
    writeIfChanged($path, ltrim($contents, PHP_EOL));
}

function mergeEnvironment(string $path): void
{
    if (! is_file($path)) {
        return;
    }

    $contents = readRequiredFile($path);
    $url = environmentValueFromContents($contents, 'APP_URL') ?: 'http://localhost';
    $domain = (string) (parse_url(trim($url, "\"'"), PHP_URL_HOST) ?: 'localhost');
    $secure = strtolower((string) parse_url(trim($url, "\"'"), PHP_URL_SCHEME)) === 'https'
        ? 'true'
        : 'false';

    $required = [
        'APP_DOMAIN' => $domain,
        'APP_LOCALE' => 'id',
        'APP_FALLBACK_LOCALE' => 'id',
        'APP_FAKER_LOCALE' => 'id_ID',
        'SESSION_SECURE_COOKIE' => $secure,
        'SESSION_DOMAIN' => 'null',
        'STARTER_SUPERUSER_USERNAME' => 'superuser',
        'STARTER_SUPERUSER_EMAIL' => 'developer@example.test',
        'STARTER_SUPERUSER_PASSWORD' => '',
        'AUTH_PASSWORD_RESET_TOKEN_TABLE' => 'x_password_reset_tokens',
    ];

    foreach ($required as $key => $value) {
        $contents = setEnvironmentDefault($contents, $key, $value);
    }

    foreach ([
        'SESSION_ENCRYPT' => 'true',
        'SESSION_HTTP_ONLY' => 'true',
    ] as $key => $value) {
        $contents = setEnvironmentValue($contents, $key, $value);
    }

    $contents = setEnvironmentDefault($contents, 'SESSION_SAME_SITE', 'lax');

    writeIfChanged($path, rtrim($contents).PHP_EOL);
}

function environmentValue(string $path, string $key): string
{
    return environmentValueFromContents(readRequiredFile($path), $key);
}

function environmentValueFromContents(string $contents, string $key): string
{
    if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $contents, $matches) !== 1) {
        return '';
    }

    return trim($matches[1]);
}

function setEnvironmentValue(string $contents, string $key, string $value): string
{
    $line = "{$key}={$value}";
    $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

    if (preg_match($pattern, $contents) === 1) {
        return preg_replace($pattern, $line, $contents, 1) ?? $contents;
    }

    return rtrim($contents).PHP_EOL.$line.PHP_EOL;
}

function setEnvironmentDefault(string $contents, string $key, string $value): string
{
    if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $contents) === 1) {
        return $contents;
    }

    return setEnvironmentValue($contents, $key, $value);
}

/**
 * @param  list<string>  $command
 */
function run(string $workingDirectory, array $command): void
{
    $printable = implode(' ', array_map('escapeshellarg', $command));
    output('');
    output("RUN     {$printable}");

    $process = proc_open(
        $command,
        [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        ],
        $pipes,
        $workingDirectory,
    );

    if (! is_resource($process)) {
        throw new RuntimeException("Gagal menjalankan command: {$printable}");
    }

    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        throw new RuntimeException("Command gagal dengan exit code {$exitCode}: {$printable}");
    }
}

function relativeHostPath(string $path): string
{
    $hostRoot = dirname(__DIR__);

    return ltrim(str_replace($hostRoot, '', $path), DIRECTORY_SEPARATOR);
}

function output(string $message): void
{
    fwrite(STDOUT, $message.PHP_EOL);
}
