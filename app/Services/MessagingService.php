<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\MessageLog;
use Exception;
use Illuminate\Support\Str;

/**
 * MessagingService handles all external API communications with Alerts365 for WhatsApp and RCS.
 */
class MessagingService
{
    // API Configuration variables
    protected $apiKey;
    protected $wabaNumber;
    protected $agentId;
    protected $botId;
    protected $baseUrl;

    /**
     * Constructor: Initializes configuration from services.php config file.
     */
    public function __construct()
    {
        $this->apiKey = config('services.alerts365.api_key');
        $this->wabaNumber = config('services.alerts365.waba_number');
        $this->agentId = config('services.alerts365.agent_id');
        $this->botId = config('services.alerts365.bot_id');
        $this->baseUrl = config('services.alerts365.base_url');
    }

    /**
     * Fetch the list of available WhatsApp templates from the Alerts365 platform.
     * Used to populate the dropdown in the UI.
     */
    public function getWhatsappTemplates()
    {
        try {
            // Send POST request to fetch templates
            $response = Http::withHeaders([
                'Key' => $this->apiKey,
                'wabaNumber' => $this->wabaNumber,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/REST/directApi/getTemplateList');

            // If successful, return the JSON data containing template details
            if ($response->successful()) {
                return $response->json();
            }

            // Log error if API fails
            Log::error('Alerts365 getTemplateList API Error: ' . $response->body());
            return ['error' => 'Failed to fetch templates from API'];
        } catch (Exception $e) {
            // Catch and log any connection exceptions
            Log::error('Alerts365 getTemplateList Exception: ' . $e->getMessage());
            return ['error' => 'An error occurred while fetching templates'];
        }
    }

    /**
     * Send a WhatsApp message using a predefined template.
     * 
     * @param string $to Recipient phone number
     * @param string $templateName Name of the template to use
     * @param array $parameters Dynamic text values for the template variables ({{1}}, {{2}}, etc)
     * @param int $leadId The ID of the lead this message belongs to (for logging)
     * @param string $language Language code (default 'en')
     * @param string $headerImage Optional URL for a header image
     */
    public function sendWhatsappTemplate($to, $templateName, $parameters, $leadId = null, $language = 'en', $headerImage = null, $campaignLeadId = null)
    {
        // Convert simple array of strings into the format required by the WhatsApp API: [{"type":"text", "text":"value"}]
        $formattedParams = collect($parameters)->map(function ($value) {
            return ['type' => 'text', 'text' => (string) $value];
        })->values()->toArray();

        $components = [];

        // If a header image URL is provided, add the 'header' component to the payload
        if ($headerImage) {
            $components[] = [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'image',
                        'image' => [
                            'link' => $headerImage
                        ]
                    ]
                ]
            ];
        }

        // Add the 'body' component containing the text parameters ONLY if parameters exist
        if (!empty($formattedParams)) {
            $components[] = [
                'type' => 'body',
                'parameters' => $formattedParams
            ];
        }

        // Default to English if no language is specified
        $langCode = $language ?: 'en';

        // Build the final API payload structure for WhatsApp
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'language' => [
                    'policy' => 'deterministic',
                    'code' => $langCode
                ],
                'name' => $templateName
            ]
        ];

        // Attach components (header/body) to the payload if they were created
        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        // Log the outgoing payload for debugging purposes
        Log::info('Alerts365 WhatsApp Payload:', $payload);

        try {
            // Send the POST request to the direct message endpoint
            $response = Http::withHeaders([
                'Key' => $this->apiKey,
                'wabaNumber' => $this->wabaNumber,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/REST/directApi/message', $payload);

            // Determine if the send was successful
            $status = $response->successful() ? 'sent' : 'failed';
            $apiResponse = $response->json();

            // Log the attempt and response in the database for tracking history
            $this->logMessage([
                'lead_id' => $leadId,
                'campaign_lead_id' => $campaignLeadId,
                'type' => 'whatsapp',
                'to_number' => $to,
                'template_name' => $templateName,
                'parameters' => $parameters,
                'status' => $status,
                'api_response' => $apiResponse,
            ]);

            return [
                'success' => $response->successful(),
                'data' => $apiResponse
            ];
        } catch (Exception $e) {
            // Handle and log network or code errors
            Log::error('Alerts365 sendMessage Exception: ' . $e->getMessage());

            // Log the failure in the database
            $this->logMessage([
                'lead_id' => $leadId,
                'campaign_lead_id' => $campaignLeadId,
                'type' => 'whatsapp',
                'to_number' => $to,
                'template_name' => $templateName,
                'parameters' => $parameters,
                'status' => 'failed',
                'api_response' => ['error' => $e->getMessage()],
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fetch the raw JSON structure for a specific RCS template.
     * This is used to dynamically identify which variables (params) are needed.
     */
    public function getRcsTemplate($templateName)
    {
        // Temporary fix: Mocking responses for specific templates because API is invalid/unstable
        if ($templateName === 'customer_reach' || $templateName === 'target_audienc' || $templateName === 'campaign_1') {
            return [
                'success' => true,
                'data' => [
                    'contentMessage' => [
                        'templateMessage' => [
                            'templateCode' => $templateName,
                            'customParams' => []
                        ],
                        'botId' => $this->botId
                    ]
                ]
            ];
        }

        try {
            // Build payload for JSON body as per user instructions
            $payload = [
                'templateName' => $templateName,
                'agentId' => $this->agentId
            ];

            // Request the template structure from the API using JSON body
            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/REST/direct/getRcsTemplateJson', $payload);

            if ($response->successful()) {
                $data = $response->json();

                // The API sometimes returns customParams as a string; we decode it if necessary for easier usage
                if (isset($data['contentMessage']['templateMessage']['customParams'])) {
                    $customParams = $data['contentMessage']['templateMessage']['customParams'];
                    if (is_string($customParams) && !empty($customParams)) {
                        $decoded = json_decode($customParams, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $data['contentMessage']['templateMessage']['customParams'] = $decoded;
                        }
                    }
                }

                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            Log::error('Alerts365 getRcsTemplateJson API Error: ' . $response->body());
            return [
                'success' => false,
                'error' => 'Failed to fetch RCS template structure from API.'
            ];
        } catch (Exception $e) {
            Log::error('Alerts365 getRcsTemplateJson Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Fetch the list of available RCS templates.
     */
    public function getRcsTemplates()
    {
        // Fixed templates as requested by the user for the dropdown
        return [
            'templateList' => [
                'customer_reach',
                'target_audienc',
                'campaign_1'
            ]
        ];
    }

    /**
     * Send an RCS (Rich Communication Services) message.
     * 
     * @param string $to Recipient phone number
     * @param string $templateCode The identifier for the RCS template
     * @param array $params Key-value pairs for the dynamic parameters in the template
     * @param string|null $botId Optional specific Bot ID (overrides default)
     * @param int $leadId The ID of the lead this message belongs to
     */
    public function sendRcsMessage($to, $templateCode, $params, $botId, $leadId = null, $campaignLeadId = null)
    {
        // Normalize the phone number to include international prefix (+91 for India) as per panel screenshot
        $to = str_replace(['+', ' '], '', $to);
        if (strlen($to) == 10) {
            $to = '91' . $to;
        }
        $to = '+' . $to;

        // Build the RCS specific API payload according to panel screenshot
        $payload = [
            'contentMessage' => [
                'templateMessage' => [
                    'templateCode' => $templateCode,
                ],
                'mobileno' => $to,
                'botId' => $botId ?: $this->botId,
                'messageId' => (string) Str::uuid()
            ]
        ];

        // Include customParams only if they exist
        if (!empty($params)) {
            $payload['contentMessage']['templateMessage']['customParams'] = json_encode($params);
        }

        try {
            // Send the POST request to the RCS endpoint
            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/REST/direct/sendRCS', $payload);

            $status = $response->successful() ? 'sent' : 'failed';
            $apiResponse = $response->json();

            // Log the message history in the database
            $this->logMessage([
                'lead_id' => $leadId,
                'campaign_lead_id' => $campaignLeadId,
                'type' => 'rcs',
                'to_number' => $to,
                'template_name' => $templateCode,
                'parameters' => $params,
                'status' => $status,
                'api_response' => $apiResponse,
            ]);

            return [
                'success' => $response->successful(),
                'data' => $apiResponse
            ];
        } catch (Exception $e) {
            Log::error('Alerts365 sendRCS Exception: ' . $e->getMessage());

            // Log the failure in the database
            $this->logMessage([
                'lead_id' => $leadId,
                'campaign_lead_id' => $campaignLeadId,
                'type' => 'rcs',
                'to_number' => $to,
                'template_name' => $templateCode,
                'parameters' => $params,
                'status' => 'failed',
                'api_response' => ['error' => $e->getMessage()],
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Internal helper to save message records into the database.
     */
    protected function logMessage($data)
    {
        return MessageLog::create($data);
    }
    /**
     * Fetch the list of SMS templates.
     */
    public function getSmsTemplates()
    {
        try {
            $response = Http::get($this->baseUrl . '/api/getSmsTemplates', [
                'key' => $this->apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Alerts365 SMS Templates Response: ' . json_encode($data));
                return $data;
            }

            Log::error('Alerts365 getSmsTemplates API Error: ' . $response->body());
            return ['error' => 'Failed to fetch SMS templates'];
        } catch (Exception $e) {
            Log::error('Alerts365 getSmsTemplates Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Send an SMS message.
     */
    public function sendSmsMessage($to, $smsContent, $senderId, $entityId, $tempId, $leadId = null, $unicode = 0, $campaignLeadId = null)
    {
        try {
            $params = [
                'key' => $this->apiKey,
                'mobiles' => $to,
                'sms' => $smsContent,
                'senderid' => $senderId,
                'entityid' => $entityId,
                'tempid' => $tempId,
                'unicode' => $unicode
            ];

            // Use query parameters as per API documentation
            $response = Http::post($this->baseUrl . '/api/sendsms?' . http_build_query($params));

            if ($response->successful()) {
                $data = $response->json();

                // Check if the API response actually indicates success in the payload
                $apiSuccess = isset($data['smslist']['sms']['status']) && $data['smslist']['sms']['status'] === 'success';

                // Log the message if a leadId is provided and it was successful
                if (($leadId || $campaignLeadId) && $apiSuccess) {
                    $this->logMessage([
                        'lead_id' => $leadId,
                        'campaign_lead_id' => $campaignLeadId,
                        'type' => 'sms',
                        'to_number' => $to,
                        'message_body' => $smsContent,
                        'status' => 'sent',
                        'api_response' => $data,
                    ]);
                }

                return [
                    'success' => $apiSuccess,
                    'data' => $data,
                    'error' => $apiSuccess ? null : ($data['smslist']['sms']['reason'] ?? 'API reported failure')
                ];
            }

            Log::error('Alerts365 sendSms API Error: ' . $response->body());
            return [
                'success' => false,
                'error' => 'Failed to send SMS.'
            ];
        } catch (Exception $e) {
            Log::error('Alerts365 sendSms Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
