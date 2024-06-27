<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('employeeDataProvider')]
    public function test_employee_validation(array|callable $data, int $expectedStatus, string $expectedErrorField = null): void
    {
        if (is_callable($data)) {
            $data = $data();
        }

        $response = $this->postJson(route('employees.store'), $data);

        $response->assertStatus($expectedStatus);

        if ($expectedErrorField) {
            $response->assertJsonValidationErrors($expectedErrorField);
        } else {
            $response->assertJsonStructure(['data' => ['id', 'email', 'created_at', 'updated_at']]);
            $this->assertDatabaseHas('employees', ['email' => $data['email']]);
            $this->assertTrue(Hash::check($data['password'], Employee::query()->first()->password));
        }
    }

    public static function employeeDataProvider(): array
    {
        return [
            'valid data' => [
                ['email' => 'test@example.com', 'password' => 'password'],
                201,
            ],
            'invalid email' => [
                ['email' => 'invalid-email', 'password' => 'password'],
                422,
                'email',
            ],
            'duplicate email' => [
                function () {
                    Employee::factory()->create(['email' => 'test@example.com']);
                    return ['email' => 'test@example.com', 'password' => 'password'];
                },
                422,
                'email',
            ],
            'missing email' => [
                ['email' => '', 'password' => 'password'],
                422,
                'email',
            ],
            'short password' => [
                ['email' => 'test@example.com', 'password' => 'short'],
                422,
                'password',
            ],
            'missing password' => [
                ['email' => 'test@example.com', 'password' => ''],
                422,
                'password',
            ],
        ];
    }
}
