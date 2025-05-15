<?php
// app/Services/DailyService.php
// app/Services/DailyService.php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DailyService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl = 'https://api.daily.co/v1';

    public function __construct()
    {
        $this->apiKey = config('services.daily.key');

        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
            'timeout' => 15,
            'http_errors' => false // Don't throw exceptions on HTTP errors
        ]);
    }

    public function createRoom(array $options = [])
    {
        try {
            Log::debug('Attempting to create Daily.co room', ['options' => $options]);

            $response = $this->client->post('/rooms', [
                'json' => $options
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);

            Log::debug('Daily.co API response', [
                'status' => $statusCode,
                'response' => $responseData
            ]);

            if ($statusCode !== 200) {
                throw new \Exception("API returned status {$statusCode}");
            }

            if (!isset($responseData['url'])) {
                throw new \Exception("Invalid response format - missing room URL");
            }

            return $responseData;

        } catch (\Exception $e) {
            Log::error('Daily.co API Error: ' . $e->getMessage(), [
                'exception' => $e,
                'options' => $options
            ]);
            return null;
        }
    }
}