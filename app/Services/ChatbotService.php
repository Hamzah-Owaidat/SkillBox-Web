<?php

namespace App\Services;

use App\Models\Service;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ChatbotService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout'  => 30,
        ]);
    }

    public function suggestServiceAndWorker(string $userMessage): array
    {
        // 1) Build context: services + workers
        $services = Service::getAll();
        $serviceData = [];

        foreach ($services as $svc) {
            $workers = Service::getWorkers($svc['id']);
            if (empty($workers)) {
                continue; // Skip services with no workers
            }
            
            $serviceData[] = [
                'id'      => $svc['id'],
                'title'   => $svc['title'],
                'desc'    => $svc['description'] ?? '',
                'workers' => array_map(function ($w) {
                    return [
                        'id'        => $w['id'],
                        'name'      => $w['full_name'],
                        'email'     => $w['email'] ?? '',
                        'phone'     => $w['phone'] ?? '',
                        'linkedin'  => $w['linkedin'] ?? '',
                    ];
                }, $workers),
            ];
        }

        if (empty($serviceData)) {
            return [
                'service_id' => null,
                'worker_id'  => null,
                'reason'     => 'No services with workers available',
            ];
        }

        // 2) Try AI first (Hugging Face - FREE, no API key needed for public models)
        try {
            $aiResult = $this->callHuggingFaceAI($userMessage, $serviceData);
            if ($aiResult !== null) {
                return $aiResult;
            }
        } catch (\Exception $e) {
            error_log('[Chatbot] AI call failed: ' . $e->getMessage());
        }

        // 3) Fallback to keyword matching if AI fails
        return $this->keywordMatching($userMessage, $serviceData);
    }

    /**
     * Call Hugging Face Inference API (FREE - no API key required for public models)
     */
    private function callHuggingFaceAI(string $userMessage, array $serviceData): ?array
    {
        try {
            // Build prompt
            $prompt = $this->buildPrompt($userMessage, $serviceData);

            // Use Hugging Face Inference API (free, public model)
            // Model: mistralai/Mistral-7B-Instruct-v0.2 (free, no auth needed)
            $response = $this->http->post('https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.2', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_new_tokens' => 200,
                        'temperature' => 0.7,
                        'return_full_text' => false,
                    ],
                ],
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            if (!isset($data[0]['generated_text'])) {
                return null;
            }

            $generatedText = $data[0]['generated_text'];
            
            // Try to extract JSON from response
            $jsonMatch = [];
            if (preg_match('/\{[^}]+\}/', $generatedText, $jsonMatch)) {
                $parsed = json_decode($jsonMatch[0], true);
                if ($parsed && isset($parsed['service_id'])) {
                    return [
                        'service_id' => $parsed['service_id'] ?? null,
                        'worker_id'  => $parsed['worker_id'] ?? null,
                        'reason'     => $parsed['reason'] ?? 'AI recommendation',
                    ];
                }
            }

            return null;
        } catch (RequestException $e) {
            // Hugging Face might be slow or rate-limited, fall back to keyword matching
            error_log('[Chatbot] Hugging Face error: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log('[Chatbot] AI error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build prompt for AI
     */
    private function buildPrompt(string $userMessage, array $serviceData): string
    {
        $servicesList = '';
        foreach ($serviceData as $svc) {
            $workersList = implode(', ', array_column($svc['workers'], 'name'));
            $servicesList .= sprintf(
                "Service ID %d: %s - %s (Workers: %s)\n",
                $svc['id'],
                $svc['title'],
                $svc['desc'],
                $workersList ?: 'None'
            );
        }

        return <<<PROMPT
You are a service recommendation assistant. Analyze the user's request and recommend the best service and worker.

Available Services:
{$servicesList}

User Request: {$userMessage}

Respond ONLY with a JSON object in this exact format:
{
  "service_id": <number or null>,
  "worker_id": <number or null>,
  "reason": "<brief explanation>"
}

Rules:
- If the request matches a service, return the service_id and worker_id (pick the first available worker)
- If no service matches, return null for both IDs
- Only use IDs that exist in the list above

JSON Response:
PROMPT;
    }

    /**
     * Fallback: Simple keyword matching
     */
    private function keywordMatching(string $userMessage, array $serviceData): array
    {
        $userLower = strtolower($userMessage);
        
        // Build keyword map
        $matches = [];
        foreach ($serviceData as $svc) {
            $score = 0;
            $title = strtolower($svc['title']);
            $desc = strtolower($svc['desc'] ?? '');
            
            // Check title keywords
            if (strpos($userLower, $title) !== false) {
                $score += 10;
            }
            
            // Check description keywords
            $descWords = explode(' ', $desc);
            foreach ($descWords as $word) {
                if (strlen($word) > 3 && strpos($userLower, $word) !== false) {
                    $score += 2;
                }
            }
            
            // Common keyword matching
            $keywords = [
                'design' => ['design', 'graphic', 'logo', 'poster', 'banner', 'image', 'visual'],
                'content' => ['content', 'writing', 'copy', 'text', 'article', 'blog', 'caption'],
                'marketing' => ['marketing', 'ad', 'advertisement', 'campaign', 'promote', 'social media'],
                'web' => ['website', 'web', 'site', 'page', 'online'],
                'video' => ['video', 'edit', 'production', 'youtube'],
            ];
            
            foreach ($keywords as $key => $terms) {
                foreach ($terms as $term) {
                    if (strpos($userLower, $term) !== false && 
                        (strpos($title, $key) !== false || strpos($desc, $key) !== false)) {
                        $score += 5;
                    }
                }
            }
            
            if ($score > 0) {
                $matches[] = [
                    'service' => $svc,
                    'score' => $score,
                ];
            }
        }
        
        // Sort by score
        usort($matches, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        // Return best match if score is high enough
        if (!empty($matches) && $matches[0]['score'] >= 5) {
            $bestService = $matches[0]['service'];
            $workers = $bestService['workers'];
            
            if (!empty($workers)) {
                return [
                    'service_id' => $bestService['id'],
                    'worker_id'  => $workers[0]['id'],
                    'reason'     => 'Matched based on keywords: ' . $bestService['title'],
                ];
            }
        }
        
        return [
            'service_id' => null,
            'worker_id'  => null,
            'reason'     => 'No matching service found. Please try rephrasing your request.',
        ];
    }
}