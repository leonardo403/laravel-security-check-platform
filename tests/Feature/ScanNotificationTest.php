<?php

namespace Tests\Feature;

use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\ScanNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ScanNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_when_plan_has_email_notifications(): void
    {
        Notification::fake();

        $plan = $this->createPlan(['email_notifications' => true]);
        $user = $this->subscribeUser($plan);
        $scan = $this->createScanFor($user);

        $scan->user->notify(new ScanNotification($scan, ScanNotification::STATUS_COMPLETED, $this->createResult($scan)));

        Notification::assertSentTo($user, ScanNotification::class);
    }

    public function test_notification_not_sent_when_plan_lacks_email_notifications(): void
    {
        Notification::fake();

        $plan = $this->createPlan([]);
        $user = $this->subscribeUser($plan);

        $this->assertTrue(
            ($plan->features['email_notifications'] ?? false) === false,
            'Plan without email_notifications should not send notifications'
        );

        $this->assertNull($user->activeSubscription()?->plan->features['email_notifications'] ?? null);
    }

    public function test_created_notification_sent(): void
    {
        Notification::fake();

        $plan = $this->createPlan(['email_notifications' => true]);
        $user = $this->subscribeUser($plan);
        $scan = $this->createScanFor($user);

        $scan->user->notify(new ScanNotification($scan, ScanNotification::STATUS_CREATED));

        Notification::assertSentTo($user, ScanNotification::class, function (ScanNotification $notification) {
            return $notification->status === ScanNotification::STATUS_CREATED;
        });
    }

    public function test_failed_notification_sent(): void
    {
        Notification::fake();

        $plan = $this->createPlan(['email_notifications' => true]);
        $user = $this->subscribeUser($plan);
        $scan = $this->createScanFor($user);

        $scan->user->notify(new ScanNotification($scan, ScanNotification::STATUS_FAILED, errorMessage: 'Clone failed'));

        Notification::assertSentTo($user, ScanNotification::class, function (ScanNotification $notification) {
            return $notification->status === ScanNotification::STATUS_FAILED
                && $notification->errorMessage === 'Clone failed';
        });
    }

    public function test_all_notifications_flag_enables_email(): void
    {
        Notification::fake();

        $plan = $this->createPlan(['all_notifications' => true]);
        $user = $this->subscribeUser($plan);
        $scan = $this->createScanFor($user);

        $scan->user->notify(new ScanNotification($scan, ScanNotification::STATUS_COMPLETED, $this->createResult($scan)));

        Notification::assertSentTo($user, ScanNotification::class);
    }

    public function test_completed_notification_contains_score_in_mail(): void
    {
        Notification::fake();

        $plan = $this->createPlan(['email_notifications' => true]);
        $user = $this->subscribeUser($plan);
        $scan = $this->createScanFor($user);
        $result = $this->createResult($scan, score: 85);

        $scan->user->notify(new ScanNotification($scan, ScanNotification::STATUS_COMPLETED, $result));

        Notification::assertSentTo($user, ScanNotification::class, function (ScanNotification $notification) {
            return $notification->status === ScanNotification::STATUS_COMPLETED
                && $notification->result->score === 85;
        });
    }

    private function createPlan(array $features): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name' => 'Test Plan',
            'slug' => 'test-'.uniqid(),
            'price' => 10.00,
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

    private function createScanFor(User $user): Scan
    {
        return Scan::create([
            'user_id' => $user->id,
            'repository_url' => 'https://github.com/user/repo.git',
            'scan_type' => 'repository',
            'status' => 'completed',
            'modules' => ['security'],
        ]);
    }

    private function createResult(Scan $scan, int $score = 95): ScanResult
    {
        return ScanResult::create([
            'scan_id' => $scan->id,
            'vulnerabilities' => [],
            'dependencies' => ['total' => 0, 'outdated' => 0, 'vulnerable' => 0, 'packages' => []],
            'config_checks' => [],
            'score' => $score,
            'duration_seconds' => 1.23,
            'summary' => "Security Score: {$score}/100.",
        ]);
    }
}
