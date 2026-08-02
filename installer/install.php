<?php

declare(strict_types=1);

const STARTER_PROVIDER = 'Altekno\\StarterKit\\Providers\\Starter\\StarterServiceProvider';
const STARTER_NAMESPACE = 'Altekno\\StarterKit\\';
define('STARTER_DIRECTORY', basename(dirname(__DIR__)));
define('STARTER_AUTOLOAD_PATH', STARTER_DIRECTORY.'/src/');
const STARTER_CLEAR_SCRIPT = '@php artisan optimize:clear --ansi';
const STARTER_PUBLISH_SCRIPT = '@php artisan starter:publish-assets --ansi';
const STARTER_AGENTS_BLOCK_START = '<!-- starterkit:agentic-connector:start -->';
const STARTER_AGENTS_BLOCK_END = '<!-- starterkit:agentic-connector:end -->';

$starterRoot = dirname(__DIR__);
$hostRoot = dirname($starterRoot);
$arguments = array_slice($argv, 1);

try {
    assertLaravelHost($hostRoot, $starterRoot);
    $reset = hasArgument($arguments, '--reset');

    if ($reset) {
        if (hasArgument($arguments, '--skip-migration')) {
            throw new RuntimeException('--reset tidak dapat digabungkan dengan --skip-migration.');
        }

        if (argumentValue($arguments, '--app') !== null
            || argumentValue($arguments, '--app-name') !== null) {
            throw new RuntimeException('--reset tidak dapat membuat atau mengganti App project.');
        }

        assertDevelopmentDatabaseReset($hostRoot);
        assertStarterkitInstallation($hostRoot);
        confirmDevelopmentDatabaseReset($hostRoot);
        $arguments = withoutArgument($arguments, '--reset');

        if (! hasArgument($arguments, '--force')) {
            $arguments[] = '--force';
        }

        if (! hasArgument($arguments, '--skip-default-app')) {
            $arguments[] = '--skip-default-app';
        }
    } else {
        assertFreshLaravelInstallation($hostRoot);
    }

    if (! $reset && ! hasArgument($arguments, '--skip-migration')) {
        confirmFreshDatabaseReset();

        if (! hasArgument($arguments, '--force')) {
            $arguments[] = '--force';
        }
    }

    if (! $reset) {
        $arguments = withDefaultAppSelection($arguments);
    }

    $composerPath = $hostRoot.'/composer.json';
    $providersPath = $hostRoot.'/bootstrap/providers.php';
    $bootstrapPath = $hostRoot.'/bootstrap/app.php';
    $gitignorePath = $hostRoot.'/.gitignore';
    $agentsPath = $hostRoot.'/AGENTS.md';
    $envExamplePath = $hostRoot.'/.env.example';
    $envPath = $hostRoot.'/.env';
    $issuesArchivePath = $hostRoot.'/issues/archives';

    if (! $reset) {
        ensureDirectory($issuesArchivePath);
        removeFreshLaravelMigrations($hostRoot);
        configureFrameworkTableNames($hostRoot);

        $composer = readJson($composerPath);
        $bootstrap = connectedBootstrap(
            readRequiredFile($bootstrapPath),
            readRequiredFile($starterRoot.'/installer/templates/bootstrap-app.php'),
        );
        $providers = connectedProviders(readRequiredFile($providersPath));

        ensureDependencies($hostRoot, $composer);

        $composer = readJson($composerPath);
        $composer['autoload']['psr-4'][STARTER_NAMESPACE] = STARTER_AUTOLOAD_PATH;
        $postAutoloadScripts = array_values(array_filter(
            $composer['scripts']['post-autoload-dump'] ?? [],
            fn (string $script): bool => ! in_array($script, [STARTER_CLEAR_SCRIPT, STARTER_PUBLISH_SCRIPT], true),
        ));
        $composer['scripts']['post-autoload-dump'] = array_values(array_unique([
            ...$postAutoloadScripts,
            STARTER_CLEAR_SCRIPT,
            STARTER_PUBLISH_SCRIPT,
        ]));

        writeJson($composerPath, $composer);
        writeIfChanged($bootstrapPath, $bootstrap);
        writeIfChanged($providersPath, $providers);
        connectAgentInstructions(
            $agentsPath,
            str_replace(
                '{{STARTERKIT_DIRECTORY}}',
                STARTER_DIRECTORY,
                readRequiredFile($starterRoot.'/installer/templates/agents-connector.md'),
            ),
        );
        ensureIgnored($gitignorePath, '/public/vendor/');

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
    } else {
        ensureDirectory($issuesArchivePath);
    }

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

function ensureDirectory(string $path): void
{
    if (is_dir($path)) {
        output('SKIP    '.relativeHostPath($path).'/');

        return;
    }

    if (file_exists($path)) {
        throw new RuntimeException("Path directory terhalang oleh file: {$path}");
    }

    if (! mkdir($path, 0755, true) && ! is_dir($path)) {
        throw new RuntimeException("Tidak dapat membuat directory: {$path}");
    }

    output('CREATE  '.relativeHostPath($path).'/');
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
                'Snapshot starterkit harus berada tepat pada <laravel>/'.STARTER_DIRECTORY.'. '
                ."File host tidak ditemukan: {$path}",
            );
        }
    }
}

function assertFreshLaravelInstallation(string $hostRoot): void
{
    $bootstrapPath = $hostRoot.'/bootstrap/app.php';
    $composerPath = $hostRoot.'/composer.json';
    $providersPath = $hostRoot.'/bootstrap/providers.php';
    $violations = [];

    if (! isFreshLaravelBootstrap(readRequiredFile($bootstrapPath))) {
        $violations[] = 'bootstrap/app.php sudah dikustomisasi';
    }

    $composer = readJson($composerPath);

    if (isset($composer['autoload']['psr-4'][STARTER_NAMESPACE])) {
        $violations[] = 'starterkit sudah terhubung pada composer.json';
    }

    if (str_contains(readRequiredFile($providersPath), 'StarterServiceProvider::class')) {
        $violations[] = 'StarterServiceProvider sudah terdaftar';
    }

    foreach ([
        'app/Livewire',
        'config/apps',
        'database/migrations/apps',
        'resources/views/apps',
        'routes/apps',
    ] as $directory) {
        if (directoryHasFiles($hostRoot.'/'.$directory)) {
            $violations[] = "{$directory} sudah berisi code project";
        }
    }

    $allowedMigrations = [
        '0001_01_01_000000_create_users_table.php',
        '0001_01_01_000001_create_cache_table.php',
        '0001_01_01_000002_create_jobs_table.php',
    ];
    $migrationFiles = glob($hostRoot.'/database/migrations/*.php') ?: [];
    $unexpectedMigrations = array_values(array_filter(
        $migrationFiles,
        fn (string $path): bool => ! in_array(basename($path), $allowedMigrations, true),
    ));

    if ($unexpectedMigrations !== []) {
        $violations[] = 'database/migrations memiliki migration di luar bawaan Laravel fresh';
    }

    if ($violations === []) {
        return;
    }

    throw new RuntimeException(
        "Installer hanya boleh dijalankan pada project Laravel fresh.\n- "
        .implode("\n- ", $violations)
        ."\nGunakan alur update pada README untuk project yang sudah memakai starterkit.",
    );
}

function assertStarterkitInstallation(string $hostRoot): void
{
    $composer = readJson($hostRoot.'/composer.json');
    $autoloadPath = $composer['autoload']['psr-4'][STARTER_NAMESPACE] ?? null;
    $providers = readRequiredFile($hostRoot.'/bootstrap/providers.php');
    $bootstrap = readRequiredFile($hostRoot.'/bootstrap/app.php');

    if ($autoloadPath !== STARTER_AUTOLOAD_PATH
        || ! str_contains($providers, 'StarterServiceProvider::class')
        || ! str_contains($bootstrap, 'StarterBootstrap::registerRoutes()')) {
        throw new RuntimeException(
            'Mode --reset hanya dapat digunakan pada Laravel host yang sudah terpasang starterkit ini.',
        );
    }
}

function assertDevelopmentDatabaseReset(string $hostRoot): void
{
    $envPath = $hostRoot.'/.env';

    if (! is_file($envPath)) {
        throw new RuntimeException('Mode --reset membutuhkan file .env untuk memverifikasi APP_ENV.');
    }

    $environment = strtolower(trim(effectiveEnvironmentValue($envPath, 'APP_ENV'), "\"' "));

    if (! in_array($environment, ['local', 'development'], true)) {
        throw new RuntimeException(
            'Mode --reset hanya diizinkan pada APP_ENV=local atau APP_ENV=development. '
            .'Environment aktif: '.($environment !== '' ? $environment : '(kosong)').'.',
        );
    }
}

function confirmDevelopmentDatabaseReset(string $hostRoot): void
{
    $database = trim(effectiveEnvironmentValue($hostRoot.'/.env', 'DB_DATABASE'), "\"' ");

    output('');
    output('PERINGATAN RESET DATABASE DEVELOPMENT');
    output('Mode ini menjalankan migrate:fresh dan menghapus seluruh tabel/data pada database.');
    output('Seluruh source App, migration, route, view, test, asset, upload, dan issue tetap dipertahankan.');
    output('Database target: '.($database !== '' ? $database : '(tidak terdefinisi)'));
    output('');
    fwrite(STDOUT, 'Tahap 1/2 — lanjutkan reset database? Ketik y [y/N]: ');

    $firstAnswer = fgets(STDIN);

    if ($firstAnswer === false || strtolower(trim($firstAnswer)) !== 'y') {
        output('');
        output('Reset database dibatalkan. Tidak ada source atau database yang diubah.');
        exit(0);
    }

    fwrite(STDOUT, 'Tahap 2/2 — ketik RESET untuk menjalankan migrate:fresh: ');
    $secondAnswer = fgets(STDIN);

    if ($secondAnswer === false || trim($secondAnswer) !== 'RESET') {
        output('');
        output('Reset database dibatalkan. Tidak ada source atau database yang diubah.');
        exit(0);
    }

    output('');
}

function directoryHasFiles(string $path): bool
{
    if (! is_dir($path)) {
        return false;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $item) {
        if ($item->isFile()) {
            return true;
        }
    }

    return false;
}

function removeFreshLaravelMigrations(string $hostRoot): void
{
    $paths = [
        $hostRoot.'/database/migrations/0001_01_01_000000_create_users_table.php',
        $hostRoot.'/database/migrations/0001_01_01_000001_create_cache_table.php',
        $hostRoot.'/database/migrations/0001_01_01_000002_create_jobs_table.php',
    ];

    foreach ($paths as $path) {
        if (! is_file($path)) {
            continue;
        }

        if (! unlink($path)) {
            throw new RuntimeException("Tidak dapat menghapus migration bawaan Laravel: {$path}");
        }

        output('REMOVE  '.relativeHostPath($path));
    }
}

function configureFrameworkTableNames(string $hostRoot): void
{
    $replacements = [
        'config/database.php' => [
            "'table' => 'migrations'," => "'table' => env('DB_MIGRATIONS_TABLE', 'x_migrations'),",
        ],
        'config/cache.php' => [
            "'table' => env('DB_CACHE_TABLE', 'cache')," => "'table' => env('DB_CACHE_TABLE', 'x_cache'),",
            "'lock_table' => env('DB_CACHE_LOCK_TABLE')," => "'lock_table' => env('DB_CACHE_LOCK_TABLE', 'x_cache_locks'),",
        ],
        'config/queue.php' => [
            "'table' => env('DB_QUEUE_TABLE', 'jobs')," => "'table' => env('DB_QUEUE_TABLE', 'x_jobs'),",
            "'table' => 'job_batches'," => "'table' => env('DB_QUEUE_BATCH_TABLE', 'x_job_batches'),",
            "'table' => 'failed_jobs'," => "'table' => env('DB_QUEUE_FAILED_TABLE', 'x_failed_jobs'),",
        ],
        'config/session.php' => [
            "'table' => env('SESSION_TABLE', 'sessions')," => "'table' => env('SESSION_TABLE', 'x_sessions'),",
        ],
    ];

    foreach ($replacements as $relativePath => $fileReplacements) {
        $path = $hostRoot.'/'.$relativePath;
        $contents = readRequiredFile($path);

        foreach ($fileReplacements as $from => $to) {
            if (str_contains($contents, $to)) {
                continue;
            }

            if (! str_contains($contents, $from)) {
                throw new RuntimeException(
                    "Struktur {$relativePath} tidak didukung untuk konfigurasi tabel helper.",
                );
            }

            $contents = str_replace($from, $to, $contents);
        }

        writeIfChanged($path, $contents);
    }
}

/**
 * @param  list<string>  $arguments
 */
function hasArgument(array $arguments, string $argument): bool
{
    return in_array($argument, $arguments, true);
}

/**
 * @param  list<string>  $arguments
 * @return list<string>
 */
function withoutArgument(array $arguments, string $argument): array
{
    return array_values(array_filter(
        $arguments,
        fn (string $value): bool => $value !== $argument,
    ));
}

/**
 * @param  list<string>  $arguments
 * @return list<string>
 */
function withDefaultAppSelection(array $arguments): array
{
    $skipDefaultApp = hasArgument($arguments, '--skip-default-app');
    $app = argumentValue($arguments, '--app');

    if ($skipDefaultApp && $app !== null) {
        throw new RuntimeException(
            'Gunakan salah satu: --app=<kode> atau --skip-default-app, bukan keduanya.',
        );
    }

    if ($skipDefaultApp || $app !== null) {
        return $arguments;
    }

    output('PENGATURAN APP PERTAMA');
    output('App adalah batas fitur tingkat atas yang dapat memakai subdomain sendiri.');
    output('Kosongkan input jika project belum membutuhkan app pertama.');
    output('');

    while (true) {
        fwrite(STDOUT, 'Kode/subdomain app pertama (contoh: keuangan, kosong = lewati): ');
        $answer = fgets(STDIN);
        $app = $answer === false ? '' : strtolower(trim($answer));

        if ($app === '') {
            $arguments[] = '--skip-default-app';
            output('App pertama dilewati. Landing onboarding akan menjelaskan cara membuatnya nanti.');
            output('');

            return $arguments;
        }

        if ($app === 'api') {
            output("Kode 'api' dicadangkan untuk gateway API. Gunakan kode App lain.");

            continue;
        }

        if (preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $app) === 1) {
            break;
        }

        output('Kode tidak valid. Gunakan huruf kecil, angka, atau tanda hubung internal.');
    }

    $defaultName = defaultAppName($app);
    fwrite(STDOUT, "Nama app [{$defaultName}]: ");
    $answer = fgets(STDIN);
    $name = $answer === false || trim($answer) === ''
        ? $defaultName
        : trim($answer);

    $arguments[] = "--app={$app}";
    $arguments[] = "--app-name={$name}";
    output("App pertama akan dibuat: {$name} ({$app}).");
    output('');

    return $arguments;
}

/**
 * @param  list<string>  $arguments
 */
function argumentValue(array $arguments, string $option): ?string
{
    $prefix = $option.'=';

    foreach ($arguments as $argument) {
        if (str_starts_with($argument, $prefix)) {
            return substr($argument, strlen($prefix));
        }
    }

    return null;
}

function defaultAppName(string $app): string
{
    $words = preg_replace('/(?<=\D)(?=\d)|(?<=\d)(?=\D)/', ' ', str_replace('-', ' ', $app));

    return ucwords((string) $words);
}

function confirmFreshDatabaseReset(): void
{
    output('');
    output('PERINGATAN INSTALASI AWAL');
    output('Starterkit hanya boleh dipasang pada project Laravel fresh dengan database khusus yang baru.');
    output('Proses ini menjalankan migrate:fresh: SEMUA TABEL DAN DATA pada database di .env akan dihapus.');
    output('Jangan lanjutkan jika database pernah dipakai oleh aplikasi lain atau memiliki data penting.');
    output('');
    fwrite(STDOUT, 'Lanjutkan instalasi dan reset database? Ketik y untuk lanjut [y/N]: ');

    $answer = fgets(STDIN);

    if ($answer !== false && strtolower(trim($answer)) === 'y') {
        output('');

        return;
    }

    output('');
    output('Instalasi dibatalkan. Tidak ada file project atau database yang diubah.');
    exit(0);
}

/**
 * @param  array<string, mixed>  $composer
 */
function ensureDependencies(string $hostRoot, array $composer): void
{
    $required = array_keys((array) ($composer['require'] ?? []));
    $missingRuntime = array_values(array_diff([
        'dedoc/scramble',
        'livewire/livewire',
        'laravel-lang/common',
        'power-components/livewire-powergrid',
    ], $required));

    if ($missingRuntime === []) {
        output('SKIP    dependency runtime sudah tersedia');
    } else {
        run($hostRoot, [
            'composer',
            'require',
            ...$missingRuntime,
            '--no-interaction',
            '--no-scripts',
        ]);
    }

    $requiredDev = array_keys((array) ($composer['require-dev'] ?? []));
    $missingDevelopment = array_values(array_diff([
        'pestphp/pest',
        'pestphp/pest-plugin-laravel',
    ], $requiredDev));

    if ($missingDevelopment === []) {
        output('SKIP    dependency test Pest sudah tersedia');

        return;
    }

    run($hostRoot, [
        'composer',
        'require',
        '--dev',
        ...$missingDevelopment,
        '--with-all-dependencies',
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
            .'Gunakan project Laravel fresh sesuai '.STARTER_DIRECTORY.'/README.md.',
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
            .'Gabungkan '.STARTER_DIRECTORY.'/installer/templates/bootstrap-app.php secara manual, '
            .'lalu jalankan ulang installer.',
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

function connectAgentInstructions(string $path, string $connector): void
{
    $contents = is_file($path) ? readRequiredFile($path) : '';
    $startCount = substr_count($contents, STARTER_AGENTS_BLOCK_START);
    $endCount = substr_count($contents, STARTER_AGENTS_BLOCK_END);
    $hasConnector = $startCount === 1 && $endCount === 1;

    if ($startCount !== $endCount || $startCount > 1) {
        throw new RuntimeException(
            'AGENTS.md memiliki marker connector starterkit yang tidak valid. '
            .'Perbaiki marker connector sebelum menjalankan installer.',
        );
    }

    $block = STARTER_AGENTS_BLOCK_START.PHP_EOL
        .trim($connector).PHP_EOL
        .STARTER_AGENTS_BLOCK_END;

    if (! $hasConnector) {
        $updated = trim($contents) === ''
            ? $block.PHP_EOL
            : rtrim($contents).PHP_EOL.PHP_EOL.$block.PHP_EOL;

        writeIfChanged($path, $updated);

        return;
    }

    $pattern = '/'.preg_quote(STARTER_AGENTS_BLOCK_START, '/')
        .'.*?'
        .preg_quote(STARTER_AGENTS_BLOCK_END, '/').'/s';
    $updated = preg_replace_callback(
        $pattern,
        static fn (): string => $block,
        $contents,
        1,
        $count,
    );

    if ($updated === null || $count !== 1) {
        throw new RuntimeException('Connector starterkit pada AGENTS.md tidak dapat diperbarui dengan aman.');
    }

    writeIfChanged($path, rtrim($updated).PHP_EOL);
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
        'STARTER_API_ENABLED' => 'false',
        'STARTER_THEME' => 'tabler',
        'DB_MIGRATIONS_TABLE' => 'x_migrations',
        'DB_CACHE_TABLE' => 'x_cache',
        'DB_CACHE_LOCK_TABLE' => 'x_cache_locks',
        'DB_QUEUE_TABLE' => 'x_jobs',
        'DB_QUEUE_BATCH_TABLE' => 'x_job_batches',
        'DB_QUEUE_FAILED_TABLE' => 'x_failed_jobs',
        'SESSION_SECURE_COOKIE' => $secure,
        'SESSION_DOMAIN' => 'null',
        'SESSION_TABLE' => 'x_sessions',
        'STARTER_SUPERUSER_USERNAME' => 'superuser',
        'STARTER_SUPERUSER_EMAIL' => 'developer@example.test',
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

    $contents = setEnvironmentValueIfEmptyOrLegacyDefault(
        $contents,
        'STARTER_SUPERUSER_PASSWORD',
        'superuser123',
    );
    $contents = setEnvironmentDefault($contents, 'SESSION_SAME_SITE', 'lax');

    writeIfChanged($path, rtrim($contents).PHP_EOL);
}

function environmentValue(string $path, string $key): string
{
    return environmentValueFromContents(readRequiredFile($path), $key);
}

function effectiveEnvironmentValue(string $path, string $key): string
{
    $processValue = getenv($key);

    return is_string($processValue) && $processValue !== ''
        ? $processValue
        : environmentValue($path, $key);
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

function setEnvironmentValueIfEmptyOrLegacyDefault(string $contents, string $key, string $value): string
{
    $current = trim(environmentValueFromContents($contents, $key), "\"' ");

    return in_array($current, ['', 'rahasia123'], true)
        ? setEnvironmentValue($contents, $key, $value)
        : $contents;
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
    $hostRoot = dirname(dirname(__DIR__));

    return ltrim(str_replace($hostRoot, '', $path), DIRECTORY_SEPARATOR);
}

function output(string $message): void
{
    fwrite(STDOUT, $message.PHP_EOL);
}
