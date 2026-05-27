<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\IdeaGroup;
use App\Models\Plan;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class IdeaService
{
    /**
     * Создать новую идею
     */
    public function createIdea(IdeaGroup $group, array $data, User $user): Idea
    {
        $idea = $group->ideas()->create(array_merge($data, [
            'plan_id' => $group->plan_id,
        ]));

        // Обновить счетчики
        $this->updateCounters($group);

        // Логировать
        $this->logAction($user, $group->plan_id, 'created_idea', 'idea', $idea->id);

        return $idea;
    }

    /**
     * Обновить идею
     */
    public function updateIdea(Idea $idea, array $data, User $user): Idea
    {
        $oldData = $idea->only(['text', 'description', 'status', 'priority', 'tags']);
        
        // Если изменился статус на "completed", добавить timestamp
        if (isset($data['status']) && $data['status'] === 'completed' && $oldData['status'] !== 'completed') {
            $data['completed_at'] = now();
        }
        
        $idea->update($data);

        // Логировать
        $this->logAction($user, $idea->plan_id, 'updated_idea', 'idea', $idea->id, [
            'changes' => $this->getDiff($oldData, $data),
        ]);

        return $idea;
    }

    /**
     * Удалить идею
     */
    public function deleteIdea(Idea $idea, User $user): bool
    {
        $group = $idea->group;
        $planId = $idea->plan_id;

        // Логировать
        $this->logAction($user, $planId, 'deleted_idea', 'idea', $idea->id);

        // Удалить
        $deleted = $idea->delete();

        if ($deleted) {
            // Обновить счетчики
            $this->updateCounters($group);
        }

        return $deleted;
    }

    /**
     * Переместить идею в другую группу
     */
    public function moveIdea(Idea $idea, IdeaGroup $newGroup, User $user): Idea
    {
        $oldGroup = $idea->group;

        $idea->update([
            'group_id' => $newGroup->id,
            'plan_id' => $newGroup->plan_id,
        ]);

        // Обновить счетчики обеих групп
        $this->updateCounters($oldGroup);
        $this->updateCounters($newGroup);

        // Логировать
        $this->logAction($user, $newGroup->plan_id, 'moved_idea', 'idea', $idea->id, [
            'changes' => [
                'from_group' => $oldGroup->id,
                'to_group' => $newGroup->id,
            ],
        ]);

        return $idea;
    }


    /**
     * Получить идеи группы с фильтрацией
     */
    public function getGroupIdeas(
        IdeaGroup $group,
        ?string $status = null,
        ?int $priority = null,
        ?string $search = null,
        int $perPage = 20
    ): Paginator {
        $query = $group->ideas();

        if ($status) {
            $query->byStatus($status);
        }

        if ($priority !== null) {
            $query->byPriority($priority);
        }

        if ($search) {
            $query->where('text', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        return $query->ordered()
            ->paginate($perPage);
    }

    /**
     * Получить идеи плана со статистикой
     */
    public function getPlanIdeas(
        Plan $plan,
        ?string $status = null,
        int $perPage = 20
    ): Paginator {
        $query = $plan->ideas();

        if ($status) {
            $query->byStatus($status);
        }

        return $query->with(['group:id,name', 'plan:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить идеи пользователя (все его планы)
     */
    public function getUserIdeas(User $user, int $perPage = 20): Paginator
    {
        return Idea::whereIn('plan_id', $user->accessiblePlans()->pluck('id'))
            ->with(['group:id,name', 'plan:id,name,user_id'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Завершить идею
     */
    public function completeIdea(Idea $idea, User $user): Idea
    {
        return $this->updateIdea($idea, [
            'status' => 'completed',
            'completed_at' => now(),
        ], $user);
    }

    /**
     * Отклонить идею
     */
    public function rejectIdea(Idea $idea, User $user): Idea
    {
        return $this->updateIdea($idea, [
            'status' => 'rejected',
        ], $user);
    }

    /**
     * Переместить идею в начало/конец (для сортировки)
     */
    public function reorderIdea(Idea $idea, int $newSortOrder): Idea
    {
        $idea->update(['sort_order' => $newSortOrder]);
        return $idea;
    }

    /**
     * Получить статистику по статусам (одним запросом)
     */
    public function getStatusStatistics(Plan $plan): array
    {
        $stats = $plan->ideas()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'new' => $stats['new'] ?? 0,
            'in_progress' => $stats['in_progress'] ?? 0,
            'completed' => $stats['completed'] ?? 0,
            'rejected' => $stats['rejected'] ?? 0,
        ];
    }

    /**
     * Получить статистику по приоритетам (одним запросом)
     */
    public function getPriorityStatistics(Plan $plan): array
    {
        $stats = $plan->ideas()
            ->select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority');

        return [
            'low' => $stats[0] ?? 0,
            'medium' => $stats[1] ?? 0,
            'high' => $stats[2] ?? 0,
            'critical' => $stats[3] ?? 0,
        ];
    }

    /**
     * Обновить счетчики в группе и плане
     */
    private function updateCounters(IdeaGroup $group): void
    {
        // Обновить счетчик в группе
        $groupCount = $group->ideas()->count();
        $group->update(['idea_count' => $groupCount]);

        // Обновить счетчик в плане
        $planCount = $group->plan->ideas()->count();
        $group->plan->update(['idea_count' => $planCount]);
    }

    /**
     * Логировать действие
     */
    private function logAction(
        User $user,
        int $planId,
        string $action,
        string $entityType,
        int $entityId,
        array $extra = []
    ): void {
        ActivityLog::create(array_merge([
            'user_id' => $user->id,
            'plan_id' => $planId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => [],
            'changes' => [],
        ], $extra));
    }

    /**
     * Получить разницу между старым и новым состоянием
     */
    private function getDiff(array $old, array $new): array
    {
        $changes = [];

        foreach ($new as $key => $value) {
            if (isset($old[$key]) && $old[$key] !== $value) {
                $changes[$key] = [
                    'old' => $old[$key],
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }
}
