<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkHourRequest;
use App\Http\Resources\WorkHourResource;
use App\Models\Payment;
use App\Models\WorkHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class WorkHourController extends Controller
{
    public function store(StoreWorkHourRequest $request): WorkHourResource
    {
        $workHour = WorkHour::query()->create($request->validated());

        return new WorkHourResource($workHour);
    }

    public function unpaidSalaries(): JsonResponse
    {
        $salaries = WorkHour::query()->where('paid', false)
            ->select('employee_id', DB::raw('SUM(hours * 100) as amount'))
            ->groupBy('employee_id')
            ->get()
            ->pluck('amount', 'employee_id');
        return response()->json(['data' => $salaries]);
    }

    public function paySalaries(): JsonResponse
    {
        $salaries = WorkHour::query()->where('paid', false)
            ->select('employee_id', DB::raw('SUM(hours * 100) as amount'))
            ->groupBy('employee_id')
            ->get();

        foreach ($salaries as $salary) {
            Payment::query()->create([
                'employee_id' => $salary->employee_id,
                'amount' => $salary->amount,
            ]);

            WorkHour::query()->where('employee_id', $salary->employee_id)->update(['paid' => true]);
        }

        return response()->json(['message' => 'Salaries paid successfully']);
    }
}
