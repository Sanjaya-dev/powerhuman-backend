<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 6);

        $teamQuery = Team::query();
        //powerhuman.com/api/team?id=1
        if ($id) {
            $team = $teamQuery->find($id);
            if($team) {
                return ResponseFormatter::success($team, 'Team found');
            }

            return ResponseFormatter::error('Team not found', 404);
        }

        // powerhuman.com/api/team?name=team
        // get multiple data
        $teams = $teamQuery->where('company_id',$request->company_id);

        if($name) {
            $teams->where('name', 'like', '%' . $name . '%');
            
        }
            return ResponseFormatter::success($teams->paginate($limit), 'Team found');
    }

    public function create(CreateTeamRequest $request)
    {
        try {
            // uplaod icon
            if($request->HasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }
            // Create team
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            if(!$team) {
                throw new Exception('Team not created');
            }
            // return response
            return ResponseFormatter::success($team, 'Team created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request,$id)
    {
        try {
            // get team by id
            $team = Team::find($id);
            
            // check if team exists
            if(!$team) {
                throw new Exception('Team not found');
            }

            // uplaod icon
            if($request->HasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // update team
            $team->update([
                'name' => $request->name ? $request->name:$team->name,
                'icon' => $request->logo ? $path:$team->icon,
                'company_id' => $request->company_id
            ]);

            // return response
            return ResponseFormatter::success($team, 'Team updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $team = Team::find($id);

            // TODO: Check if team is owned by user

            if(!$team) {
                throw new Exception('Team not found');
            }
            $team->delete();
            return ResponseFormatter::success($team, 'Team deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
