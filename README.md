# AI Chatbot Prototypes 🚗🛡️

This repository contains two PHP-based conversational AI chatbot prototypes powered by **Google Gemini AI** and **MongoDB Atlas**:

1. **Uni-Vanguard Insurance Chatbot** (`insurance-chatbot/`)
   - Specialized virtual insurance assistant for Uni-Vanguard Insurance Agency Inc. (vehicle and motor insurance lines).
   - Knowledge of 7 accredited insurance providers in the Philippines (Alpha, Bethel, Country Bankers, FGen, Metropolitan, Oona, Standard Insurance).
   - Dual-language support (English & Tagalog/Taglish).

2. **CartoGo Manila Chatbot** (`CartoGo Manila/`)
   - AI assistant for CartoGo Manila car rental services.
   - Strictly Self-Drive fleet rental (Sedans, SUVs, MPVs, Vans, Luxury cars).
   - Instant rate calculation, booking requirements, airport pickups, and terms.

---

## 🚀 Quick Start Guide

### 1. Requirements
- PHP 8.0 or higher
- cURL and OpenSSL extensions enabled in `php.ini`
- Composer (for MongoDB driver)

### 2. Running the Insurance Chatbot
```bash
cd insurance-chatbot
php -S localhost:8000 router.php
```
Open your browser to: `http://localhost:8000`

### 3. Running the CartoGo Manila Chatbot
```bash
cd "CartoGo Manila"
php -S localhost:8001 router.php
```
Open your browser to: `http://localhost:8001`

---

## 🛠️ Tech Stack & Features
- **Frontend**: Vanilla HTML5, CSS3, Modern Dark Theme, Responsive Chat Interface
- **Backend**: PHP 8 (No heavy framework, fast native cURL)
- **AI Engine**: Google Gemini Flash via REST API
- **Caching**: Smart filesystem caching to prevent API quota exhaustion
- **Database**: MongoDB Atlas for session history and audit logs
- **Fallback**: Rule-based intent handler for offline resilience
