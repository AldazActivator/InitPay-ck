
# InitPay-ck

API public for secure TRC20 USDT payments via Binance C2C and WHMCS integration.

---

## 📘 InitPay Public API

**InitPay** is a secure and flexible TRC20 payment API for platforms, businesses, and developers. It supports **Binance Pay C2C**, **direct TRC20 transfers**, and **WHMCS-compatible webhooks**. Payments are verified via Binance, secured with 2FA-protected keys, and backed by time-limited tokens.

---

## 🔐 Security Highlights

- Binance credentials are **never exposed to the frontend**.
- Checkout sessions are:
  - 🔒 **Encrypted and time-limited (15 minutes)**
  - 💥 Auto-destroyed on success, failure, or timeout
- All requests are signed via:

  | Header                        | Description                            |
  |------------------------------|----------------------------------------|
  | `X-InitPay-Authorization`    | `base64(initKey:initKeySecret)`        |

---

## 🧾 Endpoint: Create a Payment Order

### URL
```
POST https://pay.bysel.us/api/create_payment
```

### Required Headers

```http
Content-Type: application/json
X-InitPay-Authorization: base64(initKey:initKeySecret)
```

### Request Body Example

```json
{
  "order_id": "12345",
  "amount": 10.0,
  "currency": "usdt",
  "note": "38492",
  "brand": "MiTienda",
  "description": "Compra mensual",
  "redirect_url": "https://mitienda.com/pago-exitoso",
  "cancel_url": "https://mitienda.com/pago-cancelado",
  "customer_name": "Juan Pérez",
  "billing_fname": "Juan",
  "billing_lname": "Pérez",
  "billing_email": "juan@example.com",
  "items": [
    {
      "name": "Suscripción mensual",
      "qty": 1,
      "price": 10.0
    }
  ],
  "type": "dhru",
  "webhook_url": "https://midominio.com/callback.php"
}
```

### Response

```json
{
  "checkout_url": "https://pay.bysel.us/checkout.php?id=ck_abcd1234xyz"
}
```

---

## ✅ What does InitPay do?

1. ✅ **Validates your API credentials** and note uniqueness
2. ✅ **Encrypts and stores payment metadata** for 15 minutes
3. ✅ **Generates a branded checkout** page with:
   - QR, logo, brand
   - Auto-status checker
   - Redirect flow

---

## 🔄 Redirect Flow

After processing:

| Status     | Redirect URL                                 |
|------------|----------------------------------------------|
| Success    | `?status=success`                            |
| Cancelled  | `?status=cancelled`                          |
| Expired    | `?status=expired`                            |

---

## 🚀 Webhook: WHMCS-Compatible Callback

If `webhook_url` is provided and payment is confirmed, InitPay will issue a **signed POST** to that URL using `application/x-www-form-urlencoded` with:

```text
order_id
transaction_id
status_code = 200
note
confirm_rcv_amnt
confirm_rcv_amnt_curr
coin_rcv_amnt
coin_rcv_amnt_curr
txn_time
Authorization: Bearer base64(initKey:signature)
```

- Signature uses HMAC-SHA256 of sorted params with your secret key.
- This is compatible with WHMCS `callback.php` gateways.

---

## ✨ Recent Improvements

- 🔐 HMAC-secured webhooks (compatible with WHMCS/DHRU)
- 📦 Added full support for `order_id`, `items[]`, `customer_name`
- ⏱️ Faster Binance TX validation engine
- 📊 Dashboard tracking with 2FA login and encrypted tokens
- 💬 Telegram verification for secure 2FA login
- ✅ DHRU-friendly type support (`"type": "dhru"`)

---

## 📩 Need Help?

📬 Email: [support@bysel.us](mailto:support@bysel.us)  
📦 Panel: [https://pay.bysel.us/init/index](https://pay.bysel.us/init/index)  
🛡️ Register: [https://pay.bysel.us/init/register](https://pay.bysel.us/init/register)  
📣 Telegram: [@InitPaySupport](https://t.me/InitPaySupport)
