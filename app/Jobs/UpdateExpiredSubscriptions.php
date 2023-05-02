<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateExpiredSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $chunkSize = 200;

        Purchase::where('status', true)
            ->where('expire_date', '<', now())
            ->orderBy('id') // İşlem yapılacak kayıtları sıralayın
            ->chunk($chunkSize, function ($purchases) {
                foreach ($purchases as $purchase) {
                    $rateLimited = false;
                    $mockResponse = $this->mockPlatform($purchase->receipt, $rateLimited);

                    if ($rateLimited) {
                        $delay = now()->addMinutes(1); //
                        UpdateExpiredSubscriptions::dispatch()->delay($delay);
                        continue;
                    }
                    if (!$mockResponse['status']) {
                        $device = Device::find('client_token',$purchase->client_token);
                        SendCallbackEvent::dispatch($device->app_id, $device->id,'canceled');
                    }
                    $purchase->update([
                        'status' => $mockResponse['status'],
                        'expire_date' => $mockResponse['expire_date'],
                    ]);
                }
            });


    }

    private function mockPlatform($receipt, &$rateLimited = false)
    {
        $rateLimited = false;
        $lastChar = substr($receipt, -1);
        $lastTwoChars = substr($receipt, -2);
        $status = false;
        $expireDate = null;

        if (is_numeric($lastTwoChars) && $lastTwoChars % 6 == 0) {
            $rateLimited = true;
            return [
                'status' => false,
                'expire_date' => null,
            ];
        }

        if (is_numeric($lastChar) && $lastChar % 2 == 1) {
            $status = true;
            $expireDate = now()->timezone('UTC')->subHours(6)->format('Y-m-d H:i:s');
        }

        return [
            'status' => $status,
            'expire_date' => $expireDate,
        ];
    }

}
