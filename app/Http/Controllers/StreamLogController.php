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
			'user_id',
			'site_id',
			'iu',
			'stream_no',
			'parent_referrer_url',
			'referrer_url',
			'browser_url',
			'hls_url',
			'ip_address',
			'agent_device',
			'agent_platform',
			'agent_browser',
			'agent_robot',
			'note'
        ];

        $key = ($request->search_key) ? $request->search_key : '';

        if($request->search_key){
            $key = $request->search_key;
        }

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'id';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';

		$streamLog = StreamLog::where(function ($q) use ($orWhere_columns, $key) {
                            foreach ($orWhere_columns as $column) {
                                $q->orWhere($column, 'ILIKE', "%{$key}%");
                            }
                        });

        if($request->from && $request->to){
            $streamLog = $streamLog->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

        $streamLog = $streamLog->orderBy($sort_column, $sort_order)->paginate($limit);

        return response()->json([
			'data' => $streamLog,
			'status' => 1
		]);

	}

	public function create(Request $request)
	{
		$date = Carbon::now();
		$agent = new Agent();
		$platform = $agent->platform();
		$browser = $agent->browser();
		$request['agent_device'] = $this->getDevice();
		$request['agent_platform'] = $platform . ' - ' . $agent->version($platform);
		$request['agent_browser'] = $browser . ' - ' . $agent->version($browser);
		$request['agent_robot'] = $agent->robot();
		$request['created_at_bigint'] = Carbon::parse($date)->format('YmdHis');
		$request['created_at_date_bigint'] = Carbon::parse($date)->format('Ymd');
		$request['note'] = json_encode($request['note']);

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
