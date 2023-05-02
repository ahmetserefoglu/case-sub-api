<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    //
    public function register(Request $request)
    {
        $this->validate($request, [
            'uid' => 'required',
            'app_id' => 'required',
            'language' => 'required',
            'os' => 'required',
        ]);

        $device = Cache::remember("device:{$request->uid}", 3600, function () use ($request) {
            return Device::firstOrCreate(
                ['uid' => $request->uid],
                [
                    'app_id' => $request->app_id,
                    'language' => $request->language,
                    'os' => $request->os,
                    'client_token' => Str::random(32),
                ]
            );
        });

        return response()->json([
            'status' => 'OK',
            'client_token' => $device->client_token,
        ]);
    }

}
