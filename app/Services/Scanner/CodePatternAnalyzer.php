<?php

namespace App\Services\Scanner;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CodePatternAnalyzer
{
    public const CATEGORY_SECRETS = 'secrets';

    public const CATEGORY_CODE_QUALITY = 'code_quality';

    private const SKIPPED_DIRS = [
        'vendor', 'node_modules', '.git', 'storage', 'bootstrap/cache', 'cache',
        'var', '.idea', '.vscode', 'dist', 'build', 'coverage', 'vendor-local',
    ];

    private const EXTENSIONS = [
        'php', 'phtml', 'js', 'ts', 'py', 'rb', 'go', 'java', 'cs',
    ];

    private const MAX_FILES = 1500;

    private const MAX_FILE_SIZE = 524288;

    private const MAX_FINDINGS = 100;

    public function analyze(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $findings = [];
        $filesScanned = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                fn ($file) => $this->shouldScan($file)
            )
        );

        foreach ($iterator as $file) {
            if (++$filesScanned > self::MAX_FILES) {
                break;
            }

            $content = @file_get_contents($file->getPathname());

            if ($content === false || trim($content) === '') {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen(rtrim($path, '/')) + 1);

            foreach ($this->patterns() as $pattern) {
                if (count($findings) >= self::MAX_FINDINGS) {
                    return $findings;
                }

                $matches = [];

                if (@preg_match_all($pattern['regex'], $content, $matches, PREG_OFFSET_CAPTURE) > 0) {
                    $line = substr_count(substr($content, 0, $matches[0][0][1]), "\n") + 1;
                    $snippet = $this->snippet($matches[0][0][0]);

                    $findings[] = [
                        'name' => $pattern['name'],
                        'severity' => $pattern['severity'],
                        'category' => $pattern['category'],
                        'package' => 'source-code',
                        'version' => '',
                        'description' => sprintf(
                            '%s Encontrado em %s:%d. Código: %s',
                            $pattern['description'],
                            $relativePath,
                            $line,
                            $snippet
                        ),
                        'cve' => '',
                    ];
                }
            }
        }

        return $findings;
    }

    private function shouldScan(\SplFileInfo $file): bool
    {
        if ($file->isDir()) {
            return ! in_array($file->getFilename(), self::SKIPPED_DIRS, true);
        }

        return in_array(strtolower($file->getExtension()), self::EXTENSIONS, true)
            && $file->getSize() <= self::MAX_FILE_SIZE;
    }

    private function snippet(string $match): string
    {
        $snippet = trim(preg_replace('/\s+/', ' ', $match) ?? '');

        return mb_strlen($snippet) > 80 ? mb_substr($snippet, 0, 80).'...' : $snippet;
    }

    private function patterns(): array
    {
        return [
            [
                'name' => 'Execução de comandos do sistema',
                'regex' => '/\b(?:exec|shell_exec|system|passthru|popen|proc_open|pcntl_exec)\s*\(/i',
                'severity' => 'critical',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'Função perigosa de execução de comandos do sistema detectada. Valide e restrinja rigorosamente a entrada antes de qualquer uso.',
            ],
            [
                'name' => 'Uso de eval()',
                'regex' => '/\beval\s*\(/i',
                'severity' => 'critical',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'eval() executa código dinamicamente e pode permitir injeção de código.',
            ],
            [
                'name' => 'Desserialização de entrada do usuário',
                'regex' => '/unserialize\s*\(\s*\$_(?:GET|POST|REQUEST|COOKIE)\b/i',
                'severity' => 'critical',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'unserialize() recebe entrada do usuário, possibilitando injeção de objetos (object injection).',
            ],
            [
                'name' => 'Inclusão de arquivo baseada em entrada',
                'regex' => '/(?:include|include_once|require|require_once)\s*\(?\s*\$_(?:GET|POST|REQUEST)\b/i',
                'severity' => 'critical',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'Inclusão de arquivo controlada pelo usuário pode resultar em Local/Remote File Inclusion (LFI/RFI).',
            ],
            [
                'name' => 'Montagem dinâmica de SQL',
                'regex' => '/["\'](?:SELECT|INSERT|UPDATE|DELETE|WHERE|ORDER BY|GROUP BY)[^"\']*"\s*\.\s*\$[a-zA-Z_]/i',
                'severity' => 'high',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'SQL construído por concatenação de strings com variáveis pode permitir SQL Injection. Use query builders ou prepared statements.',
            ],
            [
                'name' => 'Credencial hardcoded',
                'regex' => '/(?:password|passwd|api_key|apikey|secret|token|client_secret)\s*[:=]\s*["\'][^"\']{6,}["\']/i',
                'severity' => 'high',
                'category' => self::CATEGORY_SECRETS,
                'description' => 'Credencial ou segredo aparentemente embutido no código-fonte. Mova para variáveis de ambiente.',
            ],
            [
                'name' => 'Requisições de rede com entrada do usuário (SSRF)',
                'regex' => '/(?:file_get_contents|curl_init|fopen|copy)\s*\([^)]*\$_(?:GET|POST|REQUEST)\b/i',
                'severity' => 'high',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'URL controlada pelo usuário usada em requisições HTTP pode permitir Server-Side Request Forgery (SSRF).',
            ],
            [
                'name' => 'Uso de consultas SQL brutas',
                'regex' => '/(?:whereRaw|selectRaw|orderByRaw|groupByRaw|havingRaw|DB::raw)\s*\(/i',
                'severity' => 'medium',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'Consultas SQL brutas detectadas. Verifique se nenhuma variável é concatenada sem sanitização.',
            ],
            [
                'name' => 'Criptografia fraca de senha',
                'regex' => '/(?:md5|sha1)\s*\([^)]*(?:password|senha|pass)\b/i',
                'severity' => 'medium',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'Hash fraco (md5/sha1) usado para senhas. Utilize password_hash()/bcrypt/argon2.',
            ],
            [
                'name' => 'Validação de upload baseada apenas no tipo',
                'regex' => '/\$_(?:FILES)\b[^;]*\[[\'"]type[\'"]\]/is',
                'severity' => 'medium',
                'category' => self::CATEGORY_CODE_QUALITY,
                'description' => 'Upload validado apenas pelo Content-Type enviado pelo cliente, que pode ser falsificado. Valide também a extensão e o conteúdo real.',
            ],
        ];
    }
}
