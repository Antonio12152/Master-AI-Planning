<?php

use App\Http\Controllers\Api\IdeaController;
use App\Models\User;
use App\Models\Plan;
use App\Models\IdeaGroup;
use App\Models\Idea;

beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);
    $this->otherUser = User::factory()->create(['email_verified_at' => now()]);
    $this->plan = Plan::factory()->create(['user_id' => $this->user->id]);
    $this->group = IdeaGroup::factory()->create(['plan_id' => $this->plan->id]);
    $this->idea = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);
});

describe('IdeaController', function () {
    describe('indexByPlan()', function () {
        test('returns ideas for plan when user has access', function () {
            Idea::factory(3)->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/plans/{$this->plan->id}/ideas");

            expect($response->status())->toBe(200);
            expect($response->json())->toHaveKey('ideas');
            expect($response->json())->toHaveKey('statistics');
        });

        test('forbids access when user cannot view plan', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/plans/{$planOther->id}/ideas");

            expect($response->status())->toBe(403);
        });

        test('filters ideas by status parameter', function () {
            Idea::factory(2)->create([
                'group_id' => $this->group->id,
                'plan_id' => $this->plan->id,
                'status' => 'pending',
            ]);
            Idea::factory(1)->create([
                'group_id' => $this->group->id,
                'plan_id' => $this->plan->id,
                'status' => 'completed',
            ]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/plans/{$this->plan->id}/ideas?status=pending");

            expect($response->status())->toBe(200);
        });

        test('respects pagination parameter', function () {
            Idea::factory(25)->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/plans/{$this->plan->id}/ideas?per_page=10");

            expect($response->status())->toBe(200);
        });
    });

    describe('indexByGroup()', function () {
        test('returns ideas for group when user has access', function () {
            Idea::factory(3)->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/idea-groups/{$this->group->id}/ideas");

            expect($response->status())->toBe(200);
            expect($response->json())->toBeArray();
        });

        test('forbids access when user cannot view plan', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupOther = IdeaGroup::factory()->create(['plan_id' => $planOther->id]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/idea-groups/{$groupOther->id}/ideas");

            expect($response->status())->toBe(403);
        });

        test('filters by status priority and search', function () {
            Idea::factory(3)->create([
                'group_id' => $this->group->id,
                'plan_id' => $this->plan->id,
                'text' => 'Test idea',
            ]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/idea-groups/{$this->group->id}/ideas?status=new&priority=2&search=Test");

            expect($response->status())->toBe(200);
        });
    });

    describe('store()', function () {
        test('creates idea when user has permission', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/idea-groups/{$this->group->id}/ideas", [
                    'text' => 'New idea text',
                    'description' => 'Detailed description',
                    'priority' => 2,
                    'status' => 'new',
                ]);

            expect($response->status())->toBe(201);
            expect($response->json())->toHaveKey('id');
            expect(Idea::where('text', 'New idea text')->exists())->toBeTrue();
        });

        test('forbids creation when user cannot edit plan', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupOther = IdeaGroup::factory()->create(['plan_id' => $planOther->id]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/idea-groups/{$groupOther->id}/ideas", [
                    'text' => 'New idea',
                ]);

            expect($response->status())->toBe(403);
        });

        test('requires valid idea data', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/idea-groups/{$this->group->id}/ideas", []);

            expect($response->status())->toBe(422);
        });
    });

    describe('show()', function () {
        test('returns idea when user has access', function () {
            $idea = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/ideas/{$idea->id}");

            expect($response->status())->toBe(200);
            expect($response->json())->toHaveKey('id', $idea->id);
        });

        test('forbids access when user cannot view plan', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupOther = IdeaGroup::factory()->create(['plan_id' => $planOther->id]);
            $ideaOther = Idea::factory()->create(['group_id' => $groupOther->id, 'plan_id' => $planOther->id]);

            $response = $this->actingAs($this->user)
                ->getJson("/api/ideas/{$ideaOther->id}");

            expect($response->status())->toBe(403);
        });
    });

    describe('update()', function () {
        test('updates idea when user has permission', function () {
            $idea = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);

            $response = $this->actingAs($this->user)
                ->putJson("/api/ideas/{$idea->id}", [
                    'text' => 'Updated text',
                    'priority' => 2,
                    'status' => 'in_progress',
                ]);

            expect($response->status())->toBe(200);
            expect(Idea::find($idea->id)->text)->toBe('Updated text');
        });

        test('forbids update when user cannot edit plan', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupOther = IdeaGroup::factory()->create(['plan_id' => $planOther->id]);
            $ideaOther = Idea::factory()->create(['group_id' => $groupOther->id, 'plan_id' => $planOther->id]);

            $response = $this->actingAs($this->user)
                ->putJson("/api/ideas/{$ideaOther->id}", [
                    'text' => 'Updated',
                ]);

            expect($response->status())->toBe(403);
        });
    });

    describe('destroy()', function () {
        test('deletes idea when user has permission', function () {
            $idea = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);
            $ideaId = $idea->id;

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/ideas/{$idea->id}");

            expect($response->status())->toBe(200);
            expect(Idea::find($ideaId))->toBeNull();
        });

        test('forbids delete when user cannot edit plan', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupOther = IdeaGroup::factory()->create(['plan_id' => $planOther->id]);
            $ideaOther = Idea::factory()->create(['group_id' => $groupOther->id, 'plan_id' => $planOther->id]);

            $response = $this->actingAs($this->user)
                ->deleteJson("/api/ideas/{$ideaOther->id}");

            expect($response->status())->toBe(403);
        });
    });

    describe('move()', function () {
        test('moves idea to another group when authorized', function () {
            $group2 = IdeaGroup::factory()->create(['plan_id' => $this->plan->id]);
            $idea = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/ideas/{$idea->id}/move", [
                    'group_id' => $group2->id,
                ]);
            expect($response->status())->toBe(200);
            expect(Idea::find($idea->id)->group_id)->toBe($group2->id);
        });
    // change controller method to move() and route to /api/ideas/{idea}/move
        test('forbids move to group in different plan', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupOther = IdeaGroup::factory()->create(['plan_id' => $planOther->id]);
            $idea = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/ideas/{$idea->id}/move", [
                    'group_id' => $groupOther->id,
                ]);

            expect($response->status())->toBe(403);
        });
    });

    describe('complete()', function () {
        test('marks idea as complete', function () {
            $idea = Idea::factory()->create([
                'group_id' => $this->group->id,
                'plan_id' => $this->plan->id,
                'status' => 'pending',
            ]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/ideas/{$idea->id}/complete");

            expect($response->status())->toBe(200);
            expect(Idea::find($idea->id)->status)->toBe('completed');
        });

        test('forbids complete without edit permission', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupOther = IdeaGroup::factory()->create(['plan_id' => $planOther->id]);
            $ideaOther = Idea::factory()->create(['group_id' => $groupOther->id, 'plan_id' => $planOther->id]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/ideas/{$ideaOther->id}/complete");

            expect($response->status())->toBe(403);
        });
    });
});
