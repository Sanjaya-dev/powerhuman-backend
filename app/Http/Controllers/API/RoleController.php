<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 6);
        $with_responsibilities = $request->input('with_responsibilities', false);

        $roleQuery = Role::query();
        //powerhuman.com/api/role?id=1
        if ($id) {
            $role = $roleQuery->with('responsibility')->find($id);
            if($role) {
                return ResponseFormatter::success($role, 'Role found');
            }

            return ResponseFormatter::error('Role not found', 404);
        }

        // powerhuman.com/api/role?company_id=1
        // get multiple data
        $roles = $roleQuery->where('company_id',$request->company_id);

        if($name) {
            $roles->where('name', 'like', '%' . $name . '%');
            
        }

        if($with_responsibilities) {
            $roles->with('responsibility');
        }

            return ResponseFormatter::success($roles->paginate($limit), 'Role found');
    }

    public function create(CreateRoleRequest $request)
    {
        try {
            // Create role
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            if(!$role) {
                throw new Exception('Role not created');
            }
            // return response
            return ResponseFormatter::success($role, 'Role created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request,$id)
    {
        try {
            // get role by id
            $role = Role::find($id);
            
            // check if role exists
            if(!$role) {
                throw new Exception('Role not found');
            }

            // update role
            $role->update([
                'name' => $request->name ? $request->name:$role->name,
                'company_id' => $request->company_id
            ]);

            // return response
            return ResponseFormatter::success($role, 'Role updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            // TODO: Check if role is owned by user

            if(!$role) {
                throw new Exception('Role not found');
            }
            $role->delete();
            return ResponseFormatter::success($role, 'Role deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
