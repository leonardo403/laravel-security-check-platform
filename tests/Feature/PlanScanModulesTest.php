<?php

namespace Tests\Feature;

use App\Jobs\ProcessScan;
use App\Models\Scan;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Scanner\SecurityScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PlanScanModulesTest extends TestCase
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

    public function test_basic_plan_only_enables_config_checks(): void
    {
        $plan = $this->createPlan('basic', [
            'scan_security' => true,
            'report_basic' => true,
        ]);

        $modules = array_map(fn ($m) => $m->value, $plan->scanModules());

        $this->assertSame(['security'], $modules);
    }

    public function test_medium_plan_enables_dependencies(): void
    {
        $plan = $this->createPlan('medium', [
            'scan_security' => true,
            'scan_dependencies' => true,
        ]);

        $modules = array_map(fn ($m) => $m->value, $plan->scanModules());

        $this->assertSame(['security', 'dependencies'], $modules);
    }

    public function test_premium_plan_enables_all_modules(): void
    {
        $plan = $this->createPlan('premium', [
            'scan_security' => true,
            'scan_dependencies' => true,
            'scan_secrets' => true,
            'scan_code_quality' => true,
        ]);

        $modules = array_map(fn ($m) => $m->value, $plan->scanModules());

        $this->assertSame(['security', 'dependencies', 'secrets', 'code_quality'], $modules);
    }

    public function test_store_persists_modules_from_subscribed_plan(): void
    {
        Queue::fake();

        $plan = $this->createPlan('medium', [
            'scan_security' => true,
            'scan_dependencies' => true,
        ]);

        $user = $this->subscribeUser($plan);

        $response = $this->actingAs($user)
            ->post(route('scans.store'), [
                'repository_url' => 'https://github.com/user/repo.git',
            ]);

        $response->assertRedirect();

        $scan = Scan::query()->latest('id')->first();

        $this->assertSame(['security', 'dependencies'], $scan->modules);
        Queue::assertPushedOn('scans', ProcessScan::class);
    }

    public function test_scanner_skips_modules_not_included_in_scan(): void
    {
        Http::fake();

        $scan = $this->createScan(['security']);

        $result = (new SecurityScanner)->scan($this->projectPath, $scan);

        $this->assertTrue($result->dependencies['skipped']);
        $this->assertSame(0, $result->dependencies['total']);
        $this->assertSame(0, $result->dependencies['vulnerable']);
        $this->assertGreaterThan(0, count($result->config_checks));

        $sourceFindings = collect($result->vulnerabilities)->where('package', 'source-code');
        $this->assertSame(0, $sourceFindings->count());
        $this->assertStringContainsString('Not included in this scan', $result->summary);
    }

    public function test_scanner_filters_code_findings_when_only_secrets_enabled(): void
    {
        Http::fake();

        $scan = $this->createScan(['security', 'secrets']);

        $result = (new SecurityScanner)->scan($this->projectPath, $scan);

        $names = collect($result->vulnerabilities)
            ->where('package', 'source-code')
            ->pluck('name');

        $this->assertContains('Credencial hardcoded', $names);
        $this->assertNotContains('Execução de comandos do sistema', $names);
        $this->assertNotContains('Criptografia fraca de senha', $names);
    }

    public function test_scanner_runs_all_modules_for_full_plan(): void
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

        $progress = [];
        $scan = $this->createScan(['security', 'dependencies', 'secrets', 'code_quality']);

        $result = (new SecurityScanner)->scan($this->projectPath, $scan, function ($value) use (&$progress) {
            $progress[] = $value;
        });

        $this->assertSame([30, 45, 65, 85, 95], $progress);
        $this->assertSame(1, $result->dependencies['total']);
        $this->assertSame(1, $result->dependencies['vulnerable']);

        $names = collect($result->vulnerabilities)->pluck('name');

        $this->assertContains('Credencial hardcoded', $names);
        $this->assertContains('Execução de comandos do sistema', $names);
        $this->assertContains('Criptografia fraca de senha', $names);
        $this->assertGreaterThanOrEqual(1, count($result->config_checks));
        $this->assertStringNotContainsString('Not included', $result->summary);
    }

    private function createPlan(string $slug, array $features): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'price' => 19.90,
            'max_scans_per_month' => 10,
            'features' => $features,
            'is_active' => true,
        ]);
    }

    private function subscribeUser(SubscriptionPlan $plan): User
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
        ]);

        return $user;
    }

    private function createScan(array $modules): Scan
    {
        $user = User::factory()->create();

        return Scan::create([
            'user_id' => $user->id,
            'repository_url' => 'test-project',
            'scan_type' => 'upload',
            'status' => 'processing',
            'modules' => $modules,
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

    md5($password);
}
PHP);
    }
}
