<?php

use App\Models\Employee;
use App\Models\WorkHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkHourTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('workHourDataProvider')]
    public function test_work_hour_validation(array $data, int $expectedStatus, string $expectedErrorField = null): void
    {
        Employee::factory()->create();

        $response = $this->postJson(route('work-hours.store'), $data);

        $response->assertStatus($expectedStatus);

        if ($expectedErrorField) {
            $response->assertJsonValidationErrors($expectedErrorField);
        } else {
            $response->assertJsonStructure(['data' => [
                'id', 'employee_id', 'date', 'hours',
                'paid', 'created_at', 'updated_at'
            ]]);
            $this->assertDatabaseHas('work_hours', [
                'employee_id' => $data['employee_id'],
                'hours' => $data['hours'],
            ]);
        }
    }

    public static function workHourDataProvider(): array
    {
        return [
            'valid data' => [
                ['employee_id' => 1, 'hours' => 8],
                201,
            ],
            'non-existent employee' => [
                ['employee_id' => 999, 'hours' => 8],
                422,
                'employee_id',
            ],
            'invalid hours' => [
                ['employee_id' => 1, 'hours' => 0],
                422,
                'hours',
            ],
            'missing employee_id' => [
                ['hours' => 8],
                422,
                'employee_id',
            ],
            'missing hours' => [
                ['employee_id' => 1],
                422,
                'hours',
            ],
            'non-numeric hours' => [
                ['employee_id' => 1, 'hours' => 'invalid'],
                422,
                'hours',
            ],
        ];
    }

    public function test_getting_unpaid_salaries()
    {
        $employee = Employee::factory()->create();

        WorkHour::query()->create([
            'employee_id' => $employee->id,
            'date' => Carbon::now()->toDateString(),
            'hours' => 8,
            'paid' => false,
        ]);

        $response = $this->getJson(route('work-hours.unpaid-salaries'));

        $response->assertStatus(200)
            ->assertJson(['data' => [$employee->id => 800]]);
    }

    public function test_paying_all_salaries()
    {
        $employee = Employee::factory()->create();

        WorkHour::query()->create([
            'employee_id' => $employee->id,
            'date' => Carbon::now()->toDateString(),
            'hours' => 8,
            'paid' => false,
        ]);

        $response = $this->postJson(route('work-hours.pay-salaries'));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Salaries paid successfully']);

        $this->assertDatabaseHas('payments', [
            'employee_id' => $employee->id,
            'amount' => 800,
        ]);

        $this->assertDatabaseMissing('work_hours', [
            'employee_id' => $employee->id,
            'paid' => false,
        ]);
    }
}
