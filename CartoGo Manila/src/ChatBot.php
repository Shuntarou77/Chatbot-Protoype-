<?php
namespace CartoGo;

class ChatBot {
    private ?\MongoDB\Database $db;
    private IntentHandler $intentHandler;
    private GeminiService $geminiService;
    private bool $dbConnected = false;

    public function __construct() {
        $this->intentHandler = new IntentHandler();

        $config = [];
        $configFile = __DIR__ . '/../config/config.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
        }

        $geminiKey   = $config['gemini']['api_key'] ?? '';
        $geminiModel = $config['gemini']['model'] ?? 'gemini-flash-lite-latest';
        $this->geminiService = new GeminiService($geminiKey, $geminiModel);

        try {
            $this->db          = Database::connect();
            $this->dbConnected = true;
        } catch (\Throwable $e) {
            $this->db          = null;
            $this->dbConnected = false;
            error_log('[CartoGoBot] MongoDB unavailable: ' . $e->getMessage());
        }
    }

    public function processMessage(string $sessionId, string $userMessage): array {
        $replyText = '';
        $source    = 'rules';
        $intent    = 'unknown';

        if ($this->geminiService->isConfigured()) {
            try {
                $history   = $this->getFormattedHistory($sessionId, 6);
                $replyText = $this->geminiService->generateResponse($userMessage, $history);
                $source    = 'gemini';
                $intent    = 'ai_chat';
            } catch (\Throwable $e) {
                error_log('[CartoGoBot] Gemini error: ' . $e->getMessage());
                $replyText = "⚠️ **AI Error:** " . $e->getMessage();
                $source    = 'gemini_error';
                $intent    = 'error';
            }
        } else {
            $result    = $this->intentHandler->detectIntent($userMessage);
            $replyText = "⚠️ **AI not configured.** API key missing in config/config.php.\n\n" . $result['response'];
            $intent    = $result['intent'];
            $source    = 'rules_unconfigured';
        }

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

    private function getFormattedHistory(string $sessionId, int $limit = 6): array {
        if (!$this->dbConnected) return [];
        try {
            $cursor = $this->db->conversations->find(
                ['session_id' => $sessionId],
                ['sort' => ['timestamp' => -1], 'limit' => $limit]
            );
            $raw       = array_reverse(iterator_to_array($cursor));
            $formatted = [];
            foreach ($raw as $turn) {
                if (!empty($turn['user_message'])) {
                    $formatted[] = ['role' => 'user',  'text' => (string)$turn['user_message']];
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

    private function saveMessage(string $sessionId, string $userMsg, string $botMsg, string $intent): void {
        try {
            $this->db->conversations->insertOne([
                'session_id'   => $sessionId,
                'user_message' => $userMsg,
                'bot_response' => $botMsg,
                'intent'       => $intent,
                'timestamp'    => new \MongoDB\BSON\UTCDateTime(),
            ]);
        } catch (\Throwable $e) {
            error_log('[CartoGoBot] Save failed: ' . $e->getMessage());
        }
    }

    public function getHistory(string $sessionId): array {
        if (!$this->dbConnected) return [];
        $cursor = $this->db->conversations->find(
            ['session_id' => $sessionId],
            ['sort' => ['timestamp' => 1]]
        );
        return iterator_to_array($cursor);
    }
}
