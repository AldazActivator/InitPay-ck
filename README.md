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
  "amount": 12.99,
  "note": "ORDER-ABC123",
  "redirect_url": "https://yourdomain.com/thank-you",
  "brand": "Your Brand",
  "customer_name": "John Doe",
  "description": "30-day Premium Membership",
  "image_url": "https://yourdomain.com/images/qr-binance.png"
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

## 🚧 Coming Soon

- ✅ Webhooks for backend payment confirmations
- ✅ Custom branding and color themes
- ✅ Telegram notification integration

---

## 📩 Need Help?

Contact us at [support@bysel.us](mailto:support@bysel.us)  
Join our Telegram: [@InitPaySupport](https://t.me/InitPaySupport)
