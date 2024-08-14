<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Employee;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $limit = $request->input('limit', 6);

        $employeeQuery = Employee::query();
        //powerhuman.com/api/employee?id=1
        if ($id) {
            $employee = $employeeQuery->with(['team','role'])->find($id);
            if($employee) {
                return ResponseFormatter::success($employee, 'Employee found');
            }

            return ResponseFormatter::error('Employee not found', 404);
        }

        // powerhuman.com/api/employee?name=employee
        // get multiple data
        $employees = $employeeQuery;

        if($name) {
            $employees->where('name', 'like', '%' . $name . '%');
            
        }

        if($email) {
            $employees->where('email', $email);
            
        }

        if($age) {
            $employees->where('age', $age);
            
        }

        if($phone) {
            $employees->where('phone', 'like', '%' . $phone . '%');
            
        }

        if($team_id) {
            $employees->where('team_id', $team_id);
        }

        if($role_id) {
            $employees->where('role_id', $role_id);
        }
            return ResponseFormatter::success($employees->paginate($limit), 'Employee found');
    }

    public function create(CreateEmployeeRequest $request)
    {
        try {
            // uplaod icon
            if($request->HasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }
            // Create employee
            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'gender' => $request->gender,
                'age' => $request->age,
                'phone' => $request->phone,
                'photo' => $path,
                'team_id' => $request->team_id,
                'role_id' => $request->role_id
            ]);

            if(!$employee) {
                throw new Exception('Employee not created');
            }
            // return response
            return ResponseFormatter::success($employee, 'Employee created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request,$id)
    {
        try {
            // get employee by id
            $employee = Employee::find($id);
            
            // check if employee exists
            if(!$employee) {
                throw new Exception('Employee not found');
            }

            // uplaod photo
            if($request->HasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // update employee
            $employee->update([
                'name' => isset($request->name) ? $request->name:$employee->name,
                'email' => isset($request->email) ? $request->email:$employee->email,
                'gender' => isset($request->gender) ? $request->gender:$employee->gender,
                'age' => isset($request->age) ? $request->age:$employee->age,
                'phone' => isset($request->phone) ? $request->phone:$employee->phone,
                'photo' => isset($path) ? $path:$employee->photo,
                'team_id' => isset($request->team_id) ? $request->team_id:$employee->team_id,
                'role_id' => isset($request->role_id) ? $request->role_id:$employee->role_id
            ]);

            // return response
            return ResponseFormatter::success($employee, 'Employee updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::find($id);

            // TODO: Check if employee is owned by user

            if(!$employee) {
                throw new Exception('Employee not found');
            }
            $employee->delete();
            return ResponseFormatter::success($employee, 'Employee deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
