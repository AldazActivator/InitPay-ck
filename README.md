# InitPay-ck
API public for payment binance c2c

# 📘 InitPay Public API

**InitPay** is a public and secure payment API that allows developers, platforms, and businesses to generate dynamic USDT payment links using **Binance Pay**, with real-time status tracking — all without exposing sensitive credentials to the frontend.

---

## 🔐 Security

InitPay is built with security as its foundation:

- Binance API keys are **never exposed to clients or browsers**.
- All requests are authenticated via:
  - `Authorization: Bearer <Your_API_Key>` – issued by InitPay
  - `X-Binance-Command-AuthToken: base64(BINANCE_KEY:BINANCE_SECRET)` – passed only server-to-server
- Checkout URLs are:
  - **Valid for 15 minutes only**
  - Automatically destroyed after payment or expiration
- Notes (`note`) are one-time-use and verified directly with Binance’s transaction history.

---

## 🧾 Endpoint: Create a Payment Order

### URL
```
POST https://pay.bysel.us/api/create_payment
```

### Required Headers
| Header                         | Value                                  |
|--------------------------------|----------------------------------------|
| Authorization                 | `Bearer AldazDev`                      |
| X-Binance-Command-AuthToken   | `base64(BINANCE_KEY:BINANCE_SECRET)`  |
| Content-Type                  | `application/json`                    |

### Request Body Example
```json
{
  "amount": 17.49,
  "note": "ORDER_XYZ123DEF",
  "redirect_url": "https://example.com/thankyou",
  "brand": "FastVPN Pro",
  "customer_name": "Carlos Mendoza",
  "description": "1-Year Premium VPN Access",
  "image_url": "https://ialdaz-activator.com/ALDAZDEV/config/IMG_4445.jpg",
  "webhook_url": "https://example.com/webhooks/initpay"
}
```

### Successful Response
```json
{
  "checkout_url": "https://pay.bysel.us/checkout.php?id=ck_abc123xyz"
}
```

---

## ✅ What does InitPay do?

1. **Signs and validates** your Binance Pay credentials securely.
2. **Encrypts** and stores the session with your payment metadata.
3. **Generates a secure, time-limited `checkout_url`** that shows your client:
   - A branded payment page
   - QR code
   - Amount and note
   - Timer (15 minutes)
   - Real-time status updates
   - Auto-redirection upon completion or expiration

---

## 🔄 Redirect Flow

After payment is completed, cancelled, or expired, the user is redirected to your `redirect_url` with a query string indicating the status:

| Status     | Redirect URL                                 |
|------------|----------------------------------------------|
| Success    | `?status=success`                            |
| Cancelled  | `?status=cancelled`                          |
| Expired    | `?status=expired`                            |

---

## 🚀 Webhook: Receive Payment Notification

When a payment is confirmed as PAID, your provided `webhook_url` receives a POST request:

### Webhook Payload Example
```json
{
  "status": "PAID",
  "reference": "ORDER_XYZ123DEF",
  "amount": "17.49",
  "currency": "USDT",
  "paid_at": "2025-05-21T18:24:00+00:00",
  "customer": "Carlos Mendoza",
  "brand": "FastVPN Pro"
}
```

- You do **not** need to return anything specific in your webhook handler.
- Make sure to respond quickly with HTTP 200.
- You can log or act on the payment event.

Example basic handler in PHP:
```php
<?php
$logFile = __DIR__ . '/initpay_webhook.log';
$raw = file_get_contents('php://input');
file_put_contents($logFile, date('c') . ' - ' . $raw . PHP_EOL, FILE_APPEND);
http_response_code(200);
?>
```

---

## 📩 Need Help?

Contact us at [support@bysel.us](mailto:support@bysel.us)  
Join our Telegram: [@InitPaySupport](https://t.me/InitPaySupport)
