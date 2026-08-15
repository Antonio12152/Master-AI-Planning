<?php

use App\Http\Controllers\Api\IdeaController;
use App\Models\User;
use App\Models\Plan;
use App\Models\PlanMember;
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

        test('allows creating a short idea text', function () {
            $response = $this->actingAs($this->user)
                ->postJson("/api/idea-groups/{$this->group->id}/ideas", [
                    'text' => 'Ok',
                ]);

            expect($response->status())->toBe(201);
            expect(Idea::where('text', 'Ok')->exists())->toBeTrue();
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

        test('allows creation when user can view the plan', function () {
            $planShared = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupShared = IdeaGroup::factory()->create(['plan_id' => $planShared->id]);

            PlanMember::factory()->create([
                'plan_id' => $planShared->id,
                'user_id' => $this->user->id,
                'role' => 'viewer',
            ]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/idea-groups/{$groupShared->id}/ideas", [
                    'text' => 'Shared plan idea',
                ]);

            expect($response->status())->toBe(201);
            expect(Idea::where('text', 'Shared plan idea')->exists())->toBeTrue();
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

    describe('update()', function () {
        test('updates idea sort_order when user has permission', function () {
            $response = $this->actingAs($this->user)
                ->putJson("/api/ideas/{$this->idea->id}", [
                    'sort_order' => 7,
                ]);

            expect($response->status())->toBe(200);
            expect(Idea::find($this->idea->id)->sort_order)->toBe(7);
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

        test('moves idea to another plan when authorized', function () {
            $plan2 = Plan::factory()->create(['user_id' => $this->user->id]);
            $group2 = IdeaGroup::factory()->create(['plan_id' => $plan2->id]);
            $idea = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/ideas/{$idea->id}/move", [
                    'group_id' => $group2->id,
                ]);

            expect($response->status())->toBe(200);
            $idea->refresh();
            expect($idea->group_id)->toBe($group2->id);
            expect($idea->plan_id)->toBe($plan2->id);
        });

        test('forbids move to group in a plan the user cannot edit', function () {
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

    describe('reorderPlan()', function () {
        test('reorders groups and ideas in a single batch for authorized user', function () {
            $group2 = IdeaGroup::factory()->create(['plan_id' => $this->plan->id]);

            $idea1 = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id, 'sort_order' => 0]);
            $idea2 = Idea::factory()->create(['group_id' => $this->group->id, 'plan_id' => $this->plan->id, 'sort_order' => 1]);
            $idea3 = Idea::factory()->create(['group_id' => $group2->id, 'plan_id' => $this->plan->id, 'sort_order' => 0]);

            $response = $this->actingAs($this->user)
                ->patchJson("/api/plans/{$this->plan->id}/order", [
                    'groups' => [
                        [
                            'id' => $group2->id,
                            'sort_order' => 0,
                            'ideas' => [
                                ['id' => $idea3->id, 'sort_order' => 0],
                                ['id' => $idea1->id, 'sort_order' => 1],
                            ],
                        ],
                        [
                            'id' => $this->group->id,
                            'sort_order' => 1,
                            'ideas' => [
                                ['id' => $idea2->id, 'sort_order' => 0],
                            ],
                        ],
                    ],
                ]);

            expect($response->status())->toBe(200);
            expect(IdeaGroup::find($group2->id)->sort_order)->toBe(0);
            expect(IdeaGroup::find($this->group->id)->sort_order)->toBe(1);
            expect(Idea::find($idea1->id)->group_id)->toBe($group2->id);
            expect(Idea::find($idea1->id)->sort_order)->toBe(1);
            expect(Idea::find($idea2->id)->group_id)->toBe($this->group->id);
            expect(Idea::find($idea2->id)->sort_order)->toBe(0);
            expect(Idea::find($idea3->id)->group_id)->toBe($group2->id);
            expect(Idea::find($idea3->id)->sort_order)->toBe(0);
        });

        test('forbids batch reorder when user cannot edit the plan', function () {
            $planOther = Plan::factory()->create(['user_id' => $this->otherUser->id]);
            $groupOther = IdeaGroup::factory()->create(['plan_id' => $planOther->id]);
            $ideaOther = Idea::factory()->create(['group_id' => $groupOther->id, 'plan_id' => $planOther->id]);

            $response = $this->actingAs($this->user)
                ->patchJson("/api/plans/{$planOther->id}/order", [
                    'groups' => [
                        [
                            'id' => $groupOther->id,
                            'sort_order' => 0,
                            'ideas' => [
                                ['id' => $ideaOther->id, 'sort_order' => 0],
                            ],
                        ],
                    ],
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
