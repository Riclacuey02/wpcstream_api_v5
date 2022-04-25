<?php

namespace App\Http\Controllers;

use App\Models\StreamLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class StreamLogController extends Controller
{
	public function get(Request $request) {
		$agent = new Agent();
		$device = $agent->device();
		$platform = $agent->platform();
		$platform_version = $agent->version($platform);
		$browser = $agent->browser();
		$browser_version = $agent->version($browser);
		$robot = $agent->robot();
		print_r($device . '<br>' . $platform.'-'.$platform_version . '<br>' . $browser.'-'.$browser_version . '<br>' . $robot);
	}	

	public function list(Request $request)
	{
		$orWhere_columns = [
			'vtoken',
			'browser_url',
			'referrer_url'
		];

		$limit = ($request->limit) ? $request->limit : 2;
		$projections = [];
		$sort_column = ($request->sort_column) ? $request->sort_column : 'created_at';
		$sort_order = ($request->sort_order) ? $request->sort_order : 'desc';
		
		$stream_logs = new StreamLog;

		if($request->search_key) {
			foreach ($orWhere_columns as $column) {
				$stream_logs = $stream_logs->orWhere($column, 'like', "%$request->search_key%");
			}
		}

		if ($request->from && $request->to) {
			$stream_logs = $stream_logs->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
		}
		
		$stream_logs = $stream_logs->orderBy($sort_column, $sort_order)->take(10)->paginate($limit, $projections);

		return response()->json([
			'data' => $stream_logs,
			'status' => 1
		]);
	}

	public function create(Request $request)
	{
		$agent = new Agent();
		$platform = $agent->platform();
		$browser = $agent->browser();
		$request['ip_address'] = $request->ip();
		$request['agent_device'] = $agent->device();
		$request['agent_platform'] = $platform . ' - ' . $agent->version($platform);
		$request['agent_browser'] = $browser . ' - ' . $agent->version($browser);
		$request['agent_robot'] = $agent->robot();
		$stream_logs = StreamLog::create($request->all());

		$status = ($stream_logs) ? 1 : 0;

		return response()->json([
			'status' => $status
		]);
	}

	public function update(Request $request)
	{
		$activity_log = StreamLog::where('_id', $request->id)->update($request->all());

		$status = ($activity_log > 0) ? 1 : 0;

		return response()->json([
			'status' => $status
		]);
		
	}

	public function delete(Request $request)
	{
		$activity_log = StreamLog::where('_id', $request->id)->delete();

	 	$status = ($activity_log > 0) ? 1 : 0;

		return response()->json([
			'status' => $status
		]);	
		
	}
}
