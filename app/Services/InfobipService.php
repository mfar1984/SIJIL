<?php

namespace App\Services;

use App\Models\DeliveryConfig;
use Infobip\Api\MessagesApi;
use Infobip\Configuration;
use Infobip\Model\MessagesApiMessage;
use Infobip\Model\MessagesApiRequest;
use Infobip\Model\MessagesApiToDestination;
use Infobip\Model\MessagesApiOutboundMessageChannel;
use Infobip\Model\MessagesApiMessageContent;
use Infobip\Model\MessagesApiMessageTextBody;
use Exception;

/**
 * Infobip SMS Service
 * 
 * This service handles SMS sending via Infobip API.
 * Each organizer configures their own Infobip credentials and uses this service
 * to send SMS notifications to their event participants.
 */
class InfobipService
{
    /**
     * Normalize phone number to international format
     * Handles Malaysian phone numbers with various formats:
     * - 0178591411 -> 60178591411
     * - +60178591411 -> 60178591411
     * - 60178591411 -> 60178591411
     * - 178591411 -> 60178591411
     *
     * @param string $phone Phone number
     * @return string Normalized phone number
     */
    private function normalizePhoneNumber($phone)
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Remove leading + if present
        $phone = ltrim($phone, '+');
        
        // If starts with 0, replace with 60 (Malaysia country code)
        if (substr($phone, 0, 1) === '0') {
            $phone = '60' . substr($phone, 1);
        }
        
        // If doesn't start with 60 and length is 9-10 digits (local format), add 60
        if (substr($phone, 0, 2) !== '60' && strlen($phone) >= 9 && strlen($phone) <= 10) {
            $phone = '60' . $phone;
        }
        
        return $phone;
    }

    /**
     * Send SMS using Infobip API
     *
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @param int $userId User ID for configuration
     * @return array Response with success status and message
     */
    public function sendSms($to, $message, $userId)
    {
        try {
            // Normalize phone number to international format
            $originalPhone = $to;
            $to = $this->normalizePhoneNumber($to);
            
            \Log::info('InfobipService: Normalizing phone number', [
                'original' => $originalPhone,
                'normalized' => $to,
                'user_id' => $userId,
            ]);
            
            // This account's own configuration only. SMS never borrows another
            // account's gateway. See App\Support\DeliveryAccount::smsConfig().
            [$config] = \App\Support\DeliveryAccount::smsConfig($userId);

            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'SMS delivery is switched off for this account. Enable it under '
                        . 'Configuration > Delivery and save an Infobip gateway.'
                ];
            }
            
            $settings = $config->settings;
            
            if (!isset($settings['key']) || !isset($settings['base_url']) || !isset($settings['from'])) {
                return [
                    'success' => false,
                    'message' => 'Incomplete Infobip configuration. Please check your settings.'
                ];
            }
            
            // Initialize Infobip client
            // Ensure base_url has https:// prefix
            $baseUrl = $settings['base_url'];
            if (!preg_match('#^https?://#', $baseUrl)) {
                $baseUrl = 'https://' . $baseUrl;
            }
            
            \Log::info('InfobipService: Initializing client', [
                'original_base_url' => $settings['base_url'],
                'normalized_base_url' => $baseUrl,
                'from' => $settings['from'],
            ]);
            
            $configuration = new Configuration(
                host: $baseUrl,
                apiKey: $settings['key']
            );
            
            $messagesApi = new MessagesApi(config: $configuration);
            
            // Create message request
            $request = new MessagesApiRequest(
                messages: [
                    new MessagesApiMessage(
                        channel: MessagesApiOutboundMessageChannel::SMS(),
                        sender: $settings['from'],
                        destinations: [new MessagesApiToDestination($to)],
                        content: new MessagesApiMessageContent(
                            body: new MessagesApiMessageTextBody($message)
                        )
                    )
                ]
            );
            
            // Send message
            $messageInfo = $messagesApi->sendMessagesApiMessage($request);
            
            // Extract response details - MessageResponseDetails has different methods
            $messages = $messageInfo->getMessages();
            $responseData = [];
            
            if ($messages && count($messages) > 0) {
                foreach ($messages as $msg) {
                    $responseData[] = [
                        'messageId' => $msg->getMessageId(),
                        'status' => [
                            'groupId' => $msg->getStatus()?->getGroupId(),
                            'groupName' => $msg->getStatus()?->getGroupName(),
                            'id' => $msg->getStatus()?->getId(),
                            'name' => $msg->getStatus()?->getName(),
                            'description' => $msg->getStatus()?->getDescription(),
                        ],
                    ];
                }
            }
            
            \Log::info('InfobipService: SMS sent successfully', [
                'to' => $to,
                'response_messages' => $responseData,
                'bulk_id' => $messageInfo->getBulkId(),
            ]);
            
            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'response' => $messageInfo,
                'details' => $responseData,
            ];
            
        } catch (Exception $e) {
            \Log::error('InfobipService: Failed to send SMS', [
                'to' => $to ?? 'unknown',
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Replace template variables in a message
     *
     * @param string $template Message template
     * @param array $data Data for variable replacement
     * @return string Processed message
     */
    public function processTemplate($template, $data)
    {
        $message = $template;
        
        foreach ($data as $key => $value) {
            $message = str_replace('{'.$key.'}', $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Send a test SMS
     *
     * @param int $userId User ID for configuration
     * @param string $to Recipient phone number (optional)
     * @return array Response with success status and message
     */
    public function sendTestSms($userId, $to = null)
    {
        // Get the user's Infobip configuration
        $config = DeliveryConfig::where('user_id', $userId)
            ->where('config_type', 'sms')
            ->where('provider', 'infobip')
            ->where('is_active', true)
            ->first();
            
        if (!$config) {
            return [
                'success' => false,
                'message' => 'SMS delivery is switched off for this account, or the saved gateway '
                    . 'is not Infobip. Only Infobip can send.'
            ];
        }
        
        $settings = $config->settings;
        
        // Use the provided phone number or a default one
        $phoneNumber = $to ?? $settings['from'];
        
        // Normalize phone number
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);
        
        // Create a test message
        $message = "This is a test SMS from SIJIL system. Your Infobip configuration is working correctly.";
        
        // Send the message
        return $this->sendSms($phoneNumber, $message, $userId);
    }
} 