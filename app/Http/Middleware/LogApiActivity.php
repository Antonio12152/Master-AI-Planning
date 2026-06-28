<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiActivity
{
    /**
     *   API   activity_logs
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        //   POST, PUT, DELETE, PATCH 
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     *   
     */
    private function logActivity(Request $request, Response $response): void
    {
        $user = $request->user();

        if (!$user) {
            return;
        }

        //     
        [$action, $entityType, $entityId] = $this->parseRequest($request);

        if (!$action || !$entityType) {
            return;
        }

        //   ( )
        $planId = $this->extractPlanId($request);

        ActivityLog::create([
            'user_id' => $user->id,
            'plan_id' => $planId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => [
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $response->status(),
            ],
            'changes' => $this->getChanges($request),
        ]);
    }

    /**
     *     URL
     */
    private function parseRequest(Request $request): array
    {
        $path = $request->path();
        $method = $request->method();

        // POST /api/plans → created_plan
        if ($method === 'POST' && str_contains($path, '/plans') && !str_contains($path, '/members')) {
            return ['created_plan', 'plan', null];
        }

        // PUT /api/plans/{id} → updated_plan
        if ($method === 'PUT' && str_contains($path, '/plans') && !str_contains($path, '/members')) {
            $id = $this->extractIdFromPath($path, 'plans');
            return ['updated_plan', 'plan', $id];
        }

        // DELETE /api/plans/{id} → deleted_plan
        if ($method === 'DELETE' && str_contains($path, '/plans') && !str_contains($path, '/members')) {
            $id = $this->extractIdFromPath($path, 'plans');
            return ['deleted_plan', 'plan', $id];
        }

        // POST /api/plans/{id}/groups → created_group
        if ($method === 'POST' && str_contains($path, '/groups')) {
            $id = $this->extractIdFromPath($path, 'groups');
            return ['created_group', 'group', $id];
        }

        // POST /api/groups/{id}/ideas → created_idea
        if ($method === 'POST' && str_contains($path, '/ideas')) {
            $id = $this->extractIdFromPath($path, 'ideas');
            return ['created_idea', 'idea', $id];
        }

        // PUT /api/ideas/{id} → updated_idea
        if ($method === 'PUT' && str_contains($path, '/ideas')) {
            $id = $this->extractIdFromPath($path, 'ideas');
            return ['updated_idea', 'idea', $id];
        }

        // POST /api/plans/{id}/members → added_member
        if ($method === 'POST' && str_contains($path, '/members')) {
            return ['added_member', 'plan_member', null];
        }

        return [null, null, null];
    }

    /**
     *  ID  
     */
    private function extractIdFromPath(string $path, string $entity): ?int
    {
        $segments = explode('/', $path);
        
        foreach ($segments as $i => $segment) {
            if ($segment === $entity && isset($segments[$i + 1])) {
                return (int) $segments[$i + 1];
            }
        }

        return null;
    }

    /**
     *  ID   
     */
    private function extractPlanId(Request $request): ?int
    {
        //  /api/plans/{id}
        if ($request->route('plan')) {
            return $request->route('plan')->id;
        }

        //  /api/plans/{plan_id}/groups/{id}
        if ($request->route('plan_id')) {
            return $request->route('plan_id');
        }

        //  /api/groups/{group_id}/ideas/{id}
        if ($request->route('group_id')) {
            $group = $request->route('group');
            if ($group && isset($group->plan_id)) {
                return $group->plan_id;
            }
        }

        return null;
    }

    /**
     *   (diff    )
     */
    private function getChanges(Request $request): array
    {
        //    ,    
        //    ,    
        return [
            'data' => $request->all(),
        ];
    }
}
