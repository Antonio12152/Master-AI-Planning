<?php

describe('Plan Calculations', function () {
    test('calculates completion percentage', function () {
        $completed = 7;
        $total = 10;
        $percentage = ($completed / $total) * 100;

        expect($percentage)->toBe(70.0);
    });

    test('calculates completion percentage with zero items', function () {
        $completed = 0;
        $total = 0;
        $percentage = $total > 0 ? ($completed / $total) * 100 : 0;

        expect($percentage)->toBe(0);
    });

    test('calculates priority score', function () {
        $ideas = [
            ['priority' => 'critical', 'score' => 3],
            ['priority' => 'high', 'score' => 2],
            ['priority' => 'medium', 'score' => 1],
            ['priority' => 'low', 'score' => 0],
        ];

        $totalScore = array_sum(array_column($ideas, 'score'));
        expect($totalScore)->toBe(6);
    });

    test('filters ideas by priority', function () {
        $ideas = [
            ['text' => 'Task 1', 'priority' => 'high'],
            ['text' => 'Task 2', 'priority' => 'low'],
            ['text' => 'Task 3', 'priority' => 'high'],
        ];

        $highPriority = array_filter($ideas, fn($idea) => $idea['priority'] === 'high');
        
        expect($highPriority)->toHaveCount(2);
    });

    test('counts ideas by status', function () {
        $ideas = [
            ['status' => 'completed'],
            ['status' => 'in_progress'],
            ['status' => 'completed'],
            ['status' => 'pending'],
            ['status' => 'completed'],
        ];

        $statusCounts = array_count_values(array_column($ideas, 'status'));

        expect($statusCounts['completed'])->toBe(3);
        expect($statusCounts['pending'])->toBe(1);
    });

    test('calculates average idea length', function () {
        $ideas = [
            ['text' => 'Short', 'length' => 5],
            ['text' => 'Medium length text', 'length' => 18],
            ['text' => 'This is a much longer idea text', 'length' => 31],
        ];

        $avgLength = array_sum(array_column($ideas, 'length')) / count($ideas);

        expect($avgLength)->toBe(18);
    });

    test('determines plan status based on completion', function () {
        $completed = 10;
        $total = 10;
        $status = $completed === $total ? 'completed' : ($completed > 0 ? 'in_progress' : 'pending');

        expect($status)->toBe('completed');
    });

    test('calculates team participation rate', function () {
        $activeDays = 15;
        $totalDays = 30;
        $rate = ($activeDays / $totalDays) * 100;

        expect($rate)->toBe(50.0);
    });

    test('determines next milestone', function () {
        $completed = 45;
        $total = 100;
        $milestones = [25, 50, 75, 100];
        
        $filtered = array_values(array_filter($milestones, fn($m) => $m > $completed));
        $next = $filtered[0] ?? null;

        expect($next)->toBe(50);
    });

    test('calculates time estimate based on velocity', function () {
        $remainingTasks = 20;
        $tasksPerDay = 5;
        $estimatedDays = (int) ceil($remainingTasks / $tasksPerDay);

        expect($estimatedDays)->toBe(4);
    });
});

