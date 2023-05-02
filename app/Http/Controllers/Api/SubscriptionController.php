<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendCallbackEvent;
use App\Models\Device;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubscriptionController extends Controller
{
    //Store
    public function verifyPurchase(Request $request)
    {
        $request->validate([
            'client_token' => 'required|exists:devices,client_token',
            'receipt' => 'required',
        ]);

        $device = Device::where('client_token', $request->client_token)->first();

        $mockResponse = $this->mockPlatform($request->receipt);

        if ($mockResponse['status']) {

            $purchase = Purchase::create([
                'device_id' => $device->id,
                'receipt' => $request->receipt,
                'status' => $mockResponse['status'],
                'expire_date' => $mockResponse['expire_date'],
            ]);

            $event = $purchase->isRenewed() ? 'renewed' : 'started';

            SendCallbackEvent::dispatch($device->app_id, $device->id,$event);

        }

        return response()->json($mockResponse);
    }


    public function history(Request $request)
    {
        $request->validate([
            'client_token' => 'required|exists:devices,client_token',
            'receipt' => 'required',
        ]);

        $device = Device::where('client_token', $request->client_token)->first();

        $purchaseHistory = Purchase::where('device_id', $device->id)
            ->select('receipt', 'status', 'expire_date', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($purchaseHistory);
    }

    private function mockPlatform($receipt)
    {
        $lastChar = substr($receipt, -1);
        $status = false;
        $expireDate = null;

        if (is_numeric($lastChar) && $lastChar % 2 == 1) {
            $status = true;
            $expireDate = now()->timezone('UTC')->subHours(6)->format('Y-m-d H:i:s');
        }

        return [
            'status' => $status,
            'expire_date' => $expireDate,
        ];
    }

    public function getSubscriptionStatus(Request $request)
    {
        $request->validate([
            'client_token' => 'required',
        ]);

        $device = Device::where('client_token', $request->client_token)->first();

        if (!$device) {
            return response()->json([
                'error' => 'Invalid client token.',
            ], 400);
        }

        $purchase = Purchase::where('device_id', $request->id)
            ->where('status', true)
            ->where('expire_date', '>', now())
            ->first();

        return response()->json([
            'is_subscribed' => $purchase ? true : false,
        ]);
    }

    public function check(Request $request)
    {
        $this->validate($request, [
            'client_token' => 'required|exists:devices,client_token',
        ]);

        $device = Device::where('client_token', $request->client_token)->first();

        if (!$device) {
            return response()->json(['error' => 'Cihaz bulunamadı.'], 404);
        }

        $subscription = Cache::remember("subscription:{$device->id}", 3600, function () use ($device) {
            return Purchase::where('device_id', $device->id)
                ->where('status', true)
                ->where('expire_date', '>', now())
                ->exists();
        });

        return response()->json([
            'status' => $subscription->status,
            'expire_date' => $subscription->expire_date,
        ]);
    }
}
