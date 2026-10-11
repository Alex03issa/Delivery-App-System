# Delivery App System

A Laravel 12 web platform that connects clients who need packages delivered with registered drivers, with live map tracking, in-app chat and card / crypto / cash-on-delivery payments.

## Overview

Clients sign up, create a delivery request (pickup and drop-off picked on a Google Map, package dimensions, weight, urgency, date) and follow the driver live on a map. Drivers register their vehicle details, browse pending requests, accept one, start it and stream their GPS position back to the server while they drive. Both sides can chat with each other, and the client pays through Stripe, a crypto gateway, or cash on delivery.

Built in April–May 2025.

## Features

**Accounts and security**
- Client and driver registration with role selection and password-strength rules
- Email verification links signed with an HMAC and encrypted with Laravel `Crypt`
- Emails stored encrypted, with a salted HMAC hash used for look-ups at login
- Email one-time password (OTP) as a second login step
- Forgot-password / reset-password flow with custom mail templates
- Social login with Google and Facebook (Laravel Socialite)
- Role-based redirect after login (client dashboard, driver panel, admin)

**Deliveries**
- Delivery request form with Google Places autocomplete for pickup and drop-off, storing coordinates in a `delivery_locations` table
- Package length / width / height (volume computed), weight, size class (small / medium / large), urgency (normal / urgent), delivery date, contact phone and notes
- Status lifecycle: `pending` -> `accepted` -> `in_progress` -> completed
- Driver panel: list of available (unassigned) requests, accept a request, list of active deliveries, start and complete a delivery
- Driver onboarding form after sign-up: vehicle type/brand/model, plate and license numbers, registration document upload, and pricing model (fixed price or per km)

**Live tracking**
- Driver page pushes the browser's geolocation to `/api/driver/update-location` / `/api/tracking/update-location`
- Client tracking pages poll `/api/tracking/driver-location/{driverId}` every 5 seconds, move the driver marker on a Google Map and show an ETA and online/offline status

**Chat**
- Client-driver messaging limited to users who share a delivery
- Chat history and polling for new messages

**Payments**
- Payment page with currency selection (currencies seeded with a USD conversion rate) and a fee split: 10% platform / 90% driver (or 10 / 80 / 10 when a center is involved)
- Card payments through Stripe Checkout
- Crypto payments through NOWPayments, plus a Coinbase Commerce checkout with a signed webhook
- Cash on delivery confirmed immediately

**Data model** (migrations included): users, clients, drivers, delivery requests and locations, payments, currencies, chats and chat sessions, reviews (with flagging), driver strikes (with severity), driver logs, notifications, calendar integrations, reward rules, user rewards and redemptions, package-size pricing rules.

## Tech Stack

- **Backend:** PHP 8.2, Laravel 12, Laravel Sanctum, Laravel Socialite, Guzzle
- **Payments:** stripe/stripe-php, NOWPayments API, Coinbase Commerce API
- **Frontend:** Blade templates, Bootstrap, jQuery, Font Awesome / Ionicons / Line Awesome
- **Maps:** Google Maps JavaScript API (Places library)
- **Database:** SQLite by default (any Laravel-supported DB, e.g. MySQL, works)
- **Mail:** Laravel Mailables (verification, OTP, password reset)

## Project Structure

```
app/
  Http/Controllers/
    API/                 driver location update + latest-location endpoints
    Auth/                Google, Facebook, OTP, forgot/reset password
    DeliveryController   create / store / track deliveries
    DriverPanelController available, accept, start deliveries
    ChatsController      client-driver messaging
    PaymentController    payment page and fee split
    StripeController     Stripe Checkout
    CryptoController     NOWPayments + Coinbase Commerce (webhook)
    LoginController, signupController, VerificationController
  Mail/                  LoginOtpMail, PasswordResetMail, VerificationMail
  Models/                DeliveryRequest, Driver, Client, Payment, Chat, ...
database/
  migrations/            full schema
  seeders/               CurrencySeeder
resources/views/
  home, about, login, register, delivery/, driver/, client/, Chat/, payments/, emails/
routes/
  web.php, api.php
public/assets/           CSS, JS, fonts, images
```

## Getting Started

### Prerequisites
- PHP 8.2+ with the usual Laravel extensions
- Composer
- A Google Maps JavaScript API key (Maps + Places)
- Optional: Stripe, NOWPayments / Coinbase Commerce, Google and Facebook OAuth credentials, and an SMTP server for emails

### Installation

```bash
git clone https://github.com/Alex03issa/<repo-name>.git
cd <repo-name>
composer install
cp .env.example .env
php artisan key:generate
```

### Configuration

Edit `.env`:

- `DB_CONNECTION` and related settings (SQLite works out of the box: `touch database/database.sqlite`)
- `HASH_SECRET` - any long random string (used to hash emails and sign verification tokens)
- `GOOGLE_MAPS_API_KEY` - required for the delivery form and tracking maps
- `MAIL_*` - SMTP settings so verification, OTP and reset emails can be sent (`MAIL_MAILER=log` writes them to the log instead)
- `STRIPE_KEY`, `STRIPE_SECRET`, `NOWPAYMENTS_API_KEY`, `COINBASE_COMMERCE_KEY`, `COINBASE_WEBHOOK_SECRET` - payment gateways
- `GOOGLE_CLIENT_ID/SECRET`, `FACEBOOK_CLIENT_ID/SECRET` - social login

### Run

```bash
php artisan migrate
php artisan db:seed          # seeds currencies
php artisan serve
```

Open http://localhost:8000.

## Notes

- No real credentials are included. The Google Maps API key that was hard-coded in the Blade views has been replaced with `config('services.google_maps.key')` (read from `GOOGLE_MAPS_API_KEY`).
- The admin dashboard views come from the original UI template and are mostly static.
- Payment gateways run against whatever keys you provide; use test-mode keys for local development.

## Author

Alexander Issa - [@Alex03issa](https://github.com/Alex03issa)
