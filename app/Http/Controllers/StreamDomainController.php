<?php

namespace App\Http\Controllers;

use App\Models\StreamDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StreamDomainController extends Controller
{
    public function get(Request $request)
	{
		$validator = $this->validator($request, 'get');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$streamDomain = StreamDomain::find($request->id);

		$status = ($streamDomain) ? 1 : 0; 

        return response()->json([
			'data' => $streamDomain,
			'status' => $status
		]);
	}

	public function list(Request $request)
	{
		$orWhere_columns = [
            'stream_domain.domain_id',
            'stream_domain.stream_id',
        ];

        $key = ($request->search_key) ? $request->search_key : '';

        if($request->search_key){
            $key = $request->search_key;
        }

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'id';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';

		$streamDomain = StreamDomain::with('stream')->with('domain')->where(function ($q) use ($orWhere_columns, $key) {
                            foreach ($orWhere_columns as $column) {
                                $q->orWhere($column, 'ILIKE', "%{$key}%");
                            }
                        });

        if($request->from && $request->to){
            $streamDomain = $streamDomain->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

        $streamDomain = $streamDomain->orderBy($sort_column, $sort_order)->paginate($limit);

        return response()->json([
			'data' => $streamDomain,
			'status' => 1
		]);
	}

	public function create(Request $request)
	{
		$validator = $this->validator($request, 'create');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$streamDomain = StreamDomain::create([
			'domain_id' => $request->domain_id,
			'stream_id' => $request->stream_id,
			'status' => $request->status
		]);

        // ActivityLog::create([
        //     'admin_id' => auth('admin')->user()->id,
        //     'key' => 'Stream Domain',
        //     'action' => 'Create',
        // ]);

		$status = ($streamDomain) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

	public function edit(Request $request)
	{
		$validator = $this->validator($request, 'edit');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$streamDomain = StreamDomain::find($request->id);

		$status = ($streamDomain) ? 1 : 0; 

        return response()->json([
			'data' => $streamDomain,
			'status' => $status
		]);
	}

	public function update(Request $request)
	{

		$validator = $this->validator($request, 'update');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'domain_id' => $request->domain_id,
			'stream_id' => $request->stream_id,
			'status' => $request->status
        ];

        $streamDomain = StreamDomain::where('id', $request->id)->update($update);

        // ActivityLog::create([
        //     'admin_id' => auth('admin')->user()->id,
        //     'key' => 'Stream Domain',
        //     'action' => 'Update',
        // ]);

        $status = ($streamDomain > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
		
	}

	public function updateStatus(Request $request)
	{

		$validator = $this->validator($request, 'update-status');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'status' => $request->status
        ];

        $stream = StreamDomain::where('id', $request->id)->update($update);

        // ActivityLog::create([
        //     'admin_id' => auth('admin')->user()->id,
        //     'key' => 'Stream Domain',
        //     'action' => 'Update Status',
        // ]);

        $status = ($stream > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
		
	}

	public function delete(Request $request)
	{
		$validator = $this->validator($request, 'delete');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$streamDomain = StreamDomain::where('id', $request->id)->delete();

        // ActivityLog::create([
        //     'admin_id' => auth('admin')->user()->id,
        //     'key' => 'Stream Domain',
        //     'action' => 'Delete',
        // ]);

     	$status = ($streamDomain > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
	}

	private function validator(Request $request, $x)
    {
    	//custom validation error messages.
        $messages = [
            'required' => 'The :attribute field is required.',
            'unique' => 'The :attribute field is already exist'
        ];

        //validate the request.
        if($x == 'create') {

        	$rules = [
	            'domain_id' => 'required',
	            'stream_id' => 'required',
	            'status' => 'required|digits_between:0,1',
	        ];

        }
        else if($x == 'update') {

        	$rules = [
        		'id' => 'required|integer',
	            'domain_id' => 'required',
	            'stream_id' => 'required',
	            'status' => 'required|digits_between:0,1',
	        ];

        }
        else if($x == 'update-status') {

        	$rules = [
        		'id' => 'required|integer',
	            'status' => 'required|digits_between:0,1',
	        ];

        }
        else if($x == 'get' || $x == 'edit' || $x == 'delete') {

        	$rules = [
	            'id' => 'required|integer'
	        ];

        }

        $validate =  Validator::make($request->all(), $rules, $messages);

		return $validate;
    }
}
