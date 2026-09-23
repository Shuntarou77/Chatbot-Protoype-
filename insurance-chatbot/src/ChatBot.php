<?php
namespace App;

class ChatBot {
    private ?\MongoDB\Database $db;
    private IntentHandler $intentHandler;
    private GeminiService $geminiService;
    private bool $dbConnected = false;

    public function __construct() {
        $this->intentHandler = new IntentHandler();

        // Load config for Gemini
        $config = [];
        $configFile = __DIR__ . '/../config/config.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
        }

        $geminiKey   = $config['gemini']['api_key'] ?? '';
        $geminiModel = $config['gemini']['model'] ?? 'gemini-1.5-flash';
        $this->geminiService = new GeminiService($geminiKey, $geminiModel);

        // Try to connect to MongoDB — if it fails, chatbot still works
        try {
            $this->db          = Database::connect();
            $this->dbConnected = true;
        } catch (\Throwable $e) {
            $this->db          = null;
            $this->dbConnected = false;
            error_log('[ChatBot] MongoDB unavailable: ' . $e->getMessage());
        }
    }

    /**
     * Process a user message and return a bot response.
     */
    public function processMessage(string $sessionId, string $userMessage): array {
        $replyText = '';
        $source    = 'rules';
        $intent    = 'unknown';

        // 1. If Gemini AI is configured, call it
        if ($this->geminiService->isConfigured()) {
            try {
                $history   = $this->getFormattedHistory($sessionId, 6);
                $replyText = $this->geminiService->generateResponse($userMessage, $history);
                $source    = 'gemini';
                $intent    = 'ai_chat';
            } catch (\Throwable $e) {
                error_log('[ChatBot] Gemini error: ' . $e->getMessage());
                // Tell the user the exact Gemini error so it's easy to fix
                $replyText = "⚠️ **Gemini API Error:** " . $e->getMessage() . "\n\nPlease check your API key in `config/config.php` or model quota.";
                $source    = 'gemini_error';
                $intent    = 'error';
            }
        } else {
            // Gemini is not configured yet
            $result    = $this->intentHandler->detectIntent($userMessage);
            $replyText = "⚠️ **Gemini AI is not active yet:** API key is missing in `config/config.php`.\n\n" . $result['response'];
            $intent    = $result['intent'];
            $source    = 'rules_unconfigured';
        }

        // 3. Save to MongoDB if connected
        if ($this->dbConnected) {
            $this->saveMessage($sessionId, $userMessage, $replyText, $intent);
        }

        return [
            'response'  => $replyText,
            'intent'    => $intent,
            'source'    => $source,
            'session'   => $sessionId,
            'timestamp' => date('c'),
            'db_saved'  => $this->dbConnected,
        ];
    }

    /**
     * Format past messages for Gemini multi-turn conversation.
     */
    private function getFormattedHistory(string $sessionId, int $limit = 6): array {
        if (!$this->dbConnected) {
            return [];
        }

        try {
            $cursor = $this->db->conversations->find(
                ['session_id' => $sessionId],
                [
                    'sort'  => ['timestamp' => -1],
                    'limit' => $limit
                ]
            );

            $raw = array_reverse(iterator_to_array($cursor));
            $formatted = [];

            foreach ($raw as $turn) {
                if (!empty($turn['user_message'])) {
                    $formatted[] = ['role' => 'user', 'text' => (string)$turn['user_message']];
                }
                if (!empty($turn['bot_response'])) {
                    $formatted[] = ['role' => 'model', 'text' => (string)$turn['bot_response']];
                }
            }

            return $formatted;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Save a conversation turn to MongoDB.
     */
    private function saveMessage(
        string $sessionId,
        string $userMsg,
        string $botMsg,
        string $intent
    ): void {
        try {
            $this->db->conversations->insertOne([
                'session_id'   => $sessionId,
                'user_message' => $userMsg,
                'bot_response' => $botMsg,
                'intent'       => $intent,
                'timestamp'    => new \MongoDB\BSON\UTCDateTime(),
            ]);
        } catch (\Throwable $e) {
            error_log('[ChatBot] Failed to save conversation: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve conversation history for a session.
     */
    public function getHistory(string $sessionId): array {
        if (!$this->dbConnected) {
            return [];
        }

        $cursor = $this->db->conversations->find(
            ['session_id' => $sessionId],
            ['sort' => ['timestamp' => 1]]
        );
        return iterator_to_array($cursor);
    }
}
