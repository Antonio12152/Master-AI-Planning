<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

describe('AdminController', function () {
    beforeEach(function () {
        // Create test users
        $this->admin = User::factory()->create(['is_admin' => true, 'is_verified' => true]);
        $this->regularUser = User::factory()->create(['is_admin' => false, 'is_verified' => true]);
        $this->unverifiedUser = User::factory()->create(['is_admin' => false, 'is_verified' => false]);
        $this->inactiveUser = User::factory()->create(['is_admin' => false, 'is_active' => false]);
    });

    describe('indexUsers', function () {
        test('returns all users with pagination when admin authenticated', function () {
            // Create 25 users
            User::factory(25)->create(['is_verified' => true]);

            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?per_page=10');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'users' => [
                        '*' => [
                            'id', 'name', 'email', 'is_active', 'is_verified', 'is_admin',
                            'created_at', 'updated_at',
                        ]
                    ],
                    'pagination' => [
                        'total', 'per_page', 'current_page', 'last_page'
                    ]
                ]);

            $data = $response->json();
            expect($data['pagination']['total'])->toBeGreaterThan(25);
            expect($data['pagination']['per_page'])->toBe(10);
        });

        test('forbids access for non-admin users', function () {
            $response = $this->apiAs($this->regularUser)
                ->getJson('/api/admin/users');

            $response->assertStatus(403)
                ->assertJsonPath('message', 'Unauthorized. Admin access required.');
        });

        test('filters users by is_active status', function () {
            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?is_active=false');

            $response->assertStatus(200);
            $users = $response->json('users');
            
            foreach ($users as $user) {
                expect($user['is_active'])->toBeFalse();
            }
        });

        test('filters users by is_verified status', function () {
            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?is_verified=true');

            $response->assertStatus(200);
            $users = $response->json('users');
            
            foreach ($users as $user) {
                expect($user['is_verified'])->toBeTrue();
            }
        });

        test('filters users by is_admin status', function () {
            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?is_admin=true');

            $response->assertStatus(200);
            $users = $response->json('users');
            
            foreach ($users as $user) {
                expect($user['is_admin'])->toBeTrue();
            }
        });

        test('searches users by name', function () {
            $user = User::factory()->create(['name' => 'John Doe', 'is_verified' => true]);
            
            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?search=John');

            $response->assertStatus(200);
            $users = $response->json('users');
            
            expect($users)->toHaveLength(1);
            expect($users[0]['name'])->toBe('John Doe');
        });

        test('searches users by email', function () {
            $user = User::factory()->create(['email' => 'unique@example.com', 'is_verified' => true]);
            
            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?search=unique');

            $response->assertStatus(200);
            $users = $response->json('users');
            
            expect($users)->toHaveLength(1);
            expect($users[0]['email'])->toBe('unique@example.com');
        });

        test('sorts users by created_at descending by default', function () {
            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?per_page=100');

            $response->assertStatus(200);
            $users = $response->json('users');
            
            // Last user created should appear first (desc)
            expect($users[0]['created_at'])->toBeGreaterThanOrEqual($users[1]['created_at']);
        });

        test('sorts users by name', function () {
            User::factory()->create(['name' => 'Alice', 'is_verified' => true]);
            User::factory()->create(['name' => 'Bob', 'is_verified' => true]);
            User::factory()->create(['name' => 'Charlie', 'is_verified' => true]);

            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?sort_by=name&sort_order=asc&per_page=100');

            $response->assertStatus(200);
            $users = $response->json('users');
            $names = array_column($users, 'name');
            
            expect($names)->toEqual(array_values(array_unique(array_filter($names))));
        });

        test('respects per_page limit and caps at 100', function () {
            User::factory(150)->create(['is_verified' => true]);

            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?per_page=200');

            $response->assertStatus(200);
            expect($response->json('pagination.per_page'))->toBeLessThanOrEqual(100);
        });

        test('returns paginated results correctly', function () {
            User::factory(25)->create(['is_verified' => true]);

            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users?per_page=10&page=2');

            $response->assertStatus(200);
            $data = $response->json();
            
            expect($data['pagination']['current_page'])->toBe(2);
            expect($data['pagination']['per_page'])->toBe(10);
        });

        test('requires authentication', function () {
            $response = $this->getJson('/api/admin/users');

            $response->assertStatus(401);
        });

        test('requires email verification', function () {
            $unverifiedAdmin = User::factory()->create([
                'is_admin' => true,
                'email_verified_at' => null
            ]);

            $response = $this->apiAs($unverifiedAdmin)
                ->getJson('/api/admin/users');

            $response->assertStatus(403); // blocked by 'verified' middleware
        });
    });

    describe('showUser', function () {
        test('returns individual user details when admin authenticated', function () {
            $response = $this->apiAs($this->admin)
                ->getJson("/api/admin/users/{$this->regularUser->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => [
                        'id', 'name', 'email', 'avatar_url', 'bio', 'timezone',
                        'is_active', 'is_verified', 'is_admin',
                        'created_at', 'updated_at', 'last_login_at', 'email_verified_at'
                    ]
                ])
                ->assertJsonPath('user.email', $this->regularUser->email)
                ->assertJsonPath('user.name', $this->regularUser->name);
        });

        test('forbids access for non-admin users', function () {
            $response = $this->apiAs($this->regularUser)
                ->getJson("/api/admin/users/{$this->regularUser->id}");

            $response->assertStatus(403)
                ->assertJsonPath('message', 'Unauthorized. Admin access required.');
        });

        test('returns 404 for non-existent user', function () {
            $response = $this->apiAs($this->admin)
                ->getJson('/api/admin/users/99999');

            $response->assertStatus(404);
        });

        test('returns user status flags correctly', function () {
            $response = $this->apiAs($this->admin)
                ->getJson("/api/admin/users/{$this->unverifiedUser->id}");

            $response->assertStatus(200)
                ->assertJsonPath('user.is_verified', false)
                ->assertJsonPath('user.is_admin', false);
        });

        test('shows inactive users', function () {
            $response = $this->apiAs($this->admin)
                ->getJson("/api/admin/users/{$this->inactiveUser->id}");

            $response->assertStatus(200)
                ->assertJsonPath('user.is_active', false);
        });

        test('does not expose password or sensitive tokens', function () {
            $response = $this->apiAs($this->admin)
                ->getJson("/api/admin/users/{$this->regularUser->id}");

            $response->assertStatus(200);
            $json = $response->json('user');
            
            expect(array_key_exists('password', $json))->toBeFalse();
            expect(array_key_exists('remember_token', $json))->toBeFalse();
        });

        test('requires authentication', function () {
            $response = $this->getJson("/api/admin/users/{$this->regularUser->id}");

            $response->assertStatus(401);
        });
    });

    describe('updateUserStatus', function () {
        test('updates user is_active status when admin authenticated', function () {
            expect($this->regularUser->is_active)->toBeTrue();

            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$this->regularUser->id}/status", [
                    'is_active' => false
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('user.is_active', false);

            $this->regularUser->refresh();
            expect($this->regularUser->is_active)->toBeFalse();
        });

        test('updates user is_verified status when admin authenticated', function () {
            expect($this->unverifiedUser->is_verified)->toBeFalse();

            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$this->unverifiedUser->id}/status", [
                    'is_verified' => true
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('user.is_verified', true);

            $this->unverifiedUser->refresh();
            expect($this->unverifiedUser->is_verified)->toBeTrue();
        });

        test('promotes user to admin', function () {
            expect($this->regularUser->is_admin)->toBeFalse();

            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$this->regularUser->id}/status", [
                    'is_admin' => true
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('user.is_admin', true);

            $this->regularUser->refresh();
            expect($this->regularUser->is_admin)->toBeTrue();
        });

        test('demotes user from admin', function () {
            $adminUser = User::factory()->create(['is_admin' => true, 'is_verified' => true]);

            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$adminUser->id}/status", [
                    'is_admin' => false
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('user.is_admin', false);

            $adminUser->refresh();
            expect($adminUser->is_admin)->toBeFalse();
        });

        test('updates multiple status fields at once', function () {
            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$this->regularUser->id}/status", [
                    'is_active' => false,
                    'is_verified' => false,
                    'is_admin' => true
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('user.is_active', false)
                ->assertJsonPath('user.is_verified', false)
                ->assertJsonPath('user.is_admin', true);

            $this->regularUser->refresh();
            expect($this->regularUser->is_active)->toBeFalse();
            expect($this->regularUser->is_verified)->toBeFalse();
            expect($this->regularUser->is_admin)->toBeTrue();
        });

        test('forbids non-admin users from updating status', function () {
            $response = $this->apiAs($this->regularUser)
                ->putJson("/api/admin/users/{$this->unverifiedUser->id}/status", [
                    'is_verified' => true
                ]);

            $response->assertStatus(403)
                ->assertJsonPath('message', 'Unauthorized. Admin access required.');
        });

        test('rejects invalid boolean values', function () {
            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$this->regularUser->id}/status", [
                    'is_active' => 'not_a_boolean'
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors('is_active');
        });

        test('ignores extra fields', function () {
            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$this->regularUser->id}/status", [
                    'is_active' => false,
                    'email' => 'newemail@example.com',
                    'name' => 'New Name'
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('user.is_active', false);

            $this->regularUser->refresh();
            expect($this->regularUser->email)->not->toBe('newemail@example.com');
            expect($this->regularUser->name)->not->toBe('New Name');
        });

        test('returns updated_at timestamp', function () {
            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$this->regularUser->id}/status", [
                    'is_active' => false
                ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => ['updated_at']
                ]);
        });

        test('returns 404 for non-existent user', function () {
            $response = $this->apiAs($this->admin)
                ->putJson('/api/admin/users/99999/status', [
                    'is_active' => false
                ]);

            $response->assertStatus(404);
        });

        test('requires authentication', function () {
            $response = $this->putJson("/api/admin/users/{$this->regularUser->id}/status", [
                'is_active' => false
            ]);

            $response->assertStatus(401);
        });

        test('returns success message', function () {
            $response = $this->apiAs($this->admin)
                ->putJson("/api/admin/users/{$this->regularUser->id}/status", [
                    'is_active' => false
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('message', 'User status updated successfully');
        });
    });

    describe('Admin access restriction', function () {
        test('guest users cannot access admin endpoints', function () {
            $response = $this->getJson('/api/admin/users');
            $response->assertStatus(401);

            $response = $this->getJson("/api/admin/users/{$this->regularUser->id}");
            $response->assertStatus(401);

            $response = $this->putJson("/api/admin/users/{$this->regularUser->id}/status", ['is_active' => false]);
            $response->assertStatus(401);
        });

        test('regular verified users cannot access admin endpoints', function () {
            $response = $this->apiAs($this->regularUser)->getJson('/api/admin/users');
            $response->assertStatus(403);

            $response = $this->apiAs($this->regularUser)->getJson("/api/admin/users/{$this->regularUser->id}");
            $response->assertStatus(403);

            $response = $this->apiAs($this->regularUser)->putJson("/api/admin/users/{$this->regularUser->id}/status", ['is_active' => false]);
            $response->assertStatus(403);
        });

        test('unverified admin users cannot access endpoints', function () {
            $unverifiedAdmin = User::factory()->create(['is_admin' => true, 'email_verified_at' => null]);

            $response = $this->apiAs($unverifiedAdmin)->getJson('/api/admin/users');
            $response->assertStatus(403);
        });

        test('admin can access all endpoints', function () {
            $response = $this->apiAs($this->admin)->getJson('/api/admin/users');
            $response->assertStatus(200);

            $response = $this->apiAs($this->admin)->getJson("/api/admin/users/{$this->regularUser->id}");
            $response->assertStatus(200);

            $response = $this->apiAs($this->admin)->putJson("/api/admin/users/{$this->regularUser->id}/status", ['is_active' => false]);
            $response->assertStatus(200);
        });
    });
});
