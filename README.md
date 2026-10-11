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
- Role-based redirect after login (client dashboard, driver dashboard, admin dashboard)
- `role:` middleware so client, driver and admin areas are only reachable by that role, plus ownership checks on every delivery, tracking, payment and chat endpoint
- Login, OTP and password-reset endpoints are rate limited

**Deliveries**
- Delivery request form with Google Places autocomplete for pickup and drop-off, storing coordinates in a `delivery_locations` table
- Package length / width / height (volume computed), weight, size class (small / medium / large), urgency (normal / urgent), delivery date, contact phone and notes
- Status lifecycle: `pending` -> `accepted` -> `in_progress` -> `delivered`
- Driver panel: list of available (unassigned) requests, accept a request, list of active deliveries, start a delivery and mark it delivered
- Dashboards with real data: client (delivery counts, recent deliveries with Track / Pay buttons), driver (active jobs, completed jobs, open requests, paid earnings) and admin (users, deliveries by status, platform fees, latest deliveries)
- Driver onboarding form after sign-up: vehicle type/brand/model, plate and license numbers, registration document upload, and pricing model (fixed price or per km)

**Live tracking**
- While a delivery is `in_progress`, the driver's tracking page pushes the browser's geolocation to `POST /api/tracking/update-location` (only the assigned driver can post for that delivery)
- The client's tracking page polls `GET /api/tracking/driver-location/{driverUserId}` every 5 seconds, moves the driver marker on a Google Map and shows an ETA (only the driver, an admin, or a client with an active delivery assigned to that driver can read it)
- The API routes use the normal session (`web` + `auth` middleware) because they are called from the Blade pages; the driver's last position is stored as a `live` row in the `locations` table

**Chat**
- Client-driver messaging limited to users who share a delivery (enforced on the contact list, history, new-message stream and send endpoint)
- Chat history and polling for new messages (server-sent events)

**Payments**
- "Pay" button on the tracking page, dashboard and delivery list
- Payment page with currency selection (currencies seeded with their USD value) and a fee split: 10% platform / 90% driver (or 10 / 80 / 10 when a center is involved). All amounts and fees are computed on the server from the delivery price; the form only sends the method and currency
- Card payments through Stripe Checkout; the payment is only marked paid after the Checkout Session is fetched back from Stripe and is `paid` for the right amount
- Crypto payments through NOWPayments hosted invoices, confirmed by the signed NOWPayments IPN webhook (`POST /webhook/nowpayments`)
- A Coinbase Commerce checkout with a signed webhook (`POST /webhook/coinbase`) is also available as an alternative gateway (not linked from the payment page)
- Cash on delivery: chosen on the payment page, and the delivery is flagged paid when the driver marks it delivered

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
    AdminDashboardController, ClientDashboardController
    Auth/                Google, Facebook, OTP, forgot/reset password
    DeliveryController   create / store / track deliveries
    DriverController     driver profile, driver dashboard, complete delivery
    DriverPanelController available, accept, start deliveries
    ChatsController      client-driver messaging
    PaymentController    payment page and fee split
    StripeController     Stripe Checkout
    CryptoController     NOWPayments (IPN webhook) + Coinbase Commerce (webhook)
    LoginController, signupController, VerificationController
  Http/Middleware/       EnsureUserHasRole (`role:client`, `role:driver`, `role:admin`)
  Mail/                  LoginOtpMail, PasswordResetMail, VerificationMail
  Models/                DeliveryRequest, Driver, Client, Payment, Chat, ...
database/
  migrations/            full schema
  seeders/               CurrencySeeder
resources/views/
  home, about, login, register, dashboards/, admin/, delivery/, driver/, client/, chat/, payments/, emails/
routes/
  web.php, api.php       (both registered in bootstrap/app.php)
tests/Feature/           DeliveryFlowTest: roles, full delivery + live tracking, payments, chat, OTP login
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
git clone https://github.com/Alex03issa/Delivery-App-System.git
cd Delivery-App-System
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
- `STRIPE_KEY`, `STRIPE_SECRET` - Stripe Checkout (test keys work)
- `NOWPAYMENTS_API_KEY`, `NOWPAYMENTS_IPN_SECRET` - NOWPayments invoices and IPN callbacks (the IPN URL is `https://<your-host>/webhook/nowpayments`, so it must be publicly reachable)
- `COINBASE_COMMERCE_KEY`, `COINBASE_WEBHOOK_SECRET` - optional Coinbase Commerce gateway (webhook URL `https://<your-host>/webhook/coinbase`)
- `GOOGLE_CLIENT_ID/SECRET`, `FACEBOOK_CLIENT_ID/SECRET` - social login

### Run

```bash
php artisan migrate
php artisan db:seed          # seeds currencies
php artisan serve
```

Open http://localhost:8000.

There is no admin sign-up page: create an admin by changing a user's `role` to `admin` in the database.

### Tests

```bash
php artisan test
```

The feature tests run on an in-memory SQLite database and cover role protection, the full delivery flow with live tracking, server-side payment amounts, Stripe/NOWPayments confirmation rules, chat permissions and the OTP login.

## Notes

- No real credentials are included. The Google Maps API key that was hard-coded in the Blade views has been replaced with `config('services.google_maps.key')` (read from `GOOGLE_MAPS_API_KEY`).
- Secrets are read through `config()` (never `env()` in app code), so `php artisan config:cache` is safe. Set `APP_DEBUG=false` in production.
- Payment gateways run against whatever keys you provide; use test-mode keys for local development.
- Known limitations: the delivery price is entered by the client (the `package_size_rules` table and the driver's per-km / fixed price are not used for pricing yet), driver approval (`is_approved`) is not enforced, and reviews, rewards, strikes and notifications exist only as database tables.

## Author

Alexander Issa - [@Alex03issa](https://github.com/Alex03issa)
