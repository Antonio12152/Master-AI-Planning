<?php

namespace App\Services;

use App\Models\User;
use App\Models\Plan;
use App\Models\PlanMember;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PlanService
{
    /**
     * Создать новый план
     */
    public function createPlan(array $data, User $user): Plan
    {
        // Создать план
        $plan = $user->plans()->create($data);

        // Обязательно добавить владельца в members
        $this->addMember($plan, $user->id, 'admin');

        // Обновить счетчик
        $plan->update(['member_count' => 1]);

        // Логировать
        $this->logAction($user, $plan, 'created_plan');

        return $plan;
    }

    /**
     * Обновить план
     */
    public function updatePlan(Plan $plan, array $data, User $user): Plan
    {
        $oldData = $plan->only(['name', 'description', 'color', 'icon', 'status']);
        
        $plan->update($data);

        // Логировать
        $this->logAction($user, $plan, 'updated_plan', [
            'changes' => $this->getDiff($oldData, $data),
        ]);

        return $plan;
    }

    /**
     * Удалить план (мягкое удаление)
     */
    public function deletePlan(Plan $plan, User $user): bool
    {
        // Логировать перед удалением
        $this->logAction($user, $plan, 'deleted_plan');

        // Удалить все члены плана
        $plan->members()->delete();

        // Удалить все идеи и группы (cascade от миграции)
        return $plan->delete();
    }

    /**
     * Добавить члена в план
     */
    public function addMember(Plan $plan, int $userId, string $role = 'viewer'): PlanMember
    {
        $member = $plan->members()->create([
            'user_id' => $userId,
            'role' => $role,
        ]);

        // Обновить счетчик
        $plan->update(['member_count' => $plan->members()->count()]);

        return $member;
    }

    /**
     * Обновить роль члена
     */
    public function updateMemberRole(Plan $plan, int $userId, string $role): PlanMember
    {
        $member = $plan->members()->where('user_id', $userId)->firstOrFail();
        
        $oldRole = $member->role;
        $member->update(['role' => $role]);

        // Логировать
        // ActivityLog::create([...])

        return $member;
    }

    /**
     * Удалить члена из плана
     */
    public function removeMember(Plan $plan, int $userId): bool
    {
        $deleted = $plan->members()
            ->where('user_id', $userId)
            ->delete();

        if ($deleted) {
            // Обновить счетчик
            $plan->update(['member_count' => $plan->members()->count()]);
        }

        return $deleted > 0;
    }

    /**
     * Получить все доступные планы для пользователя
     */
    public function getUserAccessiblePlans(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->accessiblePlans()
            ->with(['ideaGroups', 'members.user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить планы, созданные пользователем
     */
    public function getUserCreatedPlans(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->plans()
            ->with(['ideaGroups', 'members.user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить планы, к которым добавлен пользователь
     */
    public function getUserSharedPlans(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->planMembers()
            ->with(['plan.ideaGroups', 'plan.members.user'])
            ->orderBy('joined_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить детали плана с вложенными данными
     */
    public function getPlanDetails(Plan $plan): Plan
    {
        return $plan->load([
            'ideaGroups' => function ($query) {
                $query->orderBy('sort_order');
            },
            'ideaGroups.ideas' => function ($query) {
                $query->orderBy('sort_order');
            },
            'members' => function ($query) {
                $query->with('user:id,name,email,avatar_url');
            },
            'activityLogs' => function ($query) {
                $query->with('user:id,name')
                    ->latest('created_at')
                    ->limit(20);
            },
        ]);
    }

    /**
     * Получить статистику по плану
     */
    public function getPlanStatistics(Plan $plan): array
    {
        return [
            'total_ideas' => $plan->ideas()->count(),
            'new_ideas' => $plan->ideas()->byStatus('new')->count(),
            'in_progress' => $plan->ideas()->byStatus('in_progress')->count(),
            'completed' => $plan->ideas()->completed()->count(),
            'rejected' => $plan->ideas()->byStatus('rejected')->count(),
            'high_priority' => $plan->ideas()->highPriority()->count(),
            'total_members' => $plan->members()->count(),
            'admins' => $plan->members()->admins()->count(),
            'editors' => $plan->members()->editors()->count(),
            'viewers' => $plan->members()->viewers()->count(),
        ];
    }

    /**
     * Архивировать план
     */
    public function archivePlan(Plan $plan, User $user): Plan
    {
        $plan->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        $this->logAction($user, $plan, 'archived_plan');

        return $plan;
    }

    /**
     * Восстановить план из архива
     */
    public function unarchivePlan(Plan $plan, User $user): Plan
    {
        $plan->update([
            'status' => 'active',
            'archived_at' => null,
        ]);

        $this->logAction($user, $plan, 'unarchived_plan');

        return $plan;
    }

    /**
     * Логировать действие
     */
    private function logAction(User $user, Plan $plan, string $action, array $extra = []): void
    {
        ActivityLog::create(array_merge([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'action' => $action,
            'entity_type' => 'plan',
            'entity_id' => $plan->id,
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