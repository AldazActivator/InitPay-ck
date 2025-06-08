<?php

class InitPay_Service
{
    private $gateway_url = "https://pay.bysel.us/api/create_payment";
    private $params = [];
    private $payload = [];
    public $http_code = 0;
    public $raw_response = '';

    public function __construct($params) {
        $this->params = $params;

        $invoiceId = $params['invoiceid'];
        $amountBase = floatval($params['amount']);
        $fee = floatval($params['trc20_network_fee']);
        $total = number_format($amountBase, 2, '.', '');

        $this->payload = [
            'order_id' => md5($invoiceId), // Usa hash si tu backend lo espera
            'invoice_number' => $invoiceId,
            'amount' => $total,
            'currency' => strtolower($params['currency'] ?? 'usdt'),
            'note' => $this->generateUniqueNote(),
            'brand' => 'InitPay',
            'customer_name' => $params['clientdetails']['firstname'] . ' ' . $params['clientdetails']['lastname'],
            'description' => "Base: $amountBase USDT + Fee: $fee",
            'billing_fname' => $params['clientdetails']['firstname'] ?? 'undefine',
            'billing_lname' => $params['clientdetails']['lastname'] ?? 'undefine',
            'billing_email' => $params['clientdetails']['email'] ?? 'undefine@example.com',
            'redirect_url' => $params['redirect_url'] ?: ($params['systemurl'] . 'viewinvoice.php?id=' . $invoiceId),
            'cancel_url' => $params['cancel_url'] ?: ($params['systemurl'] . 'viewinvoice.php?id=' . $invoiceId),
            'webhook_url' => $params['systemurl'] . 'initpay_webhook.php',
            'type' => 'dhru',
            'items' => [
                [
                    'name' => 'Invoice #' . $invoiceId,
                    'qty' => 1,
                    'price' => $total
                ]
            ]
        ];
    }

    public function generateUniqueNote(): string
    {
        $digits = range(0, 9);
        shuffle($digits);
        return implode('', array_slice($digits, 0, 4));
    }


    public function generate_link() {
        $authEncoded = base64_encode(trim($this->params['init_key']) . ':' . trim($this->params['init_secret']));
        $headers = [
            'Content-Type: application/json',
            'X-InitPay-Authorization: ' . $authEncoded
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->gateway_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->raw_response = $response;

        file_put_contents(__DIR__ . '/initpay_debug.log', date('Y-m-d H:i:s') . "\nPayload:\n" . json_encode($this->payload, JSON_PRETTY_PRINT) . "\nResponse:\n$response\n\n", FILE_APPEND);

        return json_decode($response, true);
    }
}

// Configuración del módulo WHMCS
function initpay_config() {
    return [
        'name' => [
            'Type' => 'System',
            'Value' => 'InitPay TRC20 Gateway'
        ],
        'init_key' => [
            'Name' => 'Init Key',
            'Type' => 'text',
            'Size' => '60'
        ],
        'init_secret' => [
            'Name' => 'Init Secret',
            'Type' => 'text',
            'Size' => '60'
        ],
        'trc20_network_fee' => [
            'Name' => 'TRC20 Network Fee',
            'Type' => 'text',
            'Value' => '1',
            'Size' => '10'
        ],
        'redirect_url' => [
            'Name' => 'Success URL',
            'Type' => 'text',
            'Size' => '80'
        ],
        'cancel_url' => [
            'Name' => 'Cancel URL',
            'Type' => 'text',
            'Size' => '80'
        ],
        'info' => [
            'Name' => 'Notas',
            'Type' => 'textarea',
            'Cols' => '5',
            'Rows' => '4'
        ]
    ];
}

// Generación del botón de pago
function initpay_link($params)
{
    global $lng_languag;
    $client = new InitPay_Service($params);
    $response = $client->generate_link();

    if ($client->http_code !== 200) {
        return '<p style="color:red;">InitPay Server Error. HTTP Code: ' . $client->http_code . '</p>';
    }

    if (!isset($response['checkout_url'])) {
        return '<p style="color:red;">' . htmlspecialchars($response['message'] ?? 'Invalid response from InitPay API') . '</p>';
    }

    return '<a class="btn btn-success pt-3 pb-3" style="width: 100%; background-color: green!important;" href="' . htmlspecialchars($response['checkout_url']) . '" target="_blank">' . ($lng_languag['invoicespaynow'] ?? 'Pay with InitPay') . '</a>';
}
