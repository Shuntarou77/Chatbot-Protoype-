<?php
namespace App;

class IntentHandler {

    private array $intents = [
        'greeting' => [
            'patterns' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'start'],
            'response' => "👋 Hello! I'm InsureBot, your virtual insurance assistant.\n\nHow can I help you today?\n\n• 📋 **Get a quote**\n• 📄 **Check my policy**\n• 🚨 **File a claim**\n• ❓ **FAQs**\n• 📞 **Talk to an agent**",
        ],
        'quote' => [
            'patterns' => ['quote', 'price', 'cost', 'how much', 'premium', 'rate', 'cheapest', 'affordable'],
            'response' => "📋 I'd love to help you get a quote!\n\nPlease tell me:\n1. **Type of insurance** — Auto / Home / Life / Health\n2. **Your age**\n3. **Coverage amount** needed\n\nOr visit our quote page: **quote.myinsurance.com**",
        ],
        'auto_insurance' => [
            'patterns' => ['car insurance', 'auto insurance', 'vehicle insurance', 'car policy', 'motor'],
            'response' => "🚗 **Auto Insurance Plans:**\n\n• **Basic** — Third-party liability (from ₱2,500/yr)\n• **Comprehensive** — Full coverage incl. theft & damage (from ₱8,000/yr)\n• **Premium** — Comprehensive + roadside assistance (from ₱15,000/yr)\n\nWant a personalized quote? Tell me your vehicle's year, make, and model!",
        ],
        'health_insurance' => [
            'patterns' => ['health insurance', 'medical insurance', 'hospitalization', 'hmo', 'health plan'],
            'response' => "🏥 **Health Insurance Plans:**\n\n• **Basic Health** — Hospitalization up to ₱100K (from ₱3,000/yr)\n• **Family Plan** — Covers up to 5 members (from ₱12,000/yr)\n• **Premium Health** — Includes dental & optical (from ₱20,000/yr)\n\nShall I connect you with a health advisor?",
        ],
        'claim' => [
            'patterns' => ['claim', 'accident', 'damage', 'report', 'incident', 'file a claim', 'stolen'],
            'response' => "🚨 **To File a Claim:**\n\n**Step 1:** Call our 24/7 hotline: **1-800-INSURE-1**\n**Step 2:** Or use our app / visit **claims.myinsurance.com**\n**Step 3:** Have your **policy number** and **ID** ready\n\n⏱️ Claims are typically processed within **3–5 business days**.\n\nDo you need help finding your policy number?",
        ],
        'policy' => [
            'patterns' => ['policy', 'coverage', 'plan details', 'benefits', 'deductible', 'my plan', 'check policy'],
            'response' => "📄 **To check your policy details:**\n\n1. Log in to **myinsurance.com/account**\n2. Or call us at **1-800-INSURE-0**\n3. Or provide your **policy number** here and I'll look it up!\n\nWhat's your policy number? (Format: INS-XXXXX)",
        ],
        'contact' => [
            'patterns' => ['contact', 'agent', 'human', 'speak to', 'call', 'phone', 'email', 'support'],
            'response' => "📞 **Contact Us:**\n\n• **Phone:** 1-800-INSURE-0 (24/7)\n• **Email:** support@myinsurance.com\n• **Live Chat:** myinsurance.com/chat\n• **Office Hours:** Mon–Fri, 8AM–6PM\n\nWould you like me to schedule a callback?",
        ],
        'faq_payment' => [
            'patterns' => ['payment', 'pay', 'billing', 'due', 'invoice', 'installment'],
            'response' => "💳 **Payment Options:**\n\n• Online via **myinsurance.com/pay**\n• Bank transfer / GCash / Maya\n• Auto-debit from your bank account\n• Over-the-counter at partner banks\n\nPayment is due on the **1st of every month**. Late payments have a 3-day grace period.",
        ],
        'farewell' => [
            'patterns' => ['bye', 'goodbye', 'thanks', 'thank you', 'exit', 'done', 'that\'s all'],
            'response' => "👋 Thank you for using InsureBot! Have a great day.\n\n🛡️ *Stay protected. Stay covered.*\n\nFeel free to chat again anytime!",
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

        // Check for policy number pattern
        if (preg_match('/INS-\d{5}/i', $message)) {
            return [
                'intent'   => 'policy_lookup',
                'response' => "🔍 Looking up policy **" . strtoupper(preg_replace('/[^INS0-9-]/i', '', $message)) . "**...\n\n✅ **Policy Found!**\n• **Type:** Comprehensive Auto\n• **Status:** Active\n• **Coverage:** ₱500,000\n• **Renewal Date:** March 15, 2027\n\n*(This is demo data — connect your MongoDB to show real policies)*",
                'matched'  => true,
            ];
        }

        return [
            'intent'   => 'unknown',
            'response' => "🤔 I'm not sure I understand. Could you rephrase that?\n\nYou can ask me about:\n• 📋 **Quotes** — Get a price estimate\n• 🚨 **Claims** — Report an incident\n• 📄 **Policy** — View your coverage\n• 💳 **Payments** — Billing info\n• 📞 **Contact** — Reach a human agent",
            'matched'  => false,
        ];
    }
}
