<?php

namespace Tests\Feature;

use App\Models\Scan;
use App\Models\User;
use App\Services\Scanner\SecurityScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SecurityScannerTest extends TestCase
{
    use RefreshDatabase;

    private string $projectPath;

    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(storage_path('app/cache/dependencies'));
        File::deleteDirectory(storage_path('app/temp'));

        $this->projectPath = storage_path('app/temp/'.uniqid('test_project_'));
        File::ensureDirectoryExists($this->projectPath);

        $this->createFakeProject();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->projectPath);
        File::deleteDirectory(storage_path('app/cache/dependencies'));

        parent::tearDown();
    }

    public function test_scan_analyzes_real_dependencies_and_source_code(): void
    {
        Http::fake([
            'repo.packagist.org/p2/*' => Http::response([
                'packages' => [
                    'laravel/framework' => [
                        ['version' => 'v11.0.0'],
                        ['version' => 'v12.5.0'],
                    ],
                ],
            ]),
            'api.osv.dev/*' => Http::response([
                'results' => [
                    [
                        'vulns' => [
                            [
                                'id' => 'GHSA-abc123',
                                'aliases' => ['CVE-2024-12345'],
                                'summary' => 'SQL injection em laravel/framework',
                                'severity' => [['type' => 'CVSS_V3', 'score' => '9.8']],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $scan = $this->createScan();

        $progress = [];
        $result = (new SecurityScanner)->scan($this->projectPath, $scan, function ($value) use (&$progress) {
            $progress[] = $value;
        });

        $this->assertSame(1, $result->dependencies['total']);
        $this->assertSame(1, $result->dependencies['outdated']);
        $this->assertSame('11.0.0', $result->dependencies['packages'][0]['version']);
        $this->assertSame('12.5.0', $result->dependencies['packages'][0]['latest']);
        $this->assertSame(1, $result->dependencies['vulnerable']);

        $dependencyVuln = collect($result->vulnerabilities)
            ->firstWhere('package', 'laravel/framework');

        $this->assertNotNull($dependencyVuln);
        $this->assertSame('critical', $dependencyVuln['severity']);
        $this->assertSame('CVE-2024-12345', $dependencyVuln['cve']);

        $sourceVulns = collect($result->vulnerabilities)->where('package', 'source-code');
        $this->assertGreaterThanOrEqual(3, $sourceVulns->count());

        $names = $sourceVulns->pluck('name');
        $this->assertContains('Execução de comandos do sistema', $names);
        $this->assertContains('Credencial hardcoded', $names);
        $this->assertContains('Criptografia fraca de senha', $names);

        $this->assertGreaterThan(0, count($result->config_checks));
        $this->assertNotNull($result->score);
        $this->assertSame([30, 45, 65, 85, 95], $progress);
    }

    public function test_scan_without_composer_file_reports_empty_dependencies(): void
    {
        Http::fake();

        File::delete($this->projectPath.'/composer.lock');

        $result = (new SecurityScanner)->scan($this->projectPath, $this->createScan());

        $this->assertSame(0, $result->dependencies['total']);
        $this->assertSame(0, $result->dependencies['vulnerable']);
    }

    private function createScan(): Scan
    {
        $user = User::factory()->create();

        return Scan::create([
            'user_id' => $user->id,
            'repository_url' => 'test-project',
            'scan_type' => 'upload',
            'status' => 'processing',
        ]);
    }

    private function createFakeProject(): void
    {
        File::ensureDirectoryExists($this->projectPath.'/app');

        File::put($this->projectPath.'/composer.lock', json_encode([
            'packages' => [
                [
                    'name' => 'laravel/framework',
                    'version' => 'v11.0.0',
                ],
            ],
        ]));

        File::put($this->projectPath.'/.env', implode("\n", [
            'APP_DEBUG=true',
            'APP_KEY=base64:aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'APP_URL=http://localhost:8000',
            'QUEUE_CONNECTION=sync',
            'SESSION_DRIVER=file',
        ]));

        File::put($this->projectPath.'/app/Controller.php', <<<'PHP'
<?php

public function index(Request $request)
{
    $input = $_GET['cmd'];
    shell_exec($input);

    $password = 'P@ssw0rd123';

    $query = "SELECT * FROM users WHERE id = " . $input;

    md5($password);

    if (isset($_FILES['file']['type'])) {
        $type = $_FILES['file']['type'];
    }
}
PHP);
    }
}
