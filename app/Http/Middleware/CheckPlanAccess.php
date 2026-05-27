<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;

class CheckPlanAccess
{
    /**
     * Проверить доступ пользователя к плану
     * 
     * Использование в routes:
     * Route::get('/plans/{plan}', [...]) ->middleware('check.plan.access');
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission  - 'view', 'edit', 'manage'
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission = 'view')
    {
        // Получить план из route параметра
        $plan = $request->route('plan');

        if (!$plan instanceof Plan) {
            return response()->json([
                'message' => 'План не найден'
            ], 404);
        }

        $user = $request->user();

        // Проверить разрешение
        if (!$this->checkPermission($plan, $user, $permission)) {
            return response()->json([
                'message' => 'У вас нет доступа к этому плану'
            ], 403);
        }

        // Логировать просмотр
        $this->logAccess($request, $plan, $permission);

        return $next($request);
    }

    /**
     * Проверить разрешение пользователя
     */
    private function checkPermission(Plan $plan, $user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        return match ($permission) {
            'view' => $plan->canView($user),
            'edit' => $plan->canEdit($user),
            'manage' => $plan->canManageMembers($user),
            default => $plan->canView($user)
        };
    }

    /**
     * Логировать доступ к плану
     */
    private function logAccess(Request $request, Plan $plan, string $permission): void
    {
        // Логирование можно добавить позже
        // ActivityLog::create([...])
    }
}
