<?php

use App\Models\User;
use App\Models\Plan;
use App\Models\PlanMember;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
    $this->otherUser = User::factory()->create(['email_verified_at' => now()]);
    $this->plan = Plan::factory()->create(['user_id' => $this->user->id]);
});

describe('PlanController - API Endpoints', function () {
    describe('index()', function () {
        test('returns paginated plans for authenticated user', function () {
            Plan::factory(3)->create(['user_id' => $this->user->id]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/plans');

            expect($response->status())->toBe(200);
            expect($response->json())->toHaveKey('data');
        });

        test('respects pagination parameter', function () {
            Plan::factory(25)->create(['user_id' => $this->user->id]);

            $response = $this->actingAs($this->user)
                ->getJson('/api/plans?per_page=10');

            expect($response->status())->toBe(200);
        });

        test('requires authentication', function () {
            $response = $this->getJson('/api/plans');

            expect($response->status())->toBe(401);
        });
    });

    describe('store()', function () {
        test('creates plan with valid data', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/plans', [
                    'name' => 'New Plan',
                    'description' => 'Test description',
                ]);

            expect($response->status())->toBe(201);
            expect($response->json())->toHaveKey('id');
            expect(Plan::where('name', 'New Plan')->exists())->toBeTrue();
        });

        test('requires valid plan data', function () {
            $response = $this->actingAs($this->user)
                ->postJson('/api/plans', []);

            expect($response->status())->toBe(422);
        });

        test('requires authentication', function () {
            $response = $this->postJson('/api/plans', [
                'name' => 'New Plan',
            ]);

            expect($response->status())->toBe(401);
        });
    });

    describe('show()', function () {
        test('returns plan details for authorized user', function () {
            $response = $this->actingAs($this->user)
                ->getJson("/api/plans/{$this->plan->id}");

            expect($response->status())->toBe(200);
            expect($response->json())->toHaveKey('plan');
        });

        test('forbids access when user cannot view plan', function () {
            $otherPlan = Plan::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/plans/{$otherPlan->id}");

            expect($response->status())->toBe(403);
        });

        test('returns 404 for non-existent plan', function () {
            $response = $this->actingAs($this->user)
                ->getJson('/api/plans/999999');

            expect($response->status())->toBe(404);
        });
    });

    describe('update()', function () {
        test('updates plan with valid data', function () {
            $response = $this->actingAs($this->user)
                ->putJson("/api/plans/{$this->plan->id}", [
                    'name' => 'Updated Plan',
                    'description' => 'Updated description',
                ]);

            expect($response->status())->toBe(200);
            expect(Plan::find($this->plan->id)->name)->toBe('Updated Plan');
        });

        test('forbids update when user cannot edit plan', function () {
            $otherPlan = Plan::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->actingAs($this->user)
                ->putJson("/api/plans/{$otherPlan->id}", [
                    'name' => 'Hacked',
                ]);

            expect($response->status())->toBe(403);
        });
    });

    describe('destroy()', function () {
        test('deletes plan when user is owner', function () {
            $planId = $this->plan->id;

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/plans/{$this->plan->id}");

            expect($response->status())->toBe(200);
            expect(Plan::find($planId))->toBeNull();
        });

        test('forbids delete when user is not owner', function () {
            $otherPlan = Plan::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/plans/{$otherPlan->id}");

            expect($response->status())->toBe(403);
        });
    });

    describe('addMember()', function () {
        test('adds member to plan with valid data', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/plans/{$this->plan->id}/members", [
                    'user_id' => $this->otherUser->id,
                    'role' => 'editor',
                ]);

            expect($response->status())->toBe(201);
            expect(PlanMember::where('plan_id', $this->plan->id)
                ->where('user_id', $this->otherUser->id)
                ->exists())->toBeTrue();
        });

        test('forbids adding member when user cannot manage', function () {
            $otherPlan = Plan::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/plans/{$otherPlan->id}/members", [
                    'user_id' => $this->user->id,
                    'role' => 'viewer',
                ]);

            expect($response->status())->toBe(403);
        });
    });

    describe('removeMember()', function () {
        test('removes member from plan', function () {
            PlanMember::create([
                'plan_id' => $this->plan->id,
                'user_id' => $this->otherUser->id,
                'role' => 'editor',
            ]);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/plans/{$this->plan->id}/members/{$this->otherUser->id}");

            expect($response->status())->toBe(200);
            expect(PlanMember::where('plan_id', $this->plan->id)
                ->where('user_id', $this->otherUser->id)
                ->exists())->toBeFalse();
        });

        test('forbids removing member when user cannot manage', function () {
            $otherPlan = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            PlanMember::create([
                'plan_id' => $otherPlan->id,
                'user_id' => $this->user->id,
                'role' => 'viewer',
            ]);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/plans/{$otherPlan->id}/members/{$this->otherUser->id}");

            expect($response->status())->toBe(403);
        });
    });
});
