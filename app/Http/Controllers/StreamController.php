<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\StreamDomain;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class StreamController extends Controller
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

		$stream = Stream::find($request->id);

		$status = ($stream) ? 1 : 0; 

        return response()->json([
			'data' => $stream,
			'status' => $status
		]);
	}

    public function generate(Request $request) {

        $status = 0;
        $url = '';

        if($request->rtmp && $request->hash){
            $rtmp = $request->rtmp;
            $key = $request->hash;
            $length = strlen($request->rtmp);
            $e = strtotime('+12 hours', time());
            $raw_hash = $key . $rtmp . '?p=' . $length . '&e=' . $e;
            $hash = md5($raw_hash);
            $url = $rtmp . 'manifest.m3u8' . '?p=' . $length . '&e=' . $e . '&h=' . $hash;

            $status = 1;
        }

        return response()->json([
			'data' => $url,
			'status' => $status
		]);

    }

	public function list(Request $request)
	{
		$orWhere_columns = [
            'stream.name',
            'stream.rtmp',
            'stream.hash',
            'stream.time'
        ];

        $key = ($request->search_key) ? $request->search_key : '';

        if($request->search_key){
            $key = $request->search_key;
        }

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'name';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'asc';

		$stream = Stream::where(function ($q) use ($orWhere_columns, $key) {
                            foreach ($orWhere_columns as $column) {
                                $q->orWhere($column, 'ILIKE', "%{$key}%");
                            }
                        });

        if($request->from && $request->to){
            $stream = $stream->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

        $stream = $stream->orderBy($sort_column, $sort_order)->paginate($limit);

        return response()->json([
			'data' => $stream,
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

		$stream = Stream::create([
			'name' => $request->name,
			'rtmp' => $request->rtmp,
			'hash' => $request->hash,
			'time' => $request->time,
			'status' => $request->status
		]);
		$status = ($stream) ? 1 : 0;

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

		$stream = Stream::find($request->id);

		$status = ($stream) ? 1 : 0; 

        return response()->json([
			'data' => $stream,
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
			'name' => $request->name,
			'rtmp' => $request->rtmp,
			'hash' => $request->hash,
			'time' => $request->time,
			'status' => $request->status
        ];

        $stream = Stream::where('id', $request->id)->update($update);

        $status = ($stream > 0) ? 1 : 0;

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

        $stream = Stream::where('id', $request->id)->update($update);

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

		$stream = Stream::where('id', $request->id)->delete();
		
		StreamDomain::where('stream_id', $request->id)->delete();

     	$status = ($stream > 0) ? 1 : 0;

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
	            'name' => 'required|unique:stream',
	            'rtmp' => 'required|unique:stream',
	            'hash' => 'required',
	            'time' => 'required',
	            'status' => 'required|digits_between:0,1',
	        ];

        }
        else if($x == 'update') {

        	$rules = [
        		'id' => 'required|integer',
	            'name' => 'required|unique:stream,name,' . $request->id,
	            'rtmp' => 'required|unique:stream,rtmp,' . $request->id,
	            'hash' => 'required',
	            'time' => 'required',
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
