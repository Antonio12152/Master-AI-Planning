<?php

use App\Models\User;
use App\Models\Plan;
use App\Models\IdeaGroup;
use App\Models\Idea;

describe('Model Instances', function () {
    test('can instantiate user model', function () {
        $user = new User();
        expect($user)->toBeInstanceOf(User::class);
    });

    test('can instantiate plan model', function () {
        $plan = new Plan();
        expect($plan)->toBeInstanceOf(Plan::class);
    });

    test('can instantiate idea group model', function () {
        $group = new IdeaGroup();
        expect($group)->toBeInstanceOf(IdeaGroup::class);
    });

    test('can instantiate idea model', function () {
        $idea = new Idea();
        expect($idea)->toBeInstanceOf(Idea::class);
    });
});

describe('Model Fillable Properties', function () {
    test('user model is fillable', function () {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        expect($user->name)->toBe('John Doe');
        expect($user->email)->toBe('john@example.com');
    });

    test('plan model is fillable', function () {
        $plan = new Plan([
            'name' => 'My Plan',
            'description' => 'Plan description',
            'status' => 'active',
        ]);

        expect($plan->name)->toBe('My Plan');
        expect($plan->description)->toBe('Plan description');
        expect($plan->status)->toBe('active');
    });

    test('idea group model is fillable', function () {
        $group = new IdeaGroup([
            'name' => 'Frontend Tasks',
        ]);

        expect($group->name)->toBe('Frontend Tasks');
    });

    test('idea model is fillable', function () {
        $idea = new Idea([
            'text' => 'Create login page',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        expect($idea->text)->toBe('Create login page');
        expect($idea->status)->toBe('pending');
        expect($idea->priority)->toBe('high');
    });
});

describe('Model Relationships Structure', function () {
    test('user model has plans relationship method', function () {
        $user = new User();
        expect(method_exists($user, 'plans'))->toBeTrue();
    });

    test('plan model has user relationship method', function () {
        $plan = new Plan();
        expect(method_exists($plan, 'user'))->toBeTrue();
    });

    test('plan model has idea groups relationship method', function () {
        $plan = new Plan();
        expect(method_exists($plan, 'ideaGroups'))->toBeTrue();
    });

    test('idea group model has ideas relationship method', function () {
        $group = new IdeaGroup();
        expect(method_exists($group, 'ideas'))->toBeTrue();
    });

    test('idea model has group relationship method', function () {
        $idea = new Idea();
        expect(method_exists($idea, 'group'))->toBeTrue();
    });
});

describe('Model Attribute Existence', function () {
    test('user model can set attributes', function () {
        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        
        expect($user->name)->toBe('Test User');
        expect($user->email)->toBe('test@example.com');
    });

    test('plan model can set attributes', function () {
        $plan = new Plan();
        $plan->name = 'Test Plan';
        $plan->user_id = 1;
        $plan->status = 'active';
        
        expect($plan->name)->toBe('Test Plan');
        expect($plan->user_id)->toBe(1);
        expect($plan->status)->toBe('active');
    });

    test('idea group model can set attributes', function () {
        $group = new IdeaGroup();
        $group->name = 'Ideas';
        $group->plan_id = 1;
        
        expect($group->name)->toBe('Ideas');
        expect($group->plan_id)->toBe(1);
    });

    test('idea model can set attributes', function () {
        $idea = new Idea();
        $idea->text = 'Do something';
        $idea->group_id = 1;
        $idea->status = 'pending';
        $idea->priority = 'high';
        
        expect($idea->text)->toBe('Do something');
        expect($idea->group_id)->toBe(1);
        expect($idea->status)->toBe('pending');
        expect($idea->priority)->toBe('high');
    });
});

describe('Model Collections', function () {
    test('user plans relationship returns collection', function () {
        $user = new User();
        $plansMethod = method_exists($user, 'plans');
        
        expect($plansMethod)->toBeTrue();
    });

    test('plan idea groups relationship returns collection', function () {
        $plan = new Plan();
        $groupsMethod = method_exists($plan, 'ideaGroups');
        
        expect($groupsMethod)->toBeTrue();
    });

    test('idea group ideas relationship returns collection', function () {
        $group = new IdeaGroup();
        $ideasMethod = method_exists($group, 'ideas');
        
        expect($ideasMethod)->toBeTrue();
    });

    test('idea belongs to group relationship exists', function () {
        $idea = new Idea();
        $groupMethod = method_exists($idea, 'group');
        
        expect($groupMethod)->toBeTrue();
    });
});

describe('Model Type Checking', function () {
    test('user is eloquent model', function () {
        $user = new User();
        expect($user)->toBeInstanceOf(\Illuminate\Database\Eloquent\Model::class);
    });

    test('plan is eloquent model', function () {
        $plan = new Plan();
        expect($plan)->toBeInstanceOf(\Illuminate\Database\Eloquent\Model::class);
    });

    test('idea group is eloquent model', function () {
        $group = new IdeaGroup();
        expect($group)->toBeInstanceOf(\Illuminate\Database\Eloquent\Model::class);
    });

    test('idea is eloquent model', function () {
        $idea = new Idea();
        expect($idea)->toBeInstanceOf(\Illuminate\Database\Eloquent\Model::class);
    });
});

