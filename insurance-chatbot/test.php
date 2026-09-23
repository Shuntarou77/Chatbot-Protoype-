<?php
// Quick test script - run from project root
chdir(__DIR__);
require_once __DIR__ . '/vendor/autoload.php';

echo "1. Autoload OK\n";

try {
    $config = require __DIR__ . '/config/config.php';
    echo "2. Config OK: " . $config['mongodb']['uri'] . "\n";
} catch (Throwable $e) {
    echo "2. Config ERROR: " . $e->getMessage() . "\n";
}

try {
    $db = \App\Database::connect();
    echo "3. MongoDB connection OK\n";
    // Try a ping
    $db->command(['ping' => 1]);
    echo "4. MongoDB ping OK\n";
} catch (Throwable $e) {
    echo "3. MongoDB ERROR: " . $e->getMessage() . "\n";
}

try {
    $handler = new \App\IntentHandler();
    $result  = $handler->detectIntent('hello');
    echo "5. IntentHandler OK, intent: " . $result['intent'] . "\n";
} catch (Throwable $e) {
    echo "5. IntentHandler ERROR: " . $e->getMessage() . "\n";
}

try {
    $bot  = new \App\ChatBot();
    $resp = $bot->processMessage('test_session', 'hello');
    echo "6. ChatBot OK, response snippet: " . substr($resp['response'], 0, 60) . "...\n";
} catch (Throwable $e) {
    echo "6. ChatBot ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}
