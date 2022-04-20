<?php

namespace App\Http\Controllers;

use App\Models\SitePreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SitePreferenceController extends Controller
{
    public function getRandom(Request $request)
	{
		$validator = $this->validator($request, 'get');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$sitePref = SitePreference::where('key', $request->key)->inRandomOrder()->first();

		$status = ($sitePref) ? 1 : 0; 

        return response()->json([
			'data' => $sitePref,
			'status' => $status
		]);
	}

	public function get(Request $request)
	{
		$validator = $this->validator($request, 'get');

		if ($validator->fails()) {
			return response()->json([
				'messaage' => $validator->errors(),
				'status' => 0
			]);
		}

		$sitePref = SitePreference::where('key', $request->key)->first();

		$status = ($sitePref) ? 1 : 0; 

        return response()->json([
			'data' => $sitePref,
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
			'value' => $request->value
        ];

        $sitePref = SitePreference::where('key', $request->key)->update($update);

        $status = ($sitePref > 0) ? 1 : 0;

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
        if($x == 'update') {

        	$rules = [
	            'key' => 'required'
	        ];

        }
        else if($x == 'get') {

        	$rules = [
	            'key' => 'required'
	        ];

        }

        $validate =  Validator::make($request->all(), $rules, $messages);

		return $validate;
    }
}
