<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIdeaRequest;
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

    /**
     * GET /api/plans/{plan}/ideas
     * Получить идеи плана с фильтрацией
     */
    public function indexByPlan(Request $request, Plan $plan): JsonResponse
    {
        // Проверить доступ
        if (!$plan->canView($request->user())) {
            return response()->json([
                'message' => 'Нет доступа к этому плану'
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

    /**
     * GET /api/groups/{group}/ideas
     * Получить идеи группы
     */
    public function indexByGroup(Request $request, IdeaGroup $group): JsonResponse
    {
        // Проверить доступ к плану
        if (!$group->plan->canView($request->user())) {
            return response()->json([
                'message' => 'Нет доступа'
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

    /**
     * POST /api/groups/{group}/ideas
     * Создать новую идею
     */
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

    /**
     * GET /api/ideas/{idea}
     * Получить детали идеи
     */
    public function show(Request $request, Idea $idea): JsonResponse
    {
        // Проверить доступ к плану
        if (!$idea->plan->canView($request->user())) {
            return response()->json([
                'message' => 'Нет доступа'
            ], 403);
        }

        return response()->json(
            $idea->load('group', 'plan', 'plan.user')
        );
    }

    /**
     * PUT /api/ideas/{idea}
     * Обновить идею
     */
    public function update(StoreIdeaRequest $request, Idea $idea): JsonResponse
    {
        // Проверить доступ
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'Нет прав на редактирование'
            ], 403);
        }

        $idea = $this->ideaService->updateIdea(
            $idea,
            $request->validated(),
            $request->user()
        );

        return response()->json($idea);
    }

    /**
     * DELETE /api/ideas/{idea}
     * Удалить идею
     */
    public function destroy(Request $request, Idea $idea): JsonResponse
    {
        // Проверить доступ
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'Нет прав на удаление'
            ], 403);
        }

        $this->ideaService->deleteIdea($idea, $request->user());

        return response()->json([
            'message' => 'Идея удалена'
        ]);
    }


    /**
     * PUT /api/ideas/{idea}/move
     * Переместить идею в другую группу
     */
    public function move(Request $request, Idea $idea): JsonResponse
    {
        // Проверить доступ
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'Нет прав на перемещение'
            ], 403);
        }

        $request->validate([
            'group_id' => 'required|exists:idea_groups,id',
        ]);

        $newGroup = IdeaGroup::findOrFail($request->get('group_id'));

        // Убедиться, что группа в том же плане
        if ($newGroup->plan_id !== $idea->plan_id) {
            return response()->json([
                'message' => 'Группа должна быть в том же плане'
            ], 400);
        }

        $idea = $this->ideaService->moveIdea($idea, $newGroup, $request->user());

        return response()->json($idea);
    }

    /**
     * POST /api/ideas/{idea}/complete
     * Завершить идею
     */
    public function complete(Request $request, Idea $idea): JsonResponse
    {
        // Проверить доступ
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'Нет прав'
            ], 403);
        }

        $idea = $this->ideaService->completeIdea($idea, $request->user());

        return response()->json($idea);
    }

    /**
     * POST /api/ideas/{idea}/reject
     * Отклонить идею
     */
    public function reject(Request $request, Idea $idea): JsonResponse
    {
        // Проверить доступ
        if (!$idea->plan->canEdit($request->user())) {
            return response()->json([
                'message' => 'Нет прав'
            ], 403);
        }

        $idea = $this->ideaService->rejectIdea($idea, $request->user());

        return response()->json($idea);
    }

    /**
     * GET /api/my-ideas
     * Получить все идеи пользователя (из его доступных планов)
     */
    public function myIdeas(Request $request): JsonResponse
    {
        $ideas = $this->ideaService->getUserIdeas(
            $request->user(),
            $request->get('per_page', 20)
        );

        return response()->json($ideas);
    }
}
