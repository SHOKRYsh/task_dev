<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LogResult implements ShouldQueue
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
        $endpoint = 'https://randomuser.me/api/';

        try {
            $response = Http::get($endpoint);

            if ($response->successful()) {
                $results = $response->json('results');

                Log::info('Random User Data Results:', ['results' => $results]);
            } else {
                Log::warning('Failed to fetch data from Random User API', ['status' => $response->status()]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch HTTP request:', ['error' => $e->getMessage()]);
        }
    }
}
