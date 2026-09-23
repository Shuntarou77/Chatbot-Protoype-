<?php
/**
 * Command line test script for CartoGo Manila Chatbot
 * Usage: php test_cartogo.php "your message here"
 */

require_once __DIR__ . '/bootstrap.php';

use CartoGo\ChatBot;

$message = $argv[1] ?? "Magkano magpa-rent ng Vios for 3 days self drive?";

echo "=========================================\n";
echo " CartoGo Manila Chatbot - CLI Test\n";
echo "=========================================\n";
echo "User Input: " . $message . "\n";
echo "-----------------------------------------\n";

$bot = new ChatBot();
$response = $bot->processMessage('cli_test_session', $message);

echo "Bot Output:\n";
echo $response['response'] . "\n";
echo "-----------------------------------------\n";
echo "Source: " . ($response['source'] ?? 'unknown') . "\n";
echo "Cache Hit: " . (($response['cached'] ?? false) ? 'YES' : 'NO') . "\n";
echo "=========================================\n";
