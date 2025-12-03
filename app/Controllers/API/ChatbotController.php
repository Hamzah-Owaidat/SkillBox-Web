<?php

namespace App\Controllers\Api;

use App\Models\Service;
use App\Models\User;
use App\Services\ChatbotService;

class ChatbotController
{
    public function query()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if ($message === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'message is required']);
            return;
        }

        try {
            $bot = new ChatbotService();
            $result = $bot->suggestServiceAndWorker($message);

            if (!$result['service_id'] || !$result['worker_id']) {
                $reply = "Sorry, we can't help with this request right now. Please try again later or contact us directly.";
                if (!empty($result['reason'])) {
                    $reply = $result['reason'];
                }
                
                echo json_encode([
                    'success' => true,
                    'reply'   => $reply,
                    'service' => null,
                    'worker'  => null,
                ]);
                return;
            }

            $service = Service::find($result['service_id']);
            $worker  = User::find($result['worker_id']);

            if (!$service || !$worker) {
                echo json_encode([
                    'success' => true,
                    'reply'   => "Sorry, we can't help with this request right now. Please try again later.",
                    'service' => null,
                    'worker'  => null,
                ]);
                return;
            }

            // Build a nice reply message
            $baseUrl = '/skillbox/public';
            $serviceLink = "<a href='{$baseUrl}/services/{$service['id']}' target='_blank'>{$service['title']}</a>";
            
            $reply = "✅ Great! Based on your request, I recommend:\n\n";
            $reply .= "📋 Service: {$service['title']}\n";
            if (!empty($service['description'])) {
                $reply .= "📝 Description: {$service['description']}\n";
            }
            $reply .= "\n👤 Best Worker: {$worker['full_name']}\n";
            $reply .= "📧 Email: {$worker['email']}\n";
            if (!empty($worker['phone'])) {
                $reply .= "📞 Phone: {$worker['phone']}\n";
            }
            if (!empty($worker['linkedin'])) {
                $reply .= "🔗 LinkedIn: {$worker['linkedin']}\n";
            }
            $reply .= "\n💡 " . ($result['reason'] ?? 'This worker is available and matches your needs.');

            echo json_encode([
                'success' => true,
                'reply'   => $reply,
                'service' => [
                    'id' => $service['id'],
                    'title' => $service['title'],
                    'description' => $service['description'] ?? '',
                ],
                'worker'  => [
                    'id' => $worker['id'],
                    'full_name' => $worker['full_name'],
                    'email' => $worker['email'],
                    'phone' => $worker['phone'] ?? null,
                    'linkedin' => $worker['linkedin'] ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            error_log('[Chatbot] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }
}

