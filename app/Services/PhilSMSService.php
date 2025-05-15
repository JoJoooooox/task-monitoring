<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PhilSMSService
{
    protected $client;
    protected $apiKey;
    protected $senderId;
    protected $baseUrl = 'https://app.philsms.com/api/v3/sms/';

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = "1566|FaMNgJW417H1m0yAI7eeRtg6GjHQE7v7kyM5Z5YT";
        $this->senderId = "PhilSMS";
    }

    /**
     * Send SMS message
     *
     * @param string $recipient
     * @param string $message
     * @return array
     * @throws GuzzleException
     */
    public function sendSMS(?string $recipient, string $message): array
    {

        if(empty($recipient)){
            return [
                'status' => 'skipped',
                'message' => 'SMS not sent: recipient is empty'
            ];
        }
        if (strpos($recipient, '0') === 0) {
            $recipient = '+63' . substr($recipient, 1); // Convert 0936... to +63936...
        } elseif (strpos($recipient, '9') === 0) {
            $recipient = '+63' . $recipient; // Convert 912345678 to +63912345678
        }
        try {
            $response = $this->client->post($this->baseUrl . 'send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'recipient' => $recipient,
                    'message' => $message,
                    'sender_id' => $this->senderId,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            // Handle exception if necessary
            return [
                'status' => 'error',
                'message' => 'SMS sending failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check SMS balance
     *
     * @return array
     * @throws GuzzleException
     */
    public function checkBalance(): array
    {
        $response = $this->client->get($this->baseUrl . 'account/balance', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}