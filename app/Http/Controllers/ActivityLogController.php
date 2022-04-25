<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
	
	public function list(Request $request)
	{
		$orWhere_columns = [
			'key',
			'action'
		];

		$limit = ($request->limit) ? $request->limit : 2;
		$projections = [];
		$sort_column = ($request->sort_column) ? $request->sort_column : 'created_at';
		$sort_order = ($request->sort_order) ? $request->sort_order : 'desc';
		
		$activity_logs = new ActivityLog;

		if($request->search_key) {
			foreach ($orWhere_columns as $column) {
				$activity_logs = $activity_logs->orWhere($column, 'like', "%$request->search_key%");
			}
		}

		if ($request->from && $request->to) {
			$activity_logs = $activity_logs->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
		}
		
		$activity_logs = $activity_logs->orderBy($sort_column, $sort_order)->take(10)->paginate($limit, $projections);

		return response()->json([
			'data' => $activity_logs,
			'status' => 1
		]);
	}

	public function create(Request $request)
	{
		$activity_logs = ActivityLog::create($request->all());

		$status = ($activity_logs) ? 1 : 0;

		return response()->json([
			'status' => $status
		]);
	}

	public function update(Request $request)
	{
		$activity_log = ActivityLog::where('_id', $request->id)->update($request->all());

		$status = ($activity_log > 0) ? 1 : 0;

		return response()->json([
			'status' => $status
		]);
		
	}

	public function delete(Request $request)
	{
		$activity_log = ActivityLog::where('_id', $request->id)->delete();

	 	$status = ($activity_log > 0) ? 1 : 0;

		return response()->json([
			'status' => $status
		]);	
		
	}
   
}
