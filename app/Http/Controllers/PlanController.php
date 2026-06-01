<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Http\Requests\AddPlanMemberRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    public function __construct(private PlanService $planService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $plans = $this->planService->getUserAccessiblePlans(
            $request->user(),
            $request->get('per_page', 15)
        );

        return response()->json($plans);
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $plan = $this->planService->createPlan(
            $request->validated(),
            $request->user()
        );

        return response()->json(
            $plan->load('ideaGroups', 'members.user'),
            201
        );
    }

    public function show(Request $request, Plan $plan): JsonResponse
    {
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'No access to this plan'
            ], 403);
        }

        $planDetails = $this->planService->getPlanDetails($plan);
        $statistics = $this->planService->getPlanStatistics($plan);

        return response()->json([
            'plan' => $planDetails,
            'statistics' => $statistics,
            'user_role' => $plan->getUserRole($request->user()),
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan = $this->planService->updatePlan(
            $plan,
            $request->validated(),
            $request->user()
        );

        return response()->json($plan);
    }

    public function destroy(Request $request, Plan $plan): JsonResponse
    {
        if ($plan->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Only the owner can delete the plan'
            ], 403);
        }

        $this->planService->deletePlan($plan, $request->user());

        return response()->json([
            'message' => 'Plan deleted'
        ], 200);
    }

    public function myCreated(Request $request): JsonResponse
    {
        $plans = $this->planService->getUserCreatedPlans(
            $request->user(),
            $request->get('per_page', 15)
        );

        return response()->json($plans);
    }

    public function myShared(Request $request): JsonResponse
    {
        $plans = $this->planService->getUserSharedPlans(
            $request->user(),
            $request->get('per_page', 15)
        );

        return response()->json($plans);
    }

    public function addMember(AddPlanMemberRequest $request, Plan $plan): JsonResponse
    {
        $member = $this->planService->addMember(
            $plan,
            $request->validated()['user_id'],
            $request->validated()['role']
        );

        return response()->json(
            $member->load('user'),
            201
        );
    }

    public function updateMember(Request $request, Plan $plan, User $user): JsonResponse
    {
        if (!$plan->canManageMembers($request->user())) {
            return response()->json([
                'message' => 'No rights to manage members'
            ], 403);
        }

        $request->validate([
            'role' => 'required|in:admin,editor,viewer',
        ]);

        $member = $this->planService->updateMemberRole(
            $plan,
            $user->id,
            $request->get('role')
        );

        return response()->json($member->load('user'));
    }

    public function removeMember(Request $request, Plan $plan, User $user): JsonResponse
    {
        if (!$plan->canManageMembers($request->user())) {
            return response()->json([
                'message' => 'No rights to manage members'
            ], 403);
        }

        if ($user->id === $request->user()->id) {
            $adminCount = $plan->members()
                ->where('role', 'admin')
                ->count();

            if ($adminCount === 1) {
                return response()->json([
                    'message' => 'You cannot remove yourself if you are the only admin'
                ], 400);
            }
        }

        $this->planService->removeMember($plan, $user->id);

        return response()->json([
            'message' => 'Member removed from plan'
        ]);
    }

    public function archive(Request $request, Plan $plan): JsonResponse
    {
        if (!$plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No rights to archive'
            ], 403);
        }

        $plan = $this->planService->archivePlan($plan, $request->user());

        return response()->json($plan);
    }

    public function unarchive(Request $request, Plan $plan): JsonResponse
    {
        if (!$plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'No rights to unarchive'
            ], 403);
        }

        $plan = $this->planService->unarchivePlan($plan, $request->user());

        return response()->json($plan);
    }
}
