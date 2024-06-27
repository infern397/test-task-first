<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function store(StoreEmployeeRequest $request): EmployeeResource
    {
        $employee = Employee::query()->create($request->validated());

        return new EmployeeResource($employee);
    }
}
