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
            // Get the user's Infobip configuration
            $config = DeliveryConfig::where('user_id', $userId)
                ->where('config_type', 'sms')
                ->where('provider', 'infobip')
                ->where('is_active', true)
                ->first();
                
            if (!$config) {
                return [
                    'success' => false,
                    'message' => 'No active Infobip configuration found for this user.'
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
            $configuration = new Configuration(
                host: $settings['base_url'],
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
            
            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'response' => $messageInfo
            ];
            
        } catch (Exception $e) {
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
                'message' => 'No active Infobip configuration found for this user.'
            ];
        }
        
        $settings = $config->settings;
        
        // Use the provided phone number or a default one
        $phoneNumber = $to ?? $settings['from'];
        
        // Create a test message
        $message = "This is a test SMS from SIJIL system. Your Infobip configuration is working correctly.";
        
        // Send the message
        return $this->sendSms($phoneNumber, $message, $userId);
    }
} 