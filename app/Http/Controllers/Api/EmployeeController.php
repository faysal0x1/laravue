<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $service) {}

    public function index(Request $request): JsonResponse
    {
        $employees = model_cache(Employee::class, 300, fn () => $this->service->paginateData($request));

        return api_success('Employees fetched successfully.', ['employees' => $employees]);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->service->create($request->validated());

        return api_success(
            'Employee created successfully.',
            ['employee' => $this->service->toData($employee)],
            201,
        );
    }

    public function show(Employee $employee): JsonResponse
    {
        return api_success('Employee fetched successfully.', [
            'employee' => $this->service->toData($employee),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $updated = $this->service->update($employee, $request->validated());

        return api_success('Employee updated successfully.', [
            'employee' => $this->service->toData($updated),
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->service->delete($employee);

        return api_success('Employee deleted successfully.');
    }
}
