<?php

namespace App\Http\Controllers;

use App\Models\StreamLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class StreamLogController extends Controller
{
	public function get(Request $request) {
	
	}	

	public function getDevice() {
		$agent = new Agent();
		$device = '';

		if($agent->isDesktop()) {
			$device = 'Desktop';
		} else if ($agent->isPhone()) {
			$device = 'Phone';
		} else if ($agent->isMobile()) {
			$device = 'Mobile';
		} else if ($agent->isTablet()) {
			$device = 'Tablet';
		} else if ($agent->isRobot()) {
			$device = 'Robot';
		} else {
			return $device;
		}
		return $device;
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
		$request['agent_device'] = $this->getDevice();
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
