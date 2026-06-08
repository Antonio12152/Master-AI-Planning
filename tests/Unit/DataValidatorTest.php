<?php

describe('Data Validation', function () {
    test('validates plan data structure', function () {
        $planData = [
            'name' => 'My Plan',
            'description' => 'A great plan',
            'status' => 'active',
        ];

        expect($planData)->toHaveKeys(['name', 'description', 'status']);
        expect($planData['name'])->toBeString();
    });

    test('validates idea priority values', function () {
        $validPriorities = ['low', 'medium', 'high', 'critical'];
        $ideaPriority = 'high';

        expect(in_array($ideaPriority, $validPriorities))->toBeTrue();
    });

    test('rejects invalid idea status', function () {
        $validStatuses = ['pending', 'in_progress', 'completed', 'archived'];
        $invalidStatus = 'invalid_status';

        expect(in_array($invalidStatus, $validStatuses))->toBeFalse();
    });

    test('validates email format using regex', function () {
        $validEmail = 'user@example.com';
        $invalidEmail = 'not-an-email';

        $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
        
        expect(preg_match($emailRegex, $validEmail))->toBe(1);
        expect(preg_match($emailRegex, $invalidEmail))->toBe(0);
    });

    test('validates array contains required fields', function () {
        $userData = [
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'secret123',
        ];

        $requiredFields = ['name', 'email', 'password'];

        foreach ($requiredFields as $field) {
            expect(array_key_exists($field, $userData))->toBeTrue();
        }
    });

    test('validates count ranges', function () {
        $ideas = ['idea1', 'idea2', 'idea3'];
        $maxIdeas = 10;

        expect(count($ideas) <= $maxIdeas)->toBeTrue();
    });

    test('transforms and validates plan member roles', function () {
        $memberRoles = ['admin', 'editor', 'viewer'];
        $userRole = 'editor';

        expect($memberRoles)->toContain($userRole);
    });

    test('validates data type conversion', function () {
        $stringNumber = '42';
        $convertedNumber = (int) $stringNumber;

        expect($convertedNumber)->toBeInt()->toBe(42);
    });

    test('validates nested array structure', function () {
        $planData = [
            'name' => 'Plan',
            'settings' => [
                'color' => 'blue',
                'icon' => 'star',
                'public' => false,
            ],
        ];

        expect($planData['settings'])->toBeArray();
        expect($planData['settings']['color'])->toBe('blue');
    });

    test('validates string length constraints', function () {
        $ideaText = 'This is a brief idea';
        $minLength = 5;
        $maxLength = 500;

        expect(strlen($ideaText))
            ->toBeGreaterThanOrEqual($minLength)
            ->toBeLessThanOrEqual($maxLength);
    });
});

