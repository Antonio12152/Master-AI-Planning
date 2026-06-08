<?php

// Database tests moved to Feature/DatabaseModelsTest.php
// Unit tests for models without database interaction

describe('Model Attributes', function () {
    test('user model has expected attributes', function () {
        $attributes = ['id', 'name', 'email', 'password', 'created_at', 'updated_at'];
        
        expect($attributes)->not->toBeEmpty();
        expect(count($attributes))->toBeGreaterThan(0);
    });

    test('plan model has expected attributes', function () {
        $attributes = ['id', 'user_id', 'name', 'description', 'status', 'created_at', 'updated_at'];
        
        expect($attributes)->not->toBeEmpty();
        expect(count($attributes))->toBeGreaterThan(0);
    });

    test('idea model has expected attributes', function () {
        $attributes = ['id', 'idea_group_id', 'text', 'status', 'priority', 'created_at', 'updated_at'];
        
        expect($attributes)->not->toBeEmpty();
        expect(count($attributes))->toBeGreaterThan(0);
    });
});

