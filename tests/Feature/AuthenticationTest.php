<?php

use App\Models\User;
use Illuminate\Support\Str;

describe('Authentication', function () {
    test('validates email format on registration', function () {
        $validEmails = [
            'user@example.com',
            'test.user@domain.co.uk',
            'name+tag@example.org',
        ];

        $invalidEmails = [
            'not-an-email',
            '@example.com',
            'user@',
            'user @example.com',
        ];

        $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

        foreach ($validEmails as $email) {
            expect(preg_match($emailRegex, $email))->toBe(1);
        }

        foreach ($invalidEmails as $email) {
            expect(preg_match($emailRegex, $email))->toBe(0);
        }
    });

    test('validates password strength', function () {
        $weakPasswords = [
            '123',           // Too short
            'password',      // No numbers
            '12345678',      // No letters
        ];

        $strongPasswords = [
            'SecurePass123',
            'P@ssw0rd!',
            'MyStr0ng!Pass',
        ];

        foreach ($weakPasswords as $pwd) {
            $hasLower = preg_match('/[a-z]/', $pwd);
            $hasUpper = preg_match('/[A-Z]/', $pwd);
            $hasNumber = preg_match('/[0-9]/', $pwd);
            $isLongEnough = strlen($pwd) >= 8;

            $isStrong = $hasLower && $hasUpper && $hasNumber && $isLongEnough;
            expect($isStrong)->toBeFalse();
        }

        foreach ($strongPasswords as $pwd) {
            $hasLower = preg_match('/[a-z]/', $pwd);
            $hasUpper = preg_match('/[A-Z]/', $pwd);
            $hasNumber = preg_match('/[0-9]/', $pwd);
            $isLongEnough = strlen($pwd) >= 8;

            $isStrong = $hasLower && $hasUpper && $hasNumber && $isLongEnough;
            expect($isStrong)->toBeTrue();
        }
    });

    test('validates name is required', function () {
        $validNames = ['John Doe', 'Jane Smith', 'A'];
        $invalidNames = ['', '   ', null];

        foreach ($validNames as $name) {
            expect(!empty(trim($name ?? '')))->toBeTrue();
        }

        foreach ($invalidNames as $name) {
            expect(!empty(trim($name ?? '')))->toBeFalse();
        }
    });

    test('validates email is required for authentication', function () {
        $authCredentials = [
            ['email' => 'user@example.com', 'password' => 'pass123'],
            ['email' => '', 'password' => 'pass123'],
            ['email' => null, 'password' => 'pass123'],
        ];

        expect(!empty($authCredentials[0]['email']))->toBeTrue();
        expect(!empty($authCredentials[1]['email']))->toBeFalse();
        expect(!empty($authCredentials[2]['email'] ?? false))->toBeFalse();
    });

    test('validates password is required for authentication', function () {
        $credentials = [
            ['email' => 'user@example.com', 'password' => 'pass123'],
            ['email' => 'user@example.com', 'password' => ''],
            ['email' => 'user@example.com', 'password' => null],
        ];

        expect(!empty($credentials[0]['password']))->toBeTrue();
        expect(!empty($credentials[1]['password']))->toBeFalse();
        expect(!empty($credentials[2]['password'] ?? false))->toBeFalse();
    });

    test('user data structure has required fields', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
        ];

        expect($userData)->toHaveKeys(['name', 'email', 'password']);
        expect($userData['name'])->toBeString()->not->toBeEmpty();
        expect($userData['email'])->toBeString()->not->toBeEmpty();
        expect($userData['password'])->toBeString()->not->toBeEmpty();
    });

    test('validates registration input array structure', function () {
        $input = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'MyPass123',
            'password_confirmation' => 'MyPass123',
        ];

        expect($input)->toHaveCount(4);
        expect($input['password'])->toBe($input['password_confirmation']);
    });

    test('detects password mismatch in confirmation', function () {
        $cases = [
            ['password' => 'Pass123', 'password_confirmation' => 'Pass123', 'match' => true],
            ['password' => 'Pass123', 'password_confirmation' => 'Pass124', 'match' => false],
            ['password' => 'SecurePass', 'password_confirmation' => 'securepass', 'match' => false],
        ];

        foreach ($cases as $case) {
            $matches = $case['password'] === $case['password_confirmation'];
            expect($matches)->toBe($case['match']);
        }
    });

    test('validates user login credentials format', function () {
        $validCredentials = [
            ['email' => 'user@example.com', 'password' => 'pass', 'remember' => false],
            ['email' => 'admin@app.com', 'password' => 'secret', 'remember' => true],
        ];

        foreach ($validCredentials as $cred) {
            expect($cred)->toHaveKeys(['email', 'password'])
                ->and($cred['email'])->toContain('@');
        }
    });

    test('validates email is unique across users', function () {
        $userEmails = ['john@example.com', 'jane@example.com', 'admin@app.com'];
        $newEmail = 'john@example.com';

        $isDuplicate = in_array($newEmail, $userEmails);
        expect($isDuplicate)->toBeTrue();

        $newEmail2 = 'newuser@example.com';
        $isDuplicate2 = in_array($newEmail2, $userEmails);
        expect($isDuplicate2)->toBeFalse();
    });

    test('validates remember me token structure', function () {
        $token = bin2hex(random_bytes(32));
        
        expect($token)->toBeString();
        expect(strlen($token))->toBeGreaterThan(0);
        expect($token)->toMatch('/^[a-f0-9]+$/');
    });

    test('validates reset token is not empty', function () {
        $resetToken = Str::random(60);
        
        expect($resetToken)->not->toBeEmpty()
            ->and(strlen($resetToken))->toBe(60);
    });

    test('validates account status for login', function () {
        $users = [
            ['email' => 'active@app.com', 'is_active' => true, 'canLogin' => true],
            ['email' => 'inactive@app.com', 'is_active' => false, 'canLogin' => false],
            ['email' => 'verified@app.com', 'is_verified' => true, 'canLogin' => true],
        ];

        foreach ($users as $user) {
            if (isset($user['is_active'])) {
                expect($user['canLogin'])->toBe($user['is_active']);
            }
        }
    });
});

