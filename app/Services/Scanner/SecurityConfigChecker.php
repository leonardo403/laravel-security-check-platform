<?php

namespace App\Services\Scanner;

use Illuminate\Support\Facades\File;

class SecurityConfigChecker
{
    private string $path;
    private array $checks = [];

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function run(): array
    {
        $this->checks = [];

        $this->checkDebugMode();
        $this->checkAppKey();
        $this->checkAppUrl();
        $this->checkDebugbarTelescope();
        $this->checkQueueDriver();
        $this->checkCacheDriver();
        $this->checkSessionDriver();
        $this->checkMailDriver();
        $this->checkTrustedProxies();
        $this->checkCors();
        $this->checkLogging();
        $this->checkPublicDirectories();
        $this->checkComposerAutoload();
        $this->checkConfigCache();
        $this->checkRouteCache();
        $this->checkErrorExposure();
        $this->checkFilePermissions();

        return $this->checks;
    }

    private function addCheck(string $name, string $status, string $message, string $severity = 'info'): void
    {
        $this->checks[] = [
            'name' => $name,
            'status' => $status,
            'message' => $message,
            'severity' => $severity,
        ];
    }

    private function getEnvValue(string $key): ?string
    {
        $envFile = $this->path . '/.env';
        if (!File::exists($envFile)) {
            return null;
        }

        $content = File::get($envFile);
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, $key . '=')) {
                $value = substr($line, strlen($key) + 1);
                return trim($value, '"\'');
            }
        }

        return null;
    }

    private function fileContains(string $filePath, string $pattern): bool
    {
        if (!File::exists($filePath)) {
            return false;
        }
        return str_contains(File::get($filePath), $pattern);
    }

    private function getConfigValue(string $configFile, string $key): mixed
    {
        $filePath = $this->path . '/config/' . $configFile;
        if (!File::exists($filePath)) {
            return null;
        }

        $content = File::get($filePath);
        if (preg_match("/'{$key}'\s*=>\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            return $matches[1];
        }
        if (preg_match("/\"{$key}\"\s*=>\s*[\"']([^\"']+)[\"']/", $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function checkDebugMode(): void
    {
        $appDebug = $this->getEnvValue('APP_DEBUG');

        if ($appDebug === 'true') {
            $this->addCheck(
                'Debug Mode',
                'fail',
                'APP_DEBUG está habilitado. Desative em produção para não expor detalhes de erros.',
                'high'
            );
        } elseif ($appDebug === 'false') {
            $this->addCheck(
                'Debug Mode',
                'pass',
                'APP_DEBUG está desativado.',
                'info'
            );
        } else {
            $this->addCheck(
                'Debug Mode',
                'warning',
                'APP_DEBUG não está definido no .env.',
                'medium'
            );
        }
    }

    private function checkAppKey(): void
    {
        $appKey = $this->getEnvValue('APP_KEY');

        if (empty($appKey)) {
            $this->addCheck(
                'APP_KEY',
                'fail',
                'APP_KEY não está definida. Execute php artisan key:generate.',
                'critical'
            );
        } elseif (str_starts_with($appKey, 'base64:') && strlen($appKey) < 50) {
            $this->addCheck(
                'APP_KEY',
                'warning',
                'APP_KEY parece ser uma chave padrão/curta. Gere uma nova com php artisan key:generate.',
                'high'
            );
        } else {
            $this->addCheck(
                'APP_KEY',
                'pass',
                'APP_KEY está configurada.',
                'info'
            );
        }
    }

    private function checkAppUrl(): void
    {
        $appUrl = $this->getEnvValue('APP_URL');

        if (empty($appUrl)) {
            $this->addCheck(
                'APP_URL',
                'fail',
                'APP_URL não está definida no .env.',
                'high'
            );
        } elseif (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $this->addCheck(
                'APP_URL',
                'warning',
                'APP_URL aponta para localhost (' . $appUrl . '). Configure o domínio de produção.',
                'medium'
            );
        } elseif (!str_starts_with($appUrl, 'https://')) {
            $this->addCheck(
                'APP_URL',
                'warning',
                'APP_URL não usa HTTPS: ' . $appUrl . '. Use HTTPS em produção.',
                'medium'
            );
        } else {
            $this->addCheck(
                'APP_URL',
                'pass',
                'APP_URL está configurada: ' . $appUrl,
                'info'
            );
        }
    }

    private function checkDebugbarTelescope(): void
    {
        $composerFile = $this->path . '/composer.json';
        if (!File::exists($composerFile)) {
            return;
        }

        $composer = json_decode(File::get($composerFile), true);
        $require = array_merge(
            $composer['require'] ?? [],
            $composer['require-dev'] ?? []
        );

        $debugPackages = ['barryvdh/laravel-debugbar', 'laravel/telescope'];
        $found = [];

        foreach ($debugPackages as $package) {
            if (isset($require[$package])) {
                $found[] = $package;
            }
        }

        if (!empty($found)) {
            $appDebug = $this->getEnvValue('APP_DEBUG');
            if ($appDebug === 'true') {
                $this->addCheck(
                    'Debug Packages',
                    'fail',
                    'Pacotes de debug instalados e APP_DEBUG=true: ' . implode(', ', $found) . '. Remova-os ou desative o debug em produção.',
                    'high'
                );
            } else {
                $this->addCheck(
                    'Debug Packages',
                    'warning',
                    'Pacotes de debug instalados: ' . implode(', ', $found) . '. Considere removê-los em produção.',
                    'medium'
                );
            }
        } else {
            $this->addCheck(
                'Debug Packages',
                'pass',
                'Nenhum pacote de debug (Debugbar/Telescope) encontrado.',
                'info'
            );
        }
    }

    private function checkQueueDriver(): void
    {
        $driver = $this->getEnvValue('QUEUE_CONNECTION');

        if ($driver === 'sync' || $driver === 'file') {
            $this->addCheck(
                'Queue Driver',
                'warning',
                'QUEUE_CONNECTION="' . $driver . '". Em produção, use redis, sqs ou database.',
                'medium'
            );
        } elseif (empty($driver)) {
            $this->addCheck(
                'Queue Driver',
                'fail',
                'QUEUE_CONNECTION não está definido.',
                'medium'
            );
        } else {
            $this->addCheck(
                'Queue Driver',
                'pass',
                'QUEUE_CONNECTION="' . $driver . '".',
                'info'
            );
        }
    }

    private function checkCacheDriver(): void
    {
        $driver = $this->getEnvValue('CACHE_DRIVER') ?? $this->getEnvValue('CACHE_STORE');

        if ($driver === 'file' || $driver === 'sync') {
            $this->addCheck(
                'Cache Driver',
                'warning',
                'CACHE_DRIVER="' . $driver . '". Em produção, use redis ou memcached.',
                'medium'
            );
        } elseif (empty($driver)) {
            $this->addCheck(
                'Cache Driver',
                'warning',
                'CACHE_DRIVER/CACHE_STORE não está definido.',
                'medium'
            );
        } else {
            $this->addCheck(
                'Cache Driver',
                'pass',
                'Cache configurado com "' . $driver . '".',
                'info'
            );
        }
    }

    private function checkSessionDriver(): void
    {
        $driver = $this->getEnvValue('SESSION_DRIVER');

        if ($driver === 'file') {
            $this->addCheck(
                'Session Driver',
                'warning',
                'SESSION_DRIVER="file". Em produção, use redis ou database.',
                'medium'
            );
        } elseif ($driver === 'cookie') {
            $this->addCheck(
                'Session Driver',
                'warning',
                'SESSION_DRIVER="cookie". Em produção, use redis ou database para melhor segurança.',
                'low'
            );
        } elseif (empty($driver)) {
            $this->addCheck(
                'Session Driver',
                'warning',
                'SESSION_DRIVER não está definido.',
                'medium'
            );
        } else {
            $this->addCheck(
                'Session Driver',
                'pass',
                'SESSION_DRIVER="' . $driver . '".',
                'info'
            );
        }
    }

    private function checkMailDriver(): void
    {
        $driver = $this->getEnvValue('MAIL_MAILER');

        if ($driver === 'log' || $driver === 'array' || $driver === 'smtp') {
            $this->addCheck(
                'Mail Driver',
                'warning',
                'MAIL_MAILER="' . $driver . '". Em produção, use ses, mailgun ou smtp autenticado.',
                'medium'
            );
        } elseif (empty($driver)) {
            $this->addCheck(
                'Mail Driver',
                'warning',
                'MAIL_MAILER não está definido.',
                'medium'
            );
        } else {
            $this->addCheck(
                'Mail Driver',
                'pass',
                'MAIL_MAILER="' . $driver . '".',
                'info'
            );
        }
    }

    private function checkTrustedProxies(): void
    {
        $middlewareFile = $this->path . '/app/Http/Kernel.php';
        $bootstrapApp = $this->path . '/bootstrap/app.php';
        $trustedProxies = false;

        if (File::exists($middlewareFile)) {
            $content = File::get($middlewareFile);
            $trustedProxies = str_contains($content, 'TrustProxies') || str_contains($content, 'trustedProxy');
        }

        if (!$trustedProxies && File::exists($bootstrapApp)) {
            $content = File::get($bootstrapApp);
            $trustedProxies = str_contains($content, 'TrustProxies');
        }

        $envValue = $this->getEnvValue('TRUSTED_PROXIES');

        if (!$trustedProxies && empty($envValue)) {
            $this->addCheck(
                'Trusted Proxies',
                'warning',
                'Trusted Proxies não está configurado. Necessário atrás de load balancers/proxies.',
                'low'
            );
        } else {
            $this->addCheck(
                'Trusted Proxies',
                'pass',
                'Trusted Proxies está configurado.',
                'info'
            );
        }
    }

    private function checkCors(): void
    {
        $corsConfig = $this->path . '/config/cors.php';

        if (!File::exists($corsConfig)) {
            $this->addCheck(
                'CORS',
                'pass',
                'Arquivo de configuração CORS não encontrado (usa padrão seguro).',
                'info'
            );
            return;
        }

        $content = File::get($corsConfig);

        if (str_contains($content, "'*'") || str_contains($content, '"*"')) {
            $this->addCheck(
                'CORS',
                'fail',
                'CORS permite todas as origens (*). Restrinja aos domínios necessários.',
                'high'
            );
        } else {
            $this->addCheck(
                'CORS',
                'pass',
                'CORS está configurado de forma restritiva.',
                'info'
            );
        }
    }

    private function checkLogging(): void
    {
        $logChannel = $this->getEnvValue('LOG_CHANNEL');

        if (empty($logChannel) || $logChannel === 'stack') {
            $logStack = $this->getConfigValue('logging.php', 'default');
            if ($logStack === 'stack') {
                $this->addCheck(
                    'Logging',
                    'warning',
                    'LOG_CHANNEL usa stack. Verifique se o driver secundário não é apenas "single".',
                    'low'
                );
            } else {
                $this->addCheck(
                    'Logging',
                    'pass',
                    'LOG_CHANNEL="' . ($logChannel ?: 'stack') . '".',
                    'info'
                );
            }
        } elseif ($logChannel === 'single') {
            $this->addCheck(
                'Logging',
                'warning',
                'LOG_CHANNEL="single". Em produção, use daily ou um sistema externo.',
                'medium'
            );
        } else {
            $this->addCheck(
                'Logging',
                'pass',
                'LOG_CHANNEL="' . $logChannel . '".',
                'info'
            );
        }
    }

    private function checkPublicDirectories(): void
    {
        $sensitiveFiles = [
            '/.env',
            '/composer.json',
            '/composer.lock',
            '/package.json',
            '/package-lock.json',
            '/.git/config',
            '/.env.backup',
            '/.env.production',
        ];

        $publicPath = $this->path . '/public';
        $exposed = [];

        foreach ($sensitiveFiles as $file) {
            $fullPath = $publicPath . $file;
            if (File::exists($fullPath)) {
                $exposed[] = basename($file);
            }
        }

        if (!empty($exposed)) {
            $this->addCheck(
                'Public Directories',
                'fail',
                'Arquivos sensíveis acessíveis publicamente: ' . implode(', ', $exposed) . '. Remova-os do diretório public.',
                'high'
            );
        } else {
            $this->addCheck(
                'Public Directories',
                'pass',
                'Nenhum arquivo sensível encontrado no diretório public.',
                'info'
            );
        }
    }

    private function checkComposerAutoload(): void
    {
        $autoloadFile = $this->path . '/vendor/composer/autoload_classmap.php';

        if (File::exists($autoloadFile)) {
            $size = File::size($autoloadFile);
            if ($size < 100) {
                $this->addCheck(
                    'Composer Autoload',
                    'warning',
                    'Vendor sem autoload otimizado. Execute composer dump-autoload -o.',
                    'low'
                );
            } else {
                $this->addCheck(
                    'Composer Autoload',
                    'pass',
                    'Autoload do Composer presente.',
                    'info'
                );
            }
        } else {
            $this->addCheck(
                'Composer Autoload',
                'warning',
                'vendor/composer/autoload_classmap.php não encontrado. Execute composer install.',
                'medium'
            );
        }
    }

    private function checkConfigCache(): void
    {
        $cacheFile = $this->path . '/bootstrap/cache/config.php';

        if (File::exists($cacheFile)) {
            $this->addCheck(
                'Config Cache',
                'pass',
                'Config cache encontrado.',
                'info'
            );
        } else {
            $this->addCheck(
                'Config Cache',
                'warning',
                'Config não está em cache. Execute php artisan config:cache em produção.',
                'low'
            );
        }
    }

    private function checkRouteCache(): void
    {
        $cacheFile = $this->path . '/bootstrap/cache/routes-v7.php';

        if (!File::exists($cacheFile)) {
            $files = glob($this->path . '/bootstrap/cache/routes-*.php');
            $cacheFile = !empty($files) ? $files[0] : null;
        }

        if ($cacheFile && File::exists($cacheFile)) {
            $this->addCheck(
                'Route Cache',
                'pass',
                'Route cache encontrado.',
                'info'
            );
        } else {
            $this->addCheck(
                'Route Cache',
                'warning',
                'Rotas não estão em cache. Execute php artisan route:cache em produção.',
                'low'
            );
        }
    }

    private function checkErrorExposure(): void
    {
        $appDebug = $this->getEnvValue('APP_DEBUG');
        $exceptionHandler = $this->path . '/app/Exceptions/Handler.php';
        $bootstrapApp = $this->path . '/bootstrap/app.php';

        $customHandler = false;

        if (File::exists($exceptionHandler)) {
            $content = File::get($exceptionHandler);
            $customHandler = str_contains($content, 'render') || str_contains($content, 'report');
        }

        if (!$customHandler && File::exists($bootstrapApp)) {
            $content = File::get($bootstrapApp);
            $customHandler = str_contains($content, 'withExceptions');
        }

        if ($appDebug === 'true' && $customHandler) {
            $this->addCheck(
                'Error Exposure',
                'fail',
                'APP_DEBUG=true com handler de exceções customizado. Stack traces podem ser expostos.',
                'high'
            );
        } elseif ($appDebug === 'true') {
            $this->addCheck(
                'Error Exposure',
                'warning',
                'APP_DEBUG=true. Detalhes de erros podem ser visíveis aos usuários.',
                'high'
            );
        } else {
            $this->addCheck(
                'Error Exposure',
                'pass',
                'APP_DEBUG=false. Detalhes de erros estão ocultos.',
                'info'
            );
        }
    }

    private function checkFilePermissions(): void
    {
        $issues = [];

        $writableDirs = [
            '/storage',
            '/bootstrap/cache',
        ];

        foreach ($writableDirs as $dir) {
            $fullPath = $this->path . $dir;
            if (File::exists($fullPath)) {
                $perms = fileperms($fullPath);
                $octal = substr(sprintf('%o', $perms), -4);

                if (substr($octal, -2) !== '00' && substr($octal, -2) !== '04' && substr($octal, -2) !== '05') {
                    $issues[] = $dir . ' (' . $octal . ')';
                }
            }
        }

        if (!empty($issues)) {
            $this->addCheck(
                'File Permissions',
                'warning',
                'Permissões de diretório potencialmente inseguras: ' . implode(', ', $issues) . '. Use 755 ou 775.',
                'medium'
            );
        } else {
            $this->addCheck(
                'File Permissions',
                'pass',
                'Permissões de diretório parecem adequadas.',
                'info'
            );
        }
    }
}
