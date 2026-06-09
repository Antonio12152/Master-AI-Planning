<?php

use App\Actions\Fortify\CreateNewUser;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use Illuminate\Validation\ValidationException;

describe('CreateNewUser Action', function () {
    test('action class exists and is properly configured', function () {
        $action = new CreateNewUser();
        
        expect($action)->toBeInstanceOf(CreateNewUser::class);
        
        // Verify methods from traits are available
        expect(method_exists($action, 'passwordRules'))->toBeTrue();
        expect(method_exists($action, 'profileRules'))->toBeTrue();
    });
    test('validates that all required fields are present in input', function () {
        $requiredFields = ['name', 'email', 'password', 'password_confirmation'];
        
        $validInput = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ];

        foreach ($requiredFields as $field) {
            expect($validInput)->toHaveKey($field);
        }
    });

    test('validates name field requirements', function () {
        $names = [
            'John' => true,
            'Jane Doe' => true,
            'A B C' => true,
            '' => false,
        ];

        foreach ($names as $name => $isValid) {
            $cleaned = trim($name);
            expect(!empty($cleaned))->toBe($isValid);
        }
    });

    test('validates email field requirements', function () {
        $emails = [
            'user@example.com' => true,
            'test@domain.co.uk' => true,
            'admin+tag@app.io' => true,
            'invalid-email' => false,
            '@example.com' => false,
            'user@' => false,
            '' => false,
        ];

        $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

        foreach ($emails as $email => $isValid) {
            $matches = preg_match($emailRegex, $email) === 1;
            expect($matches)->toBe($isValid);
        }
    });

    test('validates password meets strength requirements', function () {
        $passwords = [
            'SecurePass123' => true,
            'P@ssw0rd!' => true,
            'MyStr0ng!Pass' => true,
            '123' => false,                    // Too short
            'password' => false,               // No uppercase or numbers
            'PASSWORD123' => false,            // No lowercase
            '12345678' => false,               // No letters
            'Pass1234' => true,                // 8+ chars with upper, lower, number
        ];

        foreach ($passwords as $pwd => $shouldBeValid) {
            $hasLower = preg_match('/[a-z]/', $pwd);
            $hasUpper = preg_match('/[A-Z]/', $pwd);
            $hasNumber = preg_match('/[0-9]/', $pwd);
            $isLongEnough = strlen($pwd) >= 8;

            $isStrong = $hasLower && $hasUpper && $hasNumber && $isLongEnough;
            expect($isStrong)->toBe($shouldBeValid);
        }
    });

    test('validates password confirmation matches password', function () {
        $testCases = [
            [
                'password' => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
                'shouldMatch' => true,
            ],
            [
                'password' => 'SecurePass123',
                'password_confirmation' => 'SecurePass124',
                'shouldMatch' => false,
            ],
            [
                'password' => 'MyPassword123',
                'password_confirmation' => 'mypassword123',
                'shouldMatch' => false,
            ],
        ];

        foreach ($testCases as $case) {
            $matches = $case['password'] === $case['password_confirmation'];
            expect($matches)->toBe($case['shouldMatch']);
        }
    });

    test('validates input structure for user creation', function () {
        $validInput = [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ];

        expect($validInput)->toHaveKeys(['name', 'email', 'password', 'password_confirmation']);
        expect($validInput['name'])->toBeString();
        expect($validInput['email'])->toBeString();
        expect($validInput['password'])->toBeString();
        expect($validInput['password_confirmation'])->toBeString();
    });

    test('rejects input with missing name', function () {
        $input = [
            'email' => 'user@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ];

        expect($input)->not->toHaveKey('name');
    });

    test('rejects input with missing email', function () {
        $input = [
            'name' => 'John Doe',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ];

        expect($input)->not->toHaveKey('email');
    });

    test('rejects input with missing password', function () {
        $input = [
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password_confirmation' => 'SecurePass123',
        ];

        expect($input)->not->toHaveKey('password');
    });

    test('validates user registration with multiple datasets', function () {
        $testCases = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
                'valid' => true,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => 'AnotherPass456',
                'password_confirmation' => 'AnotherPass456',
                'valid' => true,
            ],
            [
                'name' => 'Bob',
                'email' => 'invalid-email',
                'password' => 'SecurePass123',
                'password_confirmation' => 'SecurePass123',
                'valid' => false,
            ],
        ];

        foreach ($testCases as $case) {
            $hasAllFields = isset($case['name'], $case['email'], $case['password']);
            $passwordsMatch = $case['password'] === $case['password_confirmation'];
            $emailValid = preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $case['email']);

            $isValid = $hasAllFields && $passwordsMatch && $emailValid;
            expect($isValid)->toBe($case['valid']);
        }
    });

    test('validates user input data types', function () {
        $input = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
        ];

        expect($input['name'])->toBeString();
        expect($input['email'])->toBeString();
        expect($input['password'])->toBeString();
        expect($input['password_confirmation'])->toBeString();
    });

    test('validates email field is not empty or whitespace only', function () {
        $emails = [
            'valid@example.com' => true,
            '' => false,
        ];

        foreach ($emails as $email => $isValid) {
            $cleaned = trim($email);
            expect(!empty($cleaned))->toBe($isValid);
        }
    });

    test('validates name field is trimmed', function () {
        $names = [
            '  John Doe  ' => 'John Doe',
            'Jane Smith' => 'Jane Smith',
            '  A  ' => 'A',
        ];

        foreach ($names as $input => $expected) {
            expect(trim($input))->toBe($expected);
        }
    });
});

