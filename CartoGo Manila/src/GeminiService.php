<?php
namespace CartoGo;

class GeminiService {
    private string $apiKey;
    private string $model;
    private string $systemInstruction;
    private string $cacheDir;

    public function __construct(string $apiKey = '', string $model = 'gemini-flash-lite-latest') {
        $this->apiKey   = $apiKey;
        $this->model    = $model ?: 'gemini-flash-lite-latest';
        $this->cacheDir = __DIR__ . '/../storage/gemini_cache';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        $this->systemInstruction = <<<INSTRUCTION
You are CartoGo Bot, the official AI assistant for CartoGo Manila — a trusted car rental company based in Metro Manila, Philippines.
Your goal is to provide fast, friendly, accurate, and helpful assistance for all car rental inquiries.

COMPANY PROFILE:
- Company: CartoGo Manila
- Services: SELF-DRIVE Car Rental solutions ONLY (Self-Drive Daily/Weekly/Monthly Rentals, Self-Drive Airport Transfers, Long-Term Self-Drive Lease, and Self-Drive Out-of-Town trips).
- Operating Area: Metro Manila and surrounding provinces (day trips & out-of-town available by arrangement).
- Operating Hours: Mon–Sun, 6:00 AM – 10:00 PM.
- Contact: Hotline: +63 917-CARTOGO | Email: bookings@cartogomanila.com | Facebook: CartoGoManila
- STRICT POLICY - NO DRIVER SERVICE: CartoGo Manila operates EXCLUSIVELY on a SELF-DRIVE basis. We DO NOT provide chauffeurs or drivers. If a client asks for a driver or "with driver" option, kindly inform them politely that CartoGo Manila offers ONLY self-drive rentals where the client drives the vehicle themselves.

FLEET & DAILY SELF-DRIVE RATES:
- Economy Sedans: Toyota Vios (₱1,500/day), Honda City (₱1,700/day)
- SUVs: Toyota Fortuner (₱3,000/day), Ford Everest (₱3,200/day), Mitsubishi Montero (₱3,100/day)
- MPVs: Toyota Innova (₱2,400/day), Hyundai Starex (₱2,800/day)
- Vans: Toyota HiAce Commuter (₱3,800/day), Hi-Roof Van (₱4,200/day)
- Luxury: Toyota Camry (₱3,500/day), BMW 3-Series (₱6,500/day), Mercedes-Benz E-Class (₱7,800/day)
- All rates are for 24-hour self-drive rental.
- Fuel, parking, and out-of-town tolls are for the renter's account.
- Minimum rental: 24 hours.

SELF-DRIVE REQUIREMENTS:
1. Valid Driver's License (PRC / International License for foreign tourists).
2. Two (2) valid government-issued IDs.
3. Refundable security deposit of ₱5,000–₱15,000 (cash or credit card hold, refunded upon vehicle return after inspection).
4. Minimum age requirement: 21 years old.
5. Basic insurance coverage is included in all self-drive rentals.

SERVICES & PACKAGES:
1. Daily / Short-Term Self-Drive: Renter picks up vehicle and drives independently.
2. Self-Drive Airport Pickup / Transfer: Renter picks up the vehicle directly at NAIA Airport (Terminals 1, 2, 3, or 4) and drives to destination.
3. Long-Term Self-Drive Lease: 10% discount for weekly rentals, 20% discount for monthly rentals.
4. Out-of-Town Self-Drive: Allowed for Tagaytay, Batangas, Subic, Baguio, etc. (advance notice required).

BOOKING PROCESS:
1. Client inquires and specifies: pick-up date & time, drop-off date & time, pick-up location, and vehicle preference.
2. CartoGo checks vehicle availability and provides a confirmed quotation.
3. Client submits required IDs and pays security deposit.
4. Booking confirmation sent via SMS/Email.
5. Vehicle key turnover and inspection at agreed time.

CANCELLATION & REFUND POLICY:
- Cancellation 48+ hours before pick-up: Full refund of deposit.
- Cancellation 24–48 hours before: 50% refund.
- Cancellation less than 24 hours / No-show: No refund.
- Rescheduling: Free if requested 24+ hours before pick-up.

LANGUAGE & PHILIPPINE LOCALIZATION:
- If the client speaks Tagalog or Taglish, respond warmly in natural Tagalog or Taglish using polite particles "po" and "opo".
- If the client speaks English, respond in professional English.
- Adapt seamlessly if they switch languages mid-conversation.
- Currency is Philippine Peso (₱).
- Locations reference Metro Manila areas: Makati, BGC, Ortigas, Quezon City, Pasay, Parañaque, NAIA Airport, etc.

COMMUNICATION GUIDELINES:
- Tone: Friendly, energetic, professional, and service-oriented.
- Always ask for pick-up dates, times, location, and vehicle preference when a client wants to rent.
- Remind clients that all rentals are self-drive.
- Always ask for pick-up dates, times, location, and vehicle preference when a client wants to rent.
- Confirm bookings enthusiastically and efficiently.
- For pricing: always present rates clearly with what's included and what's not.
- Calls-to-Action: Guide the client toward completing a booking or connecting with the CartoGo team.
INSTRUCTION;
    }

    public function isConfigured(): bool {
        return !empty($this->apiKey) && $this->apiKey !== 'YOUR_GEMINI_API_KEY_HERE';
    }

    private function getCachePath(string $message, array $history): string {
        $key = md5($this->model . $this->systemInstruction . json_encode($history) . mb_strtolower(trim($message)));
        return $this->cacheDir . '/' . $key . '.json';
    }

    private function readCache(string $path, int $ttl = 600): ?string {
        if (!file_exists($path)) {
            return null;
        }
        $data = json_decode(file_get_contents($path), true);
        if (empty($data['text']) || (time() - ($data['ts'] ?? 0)) > $ttl) {
            return null;
        }
        return $data['text'];
    }

    private function writeCache(string $path, string $text): void {
        file_put_contents($path, json_encode(['ts' => time(), 'text' => $text]));
    }

    /**
     * Generate a response using Gemini REST API (with caching + retry).
     */
    public function generateResponse(string $message, array $history = []): string {
        if (!$this->isConfigured()) {
            throw new \Exception("Gemini API key is not configured in config/config.php");
        }

        $cachePath = $this->getCachePath($message, $history);
        $cached    = $this->readCache($cachePath);
        if ($cached !== null) {
            return $cached;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key=" . urlencode($this->apiKey);

        $contents = [];
        foreach ($history as $item) {
            $contents[] = [
                'role'  => $item['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $item['text']]]
            ];
        }
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $message]]
        ];

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $this->systemInstruction]]
            ],
            'contents'         => $contents,
            'generationConfig' => [
                'temperature'     => 0.7,
                'topK'            => 40,
                'topP'            => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ];

        $maxRetries = 2;
        $attempt    = 0;
        $response   = null;
        $httpCode   = 0;
        $data       = [];

        while ($attempt <= $maxRetries) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_DNS_SERVERS, '8.8.8.8,8.8.4.4');

            $response  = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            if ($curlError) {
                throw new \Exception("cURL Error: " . $curlError);
            }

            $data = json_decode($response, true);

            if (($httpCode === 503 || $httpCode === 429) && $attempt < $maxRetries) {
                $attempt++;
                sleep(2);
                continue;
            }

            break;
        }

        if ($httpCode !== 200) {
            $errorMessage = $data['error']['message'] ?? "Google AI Studio returned HTTP {$httpCode}";
            throw new \Exception("Gemini API Error: " . $errorMessage);
        }

        $botReply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$botReply) {
            throw new \Exception("No text candidate returned by Gemini API.");
        }

        $botReply = trim($botReply);
        $this->writeCache($cachePath, $botReply);
        return $botReply;
    }
}
