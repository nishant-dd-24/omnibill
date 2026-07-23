<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\IdentityAccess\Domain\Models\User;
use Modules\Subscription\Domain\Models\Plan;
use Modules\Subscription\Domain\Models\Price;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Cache::clear();
});

it('can list active plans for tenants', function () {
    /** @var TestCase $this */
    $plan = Plan::factory()->create(['is_active' => true]);
    Price::factory()->create(['plan_id' => $plan->id, 'amount' => 1500]);

    $user = User::factory()->create(['tenant_id' => Str::uuid()->toString()]);

    $response = $this->actingAs($user)->getJson('/api/plans');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.id', $plan->id)
        ->assertJsonPath('data.0.prices.0.amount', 1500);
});

it('can create a plan as super admin', function () {
    /** @var TestCase $this */
    $user = User::factory()->create(['tenant_id' => Str::uuid()->toString()]);

    $response = $this->actingAs($user)->postJson('/api/admin/plans', [
        'name' => 'Pro Plan',
        'description' => 'Pro features',
        'is_active' => true,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Pro Plan');
});
