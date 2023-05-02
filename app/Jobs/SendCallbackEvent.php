<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
class SendCallbackEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appId;
    protected $deviceId;
    protected $event;

    public function __construct($appId, $deviceId, $event)
    {
        $this->appId = $appId;
        $this->deviceId = $deviceId;
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $endpoint = 'https://example.com/subscription-event';


        $response = Http::post($endpoint, [
            'appId' => $this->appId,
            'deviceId' => $this->deviceId,
            'event' => $this->event,
        ]);

        //$response->status() !== 200 && $response->status() !== 201
        if ($response->failed()) {
            //        $this->release(60); // 1 dakika sonra tekrar dene
            $delay = now()->addMinutes(1);
            SendCallbackEvent::dispatch($this->appId,$this->deviceId, $this->event)->delay($delay);
        }
    }
}
