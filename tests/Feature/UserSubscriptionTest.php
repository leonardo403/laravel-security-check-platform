<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class UserSubscriptionTest extends TestCase
{
    public function test_user_has_subscription_relationship(): void
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'subscription'));
        $this->assertInstanceOf(HasOne::class, $user->subscription());
    }
}
