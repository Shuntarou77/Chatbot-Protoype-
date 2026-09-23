<?php
// Test script for Google AI Studio (Gemini)
chdir(__DIR__);
require_once __DIR__ . '/vendor/autoload.php';

echo "=== Google AI Studio (Gemini) Connection Test ===\n\n";

$config = require __DIR__ . '/config/config.php';
$apiKey = $config['gemini']['api_key'] ?? '';
$model  = $config['gemini']['model'] ?? 'gemini-1.5-flash';

echo "Model configured: $model\n";

if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
    echo "\n⚠️  API Key is NOT set yet!\n";
    echo "Please open `config/config.php` and set your API key.\n";
    exit(1);
}

$masked = substr($apiKey, 0, 6) . '...' . substr($apiKey, -4);
echo "API Key found: $masked\n\n";
echo "Sending test prompt to Gemini: \"Hello! Briefly introduce yourself in one sentence as InsureBot.\"\n\n";

$gemini = new \App\GeminiService($apiKey, $model);

try {
    $start = microtime(true);
    $response = $gemini->generateResponse("Hello! Briefly introduce yourself in one sentence as InsureBot.");
    $time = round(microtime(true) - $start, 2);

    echo "✅ Success! Received response in {$time}s:\n";
    echo "--------------------------------------------------\n";
    echo $response . "\n";
    echo "--------------------------------------------------\n\n";
    echo "🎉 Google AI Studio is fully working with your chatbot!\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
