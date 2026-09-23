<?php
namespace CartoGo;

/**
 * Rule-based fallback intent handler for CartoGo Manila.
 * Used when Gemini AI is unavailable or not yet configured.
 */
class IntentHandler {

    private array $intents = [
        'greeting' => [
            'patterns' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'start', 'kumusta', 'magandang'],
            'response' => "👋 Hello! Welcome to **CartoGo Manila** — your trusted self-drive car rental partner in Metro Manila!\n\nHow can I help you today?\n\n• 🚗 **Rent a car** — Browse our self-drive fleet\n• 📅 **Check availability** — Pick-up & drop-off dates\n• 💰 **Get a quote** — Self-drive daily rates\n• 📋 **Booking requirements** — License & deposit details\n• 📞 **Contact us** — Talk to our team",
        ],
        'availability' => [
            'patterns' => ['available', 'availability', 'book', 'reserve', 'rental', 'rent', 'date', 'schedule'],
            'response' => "📅 **Check Self-Drive Vehicle Availability**\n\nTo check if a vehicle is available on your preferred dates, please provide:\n\n1. **Pick-up date & time**\n2. **Drop-off date & time**\n3. **Pick-up location** (Metro Manila / NAIA Airport)\n4. **Vehicle type preference** — Sedan, SUV, MPV, Van, or Luxury\n\nOur team will confirm vehicle availability for you right away!",
        ],
        'fleet' => [
            'patterns' => ['car', 'vehicle', 'fleet', 'sedan', 'suv', 'mpv', 'van', 'fortuner', 'innova', 'vios', 'civic'],
            'response' => "🚗 **CartoGo Manila Self-Drive Fleet:**\n\n**Sedans (Economy)**\n• Toyota Vios — ₱1,500/day\n• Honda City — ₱1,700/day\n\n**SUVs**\n• Toyota Fortuner — ₱3,000/day\n• Ford Everest — ₱3,200/day\n\n**MPVs / Vans**\n• Toyota Innova — ₱2,400/day\n• Toyota HiAce — ₱3,800/day\n\n**Luxury**\n• Toyota Camry — ₱3,500/day\n• BMW 3-Series — ₱6,500/day\n\n📌 *All rates are for 24-hour Self-Drive rental. Fuel and tolls are for the renter's account.*",
        ],
        'price' => [
            'patterns' => ['price', 'rate', 'cost', 'how much', 'magkano', 'bayad', 'fee', 'charge', 'quote'],
            'response' => "💰 **CartoGo Manila Self-Drive Rates:**\n\n• Economy Sedan — from **₱1,500/day**\n• SUV — from **₱3,000/day**\n• MPV / Van — from **₱2,400/day**\n• Luxury — from **₱3,500/day**\n\n📌 **Inclusions:** 24-hour self-drive use, basic vehicle insurance\n📌 **Exclusions:** Fuel, parking, toll fees\n📌 **Discounts:** 10% off for weekly bookings, 20% off for monthly lease\n\n*Note: CartoGo Manila operates exclusively on a Self-Drive basis (no drivers provided).*",
        ],
        'requirements' => [
            'patterns' => ['requirement', 'need', 'document', 'id', 'license', 'deposit', 'kailangan', 'bring', 'prepare', 'driver'],
            'response' => "📋 **Self-Drive Booking Requirements:**\n\n• Valid Driver's License (Philippine PRC or Foreign/International License)\n• 2 valid government-issued IDs\n• Credit card or cash security deposit (₱5,000–₱15,000 depending on vehicle)\n• Renter must be at least **21 years old**\n\n⚠️ **Important Note:** We offer **Self-Drive rentals ONLY**. We do not provide drivers/chauffeurs.",
        ],
        'airport' => [
            'patterns' => ['airport', 'transfer', 'naia', 'terminal', 'pick up', 'pickup', 'drop off', 'arrival', 'flight'],
            'response' => "✈️ **Self-Drive Airport Pickup (NAIA)**\n\nPick up your reserved rental car directly at NAIA Airport (Terminals 1, 2, 3, or 4) and drive yourself to your destination!\n\n**How to reserve:**\n1. Provide flight number and arrival time\n2. Select your vehicle preference\n3. Submit Driver's License & IDs in advance\n\nOur team will turn over the key and vehicle right at the airport terminal.",
        ],
        'contact' => [
            'patterns' => ['contact', 'call', 'phone', 'email', 'agent', 'human', 'support', 'hotline'],
            'response' => "📞 **Contact CartoGo Manila:**\n\n• **Hotline:** +63 917-CARTOGO\n• **Email:** bookings@cartogomanila.com\n• **Facebook:** facebook.com/CartoGoManila\n• **Office Hours:** Mon–Sun, 6:00 AM – 10:00 PM\n\nReach out to us anytime for self-drive reservations!",
        ],
        'farewell' => [
            'patterns' => ['bye', 'goodbye', 'thanks', 'thank you', 'salamat', 'exit', 'done'],
            'response' => "👋 Thank you for choosing **CartoGo Manila**!\n\n🚗 *Drive safe and enjoy your journey!*",
        ],
    ];

    public function detectIntent(string $message): array {
        $message = strtolower(trim($message));

        foreach ($this->intents as $intent => $data) {
            foreach ($data['patterns'] as $pattern) {
                if (str_contains($message, $pattern)) {
                    return [
                        'intent'   => $intent,
                        'response' => $data['response'],
                        'matched'  => true,
                    ];
                }
            }
        }

        return [
            'intent'   => 'unknown',
            'response' => "🤔 I didn't quite catch that. I can help you with:\n\n• 🚗 **Self-Drive fleet** — Sedans, SUVs, MPVs, Vans, Luxury\n• 📅 **Availability** — Check open dates\n• 💰 **Rates & Quotes** — Self-drive daily pricing\n• 📋 **Requirements** — Driver's license & deposit\n• ✈️ **Airport Pickup** — Self-drive NAIA pickup\n• 📞 **Contact** — Reach our team\n\nHow can I help?",
            'matched'  => false,
        ];
    }
}
