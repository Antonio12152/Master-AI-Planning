<?php

namespace App\Services;

use App\Models\Idea;
use App\Models\IdeaGroup;
use App\Models\Plan;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class IdeaService
{
    /**
     *   
     */
    public function createIdea(IdeaGroup $group, array $data, User $user): Idea
    {
        $nextSortOrder = $data['sort_order'] ?? (($group->ideas()->max('sort_order') ?? -1) + 1);

        $idea = $group->ideas()->create(array_merge($data, [
            'plan_id' => $group->plan_id,
            'sort_order' => $nextSortOrder,
        ]));

        $this->updateCounters($group);

        $this->logAction($user, $group->plan_id, 'created_idea', 'idea', $idea->id);

        return $idea;
    }

    /**
     *  
     */
    public function updateIdea(Idea $idea, array $data, User $user): Idea
    {
        $oldData = $idea->only(['text', 'description', 'status', 'priority', 'tags']);
        
        //     "completed",  timestamp
        if (isset($data['status']) && $data['status'] === 'completed' && $oldData['status'] !== 'completed') {
            $data['completed_at'] = now();
        }
        
        $idea->update($data);

        // 
        $this->logAction($user, $idea->plan_id, 'updated_idea', 'idea', $idea->id, [
            'changes' => $this->getDiff($oldData, $data),
        ]);

        return $idea;
    }

    /**
     *  
     */
    public function deleteIdea(Idea $idea, User $user): bool
    {
        $group = $idea->group;
        $planId = $idea->plan_id;

        // 
        $this->logAction($user, $planId, 'deleted_idea', 'idea', $idea->id);

        // 
        $deleted = $idea->delete();

        if ($deleted) {
            //  
            $this->updateCounters($group);
        }

        return $deleted;
    }

    /**
     *     
     */
    public function moveIdea(Idea $idea, IdeaGroup $newGroup, User $user): Idea
    {
        $oldGroup = $idea->group;
        $oldPlanId = $idea->plan_id;

        $newSortOrder = ($newGroup->ideas()->max('sort_order') ?? -1) + 1;

        $idea->update([
            'group_id' => $newGroup->id,
            'plan_id' => $newGroup->plan_id,
            'sort_order' => $newSortOrder,
        ]);

        $this->updateCounters($oldGroup);
        $this->updateCounters($newGroup);

        if ($oldPlanId !== $newGroup->plan_id) {
            $oldPlan = Plan::find($oldPlanId);
            if ($oldPlan) {
                $oldPlan->refresh();
                $oldPlan->update(['idea_count' => $oldPlan->ideas()->count()]);
            }
            $newGroup->plan->refresh();
            $newGroup->plan->update(['idea_count' => $newGroup->plan->ideas()->count()]);
        }

        // 
        $this->logAction($user, $newGroup->plan_id, 'moved_idea', 'idea', $idea->id, [
            'changes' => [
                'from_group' => $oldGroup->id,
                'to_group' => $newGroup->id,
            ],
        ]);

        return $idea;
    }


    /**
     *     
     */
    public function getGroupIdeas(
        IdeaGroup $group,
        ?string $status = null,
        ?string $priority = null,
        ?string $search = null,
        int $perPage = 20
    ): LengthAwarePaginator
    {
        $query = $group->ideas();

        if ($status) {
            $query->byStatus($status);
        }

        if ($priority !== null) {
            $query->byPriority((int) $priority);
        }

        if ($search) {
            $query->where('text', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        return $query->ordered()
            ->paginate($perPage);
    }

    /**
     *     
     */
    public function getPlanIdeas(
        Plan $plan,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = $plan->ideas();

        if ($status) {
            $query->byStatus($status);
        }

        return $query->with(['group:id,name', 'plan:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     *    (  )
     */
    public function getUserIdeas(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return Idea::whereIn('plan_id', $user->accessiblePlans()->pluck('id'))
            ->with(['group:id,name', 'plan:id,name,user_id'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     *  
     */
    public function completeIdea(Idea $idea, User $user): Idea
    {
        return $this->updateIdea($idea, [
            'status' => 'completed',
            'completed_at' => now(),
        ], $user);
    }

    /**
     *  
     */
    public function rejectIdea(Idea $idea, User $user): Idea
    {
        return $this->updateIdea($idea, [
            'status' => 'rejected',
        ], $user);
    }

    /**
     *    / ( )
     */
    public function reorderIdea(Idea $idea, int $newSortOrder): Idea
    {
        $idea->update(['sort_order' => $newSortOrder]);
        return $idea;
    }

    /**
     *     ( )
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
     *     ( )
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
     *      
     */
    private function updateCounters(IdeaGroup $group): void
    {
        //    
        $groupCount = $group->ideas()->count();
        $group->update(['idea_count' => $groupCount]);

        //    
        $planCount = $group->plan->ideas()->count();
        $group->plan->update(['idea_count' => $planCount]);
    }

    /**
     *  
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
     *       
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
