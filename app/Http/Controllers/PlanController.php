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

    /**
     * GET /api/plans
     * Получить все доступные планы пользователя
     */
    public function index(Request $request): JsonResponse
    {
        $plans = $this->planService->getUserAccessiblePlans(
            $request->user(),
            $request->get('per_page', 15)
        );

        return response()->json($plans);
    }

    /**
     * POST /api/plans
     * Создать новый план
     */
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

    /**
     * GET /api/plans/{plan}
     * Получить детали плана
     */
    public function show(Request $request, Plan $plan): JsonResponse
    {
        // Middleware проверит доступ, но можно и здесь
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'Нет доступа к этому плану'
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

    /**
     * PUT /api/plans/{plan}
     * Обновить план
     */
    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan = $this->planService->updatePlan(
            $plan,
            $request->validated(),
            $request->user()
        );

        return response()->json($plan);
    }

    /**
     * DELETE /api/plans/{plan}
     * Удалить план (только владелец)
     */
    public function destroy(Request $request, Plan $plan): JsonResponse
    {
        // Только владелец может удалить
        if ($plan->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Только владелец может удалить план'
            ], 403);
        }

        $this->planService->deletePlan($plan, $request->user());

        return response()->json([
            'message' => 'План удален'
        ], 200);
    }

    /**
     * GET /api/plans/my/created
     * Получить планы, созданные пользователем
     */
    public function myCreated(Request $request): JsonResponse
    {
        $plans = $this->planService->getUserCreatedPlans(
            $request->user(),
            $request->get('per_page', 15)
        );

        return response()->json($plans);
    }

    /**
     * GET /api/plans/my/shared
     * Получить планы, к которым добавлен пользователь
     */
    public function myShared(Request $request): JsonResponse
    {
        $plans = $this->planService->getUserSharedPlans(
            $request->user(),
            $request->get('per_page', 15)
        );

        return response()->json($plans);
    }

    /**
     * POST /api/plans/{plan}/members
     * Добавить члена в план
     */
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

    /**
     * PUT /api/plans/{plan}/members/{user}
     * Обновить роль члена
     */
    public function updateMember(Request $request, Plan $plan, User $user): JsonResponse
    {
        // Проверить права
        if (!$plan->canManageMembers($request->user())) {
            return response()->json([
                'message' => 'Нет прав на управление членами'
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

    /**
     * DELETE /api/plans/{plan}/members/{user}
     * Удалить члена из плана
     */
    public function removeMember(Request $request, Plan $plan, User $user): JsonResponse
    {
        // Проверить права
        if (!$plan->canManageMembers($request->user())) {
            return response()->json([
                'message' => 'Нет прав на управление членами'
            ], 403);
        }

        // Не может удалить себя, если он единственный админ
        if ($user->id === $request->user()->id) {
            $adminCount = $plan->members()
                ->where('role', 'admin')
                ->count();

            if ($adminCount === 1) {
                return response()->json([
                    'message' => 'Не можете удалить себя, если вы единственный админ'
                ], 400);
            }
        }

        $this->planService->removeMember($plan, $user->id);

        return response()->json([
            'message' => 'Член удален из плана'
        ]);
    }

    /**
     * POST /api/plans/{plan}/archive
     * Архивировать план
     */
    public function archive(Request $request, Plan $plan): JsonResponse
    {
        if (!$plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'Нет прав на архивирование'
            ], 403);
        }

        $plan = $this->planService->archivePlan($plan, $request->user());

        return response()->json($plan);
    }

    /**
     * POST /api/plans/{plan}/unarchive
     * Восстановить план из архива
     */
    public function unarchive(Request $request, Plan $plan): JsonResponse
    {
        if (!$plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'Нет прав на восстановление'
            ], 403);
        }

        $plan = $this->planService->unarchivePlan($plan, $request->user());

        return response()->json($plan);
    }
}
