<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OllamaClient
{
    private $baseUrl = '';
    private $model = '';
    private $timeout = 120;

    public function __construct(
        private readonly string $baseUrl = '',
        private readonly string $model   = '',
        private readonly int    $timeout = 120,
    ) {
        $this->baseUrl = $this->baseUrl ?: config('ollama.host');
        $this->model   = $this->model   ?: config('ollama.model');
        $this->timeout = $this->timeout ?: (int) config('ollama.timeout');
    }

    private function http()
    {
        return Http::baseUrl($this->baseUrl)
            ->connectTimeout(10)
            ->timeout($this->timeout)
            ->asJson();
    }

    /** Синхронная генерация (без стриминга) */
    public function generate(string $prompt, array $options = []): array
    {
        $payload = array_filter([
            'model'   => $options['model'] ?? $this->model,
            'prompt'  => $prompt,
            'system'  => $options['system'] ?? null,
            'images'  => $options['images'] ?? null,   // Base64 картинок (для мультимоделей)
            'options' => $options['params'] ?? null,   // temperature, top_p, num_ctx и т.д.
            'stream'  => false,
        ], fn($v) => $v !== null);

        $resp = $this->http()->post('/api/generate', $payload);
        $resp->throw();

        return $resp->json(); // {'response': '...', ...}
    }

    /** SSE-стрим в HTTP-ответ Laravel */
    public function streamGenerate(string $prompt, array $options = []): StreamedResponse
    {
        $payload = array_filter([
            'model'   => $options['model'] ?? $this->model,
            'prompt'  => $prompt,
            'system'  => $options['system'] ?? null,
            'images'  => $options['images'] ?? null,
            'options' => $options['params'] ?? null,
            'stream'  => true,
        ], fn($v) => $v !== null);

        return response()->stream(function () use ($payload) {
            try {
                $response = $this->http()
                    ->withOptions(['stream' => true])
                    ->post('/api/generate', $payload);

                $response->throw();

                foreach ($response->toPsrResponse()->getBody() as $chunk) {
                    if (!$chunk) continue;
                    // Ollama шлёт JSON-строчки; для SSE оборачиваем как data: ...
                    echo "data: " . trim($chunk) . "\n\n";
                    @ob_flush(); @flush();
                }

                echo "event: done\ndata: [DONE]\n\n";
                @ob_flush(); @flush();
            } catch (ConnectionException $e) {
                echo "event: error\ndata: " . json_encode(['error' => 'Connection error']) . "\n\n";
            } catch (\Throwable $e) {
                echo "event: error\ndata: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no', // для nginx — отключить буферизацию
        ]);
    }

    /** Chat-эндпоинт Ollama (формат сообщений) */
    public function chat(array $messages, array $options = []): array
    {
        $payload = array_filter([
            'model'    => $options['model'] ?? $this->model,
            'messages' => $messages, // [['role'=>'system|user|assistant','content'=>'...']]
            'options'  => $options['params'] ?? null,
            'stream'   => false,
        ], fn($v) => $v !== null);

        $resp = $this->http()->post('/api/chat', $payload);
        $resp->throw();

        return $resp->json(); // {'message': {'content': '...'}, ...}
    }
}
