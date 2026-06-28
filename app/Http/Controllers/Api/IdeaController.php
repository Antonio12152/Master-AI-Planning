<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIdeaRequest;
use App\Http\Requests\UpdateIdeaRequest;
use App\Models\Idea;
use App\Models\IdeaGroup;
use App\Models\Plan;
use App\Services\IdeaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IdeaController extends Controller
{
    public function __construct(private IdeaService $ideaService)
    {
    }

    public function indexByPlan(Request $request, Plan $plan): JsonResponse
    {
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'No access to this plan'
            ], 403);
        }

        $ideas = $this->ideaService->getPlanIdeas(
            $plan,
            $request->get('status'),
            $request->get('per_page', 20)
        );

        $statistics = [
            'by_status' => $this->ideaService->getStatusStatistics($plan),
            'by_priority' => $this->ideaService->getPriorityStatistics($plan),
        ];

        return response()->json([
            'ideas' => $ideas,
            'statistics' => $statistics,
        ]);
    }

    public function indexByGroup(Request $request, IdeaGroup $group): JsonResponse
    {
        if (!$group->plan->canView($request->user())) {
            return response()->json([
                'message' => 'No access'
            ], 403);
        }

        $ideas = $this->ideaService->getGroupIdeas(
            $group,
            $request->get('status'),
            $request->get('priority'),
            $request->get('search'),
            $request->get('per_page', 20)
        );

        return response()->json($ideas);
    }

    public function store(StoreIdeaRequest $request, IdeaGroup $group): JsonResponse
    {
        $idea = $this->ideaService->createIdea(
            $group,
            $request->validated(),
            $request->user()
        );

        return response()->json(
            $idea->load('group', 'plan'),
            201
        );
    }

    public function show(Request $request, Idea $idea): JsonResponse
    {
        if (!$idea->plan->canView($request->user())) {
            return response()->json([
                'message' => 'No access'
            ], 403);
        }

        return response()->json(
            $idea->load('group', 'plan', 'plan.user')
        );
    }

    public function update(UpdateIdeaRequest $request, Idea $idea): JsonResponse
    {
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No rights to edit'
            ], 403);
        }

        $idea = $this->ideaService->updateIdea(
            $idea,
            $request->validated(),
            $request->user()
        );

        return response()->json($idea);
    }

    public function destroy(Request $request, Idea $idea): JsonResponse
    {
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No rights to delete'
            ], 403);
        }

        $this->ideaService->deleteIdea($idea, $request->user());

        return response()->json([
            'message' => 'Idea deleted'
        ]);
    }

    public function move(Request $request, Idea $idea): JsonResponse
    {
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No rights to move'
            ], 403);
        }

        $request->validate([
            'group_id' => 'required|exists:idea_groups,id',
        ]);

        $newGroup = IdeaGroup::findOrFail($request->get('group_id'));

        if (!$newGroup->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No rights to move to this group'
            ], 403);
        }

        $idea = $this->ideaService->moveIdea($idea, $newGroup, $request->user());

        return response()->json($idea);
    }

    public function complete(Request $request, Idea $idea): JsonResponse
    {
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No rights'
            ], 403);
        }

        $idea = $this->ideaService->completeIdea($idea, $request->user());

        return response()->json($idea);
    }

    public function reject(Request $request, Idea $idea): JsonResponse
    {
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No rights'
            ], 403);
        }

        $idea = $this->ideaService->rejectIdea($idea, $request->user());

        return response()->json($idea);
    }

    public function myIdeas(Request $request): JsonResponse
    {
        $ideas = $this->ideaService->getUserIdeas(
            $request->user(),
            $request->get('per_page', 20)
        );

        return response()->json($ideas);
    }

    /**
     * GET /api/plans/{plan}/groups
     * Get all idea groups for a plan
     */
    public function indexGroups(Request $request, Plan $plan): JsonResponse
    {
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'No access'
            ], 403);
        }

        $groups = $plan->ideaGroups()->with('ideas')->get();

        return response()->json([
            'data' => $groups,
        ]);
    }

    /**
     * POST /api/plans/{plan}/groups
     * Create a new idea group
     */
    public function storeGroup(Request $request, Plan $plan): JsonResponse
    {
        if (!$plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No edit rights'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|min:1|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $group = $plan->ideaGroups()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? null,
            'sort_order' => ($plan->ideaGroups()->max('sort_order') ?? -1) + 1,
        ]);

        return response()->json($group, 201);
    }

    /**
     * PUT /api/groups/{group}
     * Update an idea group
     */
    public function updateGroup(Request $request, IdeaGroup $group): JsonResponse
    {
        if (!$group->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No edit rights'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|min:1|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $group->update($validated);

        return response()->json($group);
    }

    /**
     * DELETE /api/groups/{group}
     * Delete an idea group
     */
    public function destroyGroup(Request $request, IdeaGroup $group): JsonResponse
    {
        if (!$group->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No delete rights'
            ], 403);
        }

        // Delete all ideas in this group first
        $group->ideas()->delete();
        
        // Delete the group
        $group->delete();

        return response()->json([
            'message' => 'Group deleted',
        ]);
    }
}
