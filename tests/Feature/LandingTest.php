<?php

namespace Tests\Feature;

use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
    }

    public function test_landing_page_loads_with_hero(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('landing.hero_title_a'))
            ->assertSee(__('landing.hero_subtitle'));
    }

    public function test_landing_shows_product_about_section(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('landing.about_title'))
            ->assertSee(__('landing.about_point1_title'));
    }

    public function test_landing_shows_analysis_modules(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('scans.module_security'))
            ->assertSee(__('scans.module_dependencies'))
            ->assertSee(__('scans.module_secrets'))
            ->assertSee(__('scans.module_code_quality'));
    }

    public function test_landing_shows_how_it_works_steps(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('landing.how_title'))
            ->assertSee(__('landing.how_step1_title'))
            ->assertSee(__('landing.how_step3_title'));
    }

    public function test_landing_shows_pricing_with_plans(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('landing.plans_title'))
            ->assertSee('$5.00')
            ->assertSee('$15.00')
            ->assertSee('$45.00')
            ->assertSee(__('plans.scans_per_month', ['count' => 4]));
    }

    public function test_landing_shows_cta_buttons_for_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('auth.login'))
            ->assertSee(__('landing.hero_cta_register'))
            ->assertSee(route('register'));
    }

    public function test_landing_shows_faq(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(__('landing.faq_title'));

        foreach (__('landing.faq') as $item) {
            $this->get('/')->assertSee($item['question']);
        }
    }
}
