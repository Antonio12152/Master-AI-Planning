<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Reset all sort_order values to be group-scoped (0, 1, 2... within each group)
     * instead of plan-scoped. This ensures ordering is consistent within groups.
     */
    public function up(): void
    {
        // For each group, reassign sort_order starting from 0
        $groups = DB::table('idea_groups')->select('id')->get();
        
        foreach ($groups as $group) {
            $ideas = DB::table('ideas')
                ->where('group_id', $group->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            
            foreach ($ideas as $index => $idea) {
                DB::table('ideas')
                    ->where('id', $idea->id)
                    ->update(['sort_order' => $index]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset to all 0s on rollback
        DB::table('ideas')->update(['sort_order' => 0]);
    }
};
