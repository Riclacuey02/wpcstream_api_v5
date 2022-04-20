<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use App\Models\StreamDomain;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\SitePreference;

class DomainController extends Controller
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

        $domain = Domain::find($request->id);

        $status = ($domain) ? 1 : 0; 

        return response()->json([
            'data' => $domain,
            'status' => $status
        ]);
    }

    public function list(Request $request)
    {
        $orWhere_columns = [
            'domain.domain',
            'domain.thumbnail'
        ];

        $key = ($request->search_key) ? $request->search_key : '';

        if($request->search_key){
            $key = $request->search_key;
        }

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'id';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';

        $domain = Domain::where(function ($q) use ($orWhere_columns, $key) {
                            foreach ($orWhere_columns as $column) {
                                $q->orWhere($column, 'ILIKE', "%{$key}%");
                            }
                        });

        if($request->from && $request->to){
            $domain = $domain->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

        $domain = $domain->orderBy($sort_column, $sort_order)->paginate($limit);

        $toggle_userdata = SitePreference::where('key', 'toggle_userdata')->first();
        return response()->json([
            'data' => $domain,
            'toggle_userdata' => $toggle_userdata ? $toggle_userdata->value : 0,
            'status' => 1
        ]);
    }

    public function iframeStreamList(Request $request)
    {
        $referer = $request->referer;
        $domain = Domain::select('id', 'thumbnail')->where('domain', $referer)->get();
        
        $vtokenData = $this->encryptDecrypt($request->voucher, 'decrypt');

        if(count($domain) > 0 && $vtokenData->original['status']) {
            $iframeStreamLists[] = StreamDomain::select('stream.id', 'stream.name', 'stream.rtmp', 'stream.hash', 'stream.time', 'stream.status', 'stream_domain.id as stream_domain_id', 'stream_domain.status as stream_domain_status')
                                                    ->where('stream_domain.domain_id', $domain['0']->id)
                                                    ->where('stream_domain.status', 1)
                                                    ->rightJoin('stream', function ($join) {
                                                        $join->on('stream_domain.stream_id', '=', 'stream.id')
                                                        ->where('stream.status', '=', 1)
                                                        ->where('stream.deleted_at', '=', NULL);
                                                    })
                                                    ->inRandomOrder()->first();
            
            for($i=0; count($iframeStreamLists) > $i; $i++){
                
                $length = strlen($iframeStreamLists[$i]->rtmp);
                $epoch = strtotime($iframeStreamLists[$i]->time, time());
                $raw_hash = $iframeStreamLists[$i]->hash . $iframeStreamLists[$i]->rtmp . '?p=' . $length . '&e=' . $epoch . '&iu=' . $domain['0']->id.'10001'.$vtokenData->getData()->data->user_id;
                $hash = md5($raw_hash);
                $url = $iframeStreamLists[$i]->rtmp . 'manifest.m3u8' . '?p=' . $length . '&e=' . $epoch . '&iu=' . $domain['0']->id.'10001'.$vtokenData->getData()->data->user_id . '&h=' . $hash;

                $iframeStreamLists[$i]->generated = $url;
                $iframeStreamLists[$i]->thumbnail = $domain['0']->thumbnail;
                unset($iframeStreamLists[$i]->rtmp);
                unset($iframeStreamLists[$i]->hash);
                unset($iframeStreamLists[$i]->time);
                unset($iframeStreamLists[$i]->id);
                unset($iframeStreamLists[$i]->name);
                unset($iframeStreamLists[$i]->status);
                unset($iframeStreamLists[$i]->stream_domain_id);
                unset($iframeStreamLists[$i]->stream_domain_status);

            }

            $toggle_userdata = SitePreference::where('key', 'toggle_userdata')->first();
            return response()->json([
                'data' => $iframeStreamLists,
                'toggle_userdata' => $toggle_userdata ? $toggle_userdata->value : 0,
                'coupon' => $vtokenData->original['coupon'],
                'status' => 1
            ]);

        }

        return response()->json([
            'status' => 0
        ]);

    }

    public function monitoringStreamList(Request $request)
    {
        $domain_id = $request->id;
        $limit = ($request->limit) ? $request->limit : 2;
        if($domain_id) {
            $monitoringStreamLists = StreamDomain::select('stream.id', 'stream.name', 'stream.rtmp', 'stream.hash', 'stream.time', 'stream.status')
                                                    ->where('stream_domain.domain_id', $domain_id)
                                                    ->rightJoin('stream', function ($join) {
                                                        $join->on('stream_domain.stream_id', '=', 'stream.id')
                                                        ->where('stream.deleted_at', '=', NULL);
                                                    })
                                                    ->paginate($limit);

            for($i=0; count($monitoringStreamLists) > $i; $i++){
                
                $length = strlen($monitoringStreamLists[$i]->rtmp);
                $epoch = strtotime($monitoringStreamLists[$i]->time, time());
                $raw_hash = $monitoringStreamLists[$i]->hash . $monitoringStreamLists[$i]->rtmp . '?p=' . $length . '&e=' . $epoch;
                $hash = md5($raw_hash);
                $url = $monitoringStreamLists[$i]->rtmp . 'manifest.m3u8' . '?p=' . $length  . '&e=' . $epoch . '&h=' . $hash;

                $monitoringStreamLists[$i]->url = $url;
                unset($monitoringStreamLists[$i]->rtmp);
                unset($monitoringStreamLists[$i]->hash);
                unset($monitoringStreamLists[$i]->time);

            }

            return response()->json([
                'data' => $monitoringStreamLists,
                'status' => 1
            ]);

        }

        return response()->json([
            'status' => 0
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

        $domain = Domain::create([
            'domain' => $request->domain,
            'thumbnail' => $request->thumbnail
        ]);

        $status = ($domain) ? 1 : 0;

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

        $domain = Domain::find($request->id);

        $status = ($domain) ? 1 : 0; 

        return response()->json([
            'data' => $domain,
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
            'domain' => $request->domain,
            'thumbnail' => $request->thumbnail
        ];

        $domain = Domain::where('id', $request->id)->update($update);

        $status = ($domain > 0) ? 1 : 0;

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

        $domain = Domain::where('id', $request->id)->delete();

        StreamDomain::where('domain_id', $request->id)->delete();

        $status = ($domain > 0) ? 1 : 0;

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
                'domain' => 'required|url|unique:domain',
                'thumbnail' => 'required'
            ];

        }
        else if($x == 'update') {

            $rules = [
                'id' => 'required|integer',
                'domain' => 'required|url|unique:domain,domain,' . $request->id,
                'thumbnail' => 'required',
            ];

        }
        else if($x == 'get' || $x == 'edit' || $x == 'delete') {

            $rules = [
                'id' => 'required|integer'
            ];

        }

        $validate = Validator::make($request->all(), $rules, $messages);

        return $validate;
    }
    
    public function encryptDecrypt($string, $action = 'encrypt')
    {
        $vtokenValidation = false;

        $encryptMethod = "AES-256-CBC";
        $secretKey = '6v5asefgb8';
        $secretIv = 'tbwjb7a9u9';
        $key = hash('sha256', $secretKey);
        $iv = substr(hash('sha256', $secretIv), 0, 16);

        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encryptMethod, $key, 0, $iv);
            $output = base64_encode($output);
        } elseif ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encryptMethod, $key, 0, $iv);
        }

        $decryptedExp = explode('-', $output);
        if (count($decryptedExp) === 3) {
            $data = [
                'site_id' => $decryptedExp[0],
                'timestamp' => $decryptedExp[1],
                'user_id' => $decryptedExp[2],
            ];
        
            $reEncrypt = $this->reEncrypt($decryptedExp[0].'-'.$decryptedExp[1].'-'.$decryptedExp[2]);
            
            $vtokenValidation = true;

            return response()->json([
                'data' => $data,
                'coupon' => $reEncrypt,
                'status' => $vtokenValidation
            ]);
        }

        return response()->json([
            'status' =>$vtokenValidation
        ]);
        
    }

    private function reEncrypt($string){
        $salt = openssl_random_pseudo_bytes(256);
        $iv = openssl_random_pseudo_bytes(16);
        $passphrase = 'Pg25LJg5xG0Bqo74L0dXprowNxcmjmMZ';
        $iterations = 999;
        $key = hash_pbkdf2("sha512", $passphrase, $salt, $iterations, 64);

        $encrypted_data = openssl_encrypt($string, 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);

        $data = array("ciphertext" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "salt" => bin2hex($salt));
        return json_encode($data);
    }
}
