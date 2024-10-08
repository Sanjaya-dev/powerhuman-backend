<?php

namespace App\Http\Controllers\API;

use Exception;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Http\Requests\UpdateResponsibilityRequest;
use App\Models\Responsibility;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 6);

        $responsibilityQuery = Responsibility::query();
        //powerhuman.com/api/responsibility?id=1
        if ($id) {
            $responsibility = $responsibilityQuery->find($id);
            if($responsibility) {
                return ResponseFormatter::success($responsibility, 'Responsibility found');
            }

            return ResponseFormatter::error('Responsibility not found', 404);
        }

        // powerhuman.com/api/responsibility?role_id=1
        // get multiple data
        $responsibilities = $responsibilityQuery->where('role_id',$request->role_id);

        if($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%');
            
        }
            return ResponseFormatter::success($responsibilities->paginate($limit), 'Responsibility found');
    }

    public function create(CreateResponsibilityRequest $request)
    {
        try {
            // Create responsibility
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id
            ]);

            if(!$responsibility) {
                throw new Exception('Responsibility not created');
            }
            // return response
            return ResponseFormatter::success($responsibility, 'Responsibility created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $responsibility = Responsibility::find($id);

            // TODO: Check if responsibility is owned by user

            if(!$responsibility) {
                throw new Exception('Responsibility not found');
            }
            $responsibility->delete();
            return ResponseFormatter::success($responsibility, 'Responsibility deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
