<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function login(Request $request)
	{
		$credentials = $request->only(['username', 'password']);
		$token = auth('admin')->attempt($credentials);
		if ($token) {
			return [
				'token' => $token,
				'status' => 1
			];
		} else {
			return ['status' => 0];
		}
	}

	public function me()
	{
		$user = auth('admin')->user();
		return response()->json([
			'data' => $user,
			'status' => 1
		]);
	}

	public function logout()
	{
		auth('admin')->logout();
		return response()->json([
		 	'status' => 1
		]);
	}

	public function list(Request $request)
	{

		$orWhere_columns = [
            'admin.username'
        ];

        $key = ($request->search_key) ? $request->search_key : '';

        if($request->search_key){
            $key = $request->search_key;
        }

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'id';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';

		$admin = Admin::where(function ($q) use ($orWhere_columns, $key) {
                            foreach ($orWhere_columns as $column) {
                                $q->orWhere($column, 'ILIKE', "%{$key}%");
                            }
                        });

		if($request->from && $request->to){
            $admin = $admin->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

		$admin = $admin->orderBy($sort_column, $sort_order)->paginate($limit);

        return response()->json([
			'data' => $admin,
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
		
		$request['password_raw'] = $request->password;
		$request['password'] = Hash::make($request->password);

		$admin = Admin::create($request->all());

		$status = ($admin) ? 1 : 0;

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

		$admin = Admin::find($request->id);

		$status = ($admin) ? 1 : 0; 

        return response()->json([
			'data' => $admin,
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

		if($request->password) {
			$request['password_raw'] = $request->password;
			$request['password'] = Hash::make($request->password);
		}

        $admin = Admin::where('id', $request->id)->update($request->all());

        $status = ($admin > 0) ? 1 : 0;

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

        $admin = Admin::where('id', $request->id)->update($update);

        $status = ($admin > 0) ? 1 : 0;

        return response()->json([
			'status' => $status
		]);
		
	}

	public function updatePassword(Request $request)
	{

		$validator = $this->validator($request, 'update-password');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$update = [
			'password' => Hash::make($request->password),
			'password_raw' => $request->password,
        ];

        $admin = Admin::where('id', $request->id)->update($update);

        $status = ($admin > 0) ? 1 : 0;

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

		$admin = Admin::where('id', $request->id)->delete();

     	$status = ($admin > 0) ? 1 : 0;

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
	            'username' => 'required|unique:admin',
	            'password' => 'required|between:5,255|confirmed',
	            'status' => 'required|digits_between:0,1',
	        ];

        }
        else if($x == 'update') {

        	$rules = [
	            'id' => 'required|integer',
	            'username' => 'unique:admin,username,' . $request->id,
	            'status' => 'digits_between:0,1',
	        ];

        }
        else if($x == 'update-status') {

        	$rules = [
        		'id' => 'required|integer',
	            'status' => 'required|digits_between:0,1',
	        ];

        }
        else if($x == 'update_password') {

        	$rules = [
	            'id' => 'required|integer',
	            'password' => 'required|between:5,255|confirmed',
	        ];

        }
        else if($x == 'edit' || $x == 'delete') {

        	$rules = [
	            'id' => 'required|integer'
	        ];

        }

        $validate =  Validator::make($request->all(), $rules, $messages);

		return $validate;
    }


    private function client_ip_address()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }
}
