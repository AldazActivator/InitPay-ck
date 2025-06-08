<?php

define("DEFINE_MY_ACCESS", true);
define("DEFINE_DHRU_FILE", true);
include 'comm.php';
require 'includes/fun.inc.php';
include 'includes/gateway.fun.php';
include 'includes/invoice.fun.php';

date_default_timezone_set('Asia/Dhaka');
$logFile = "dhu_resps_log.log";

// Función para log
function logCallback($msg) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "$timestamp - $msg\n", FILE_APPEND);
}

// Cargar gateway config
$GATEWAY = loadGatewayModule('payerurl');

// Leer cuerpo del request
$rawBody = file_get_contents('php://input');
logCallback("Raw Input: $rawBody");

// Obtener auth header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (empty($authHeader)) {
    logCallback("Missing Authorization header");
    http_response_code(401);
    echo json_encode(['status' => 401, 'message' => 'Missing Authorization']);
    exit;
}

$authStr = str_replace('Bearer ', '', $authHeader);
$authDecoded = base64_decode($authStr);
[$authPublic, $authSignature] = explode(':', $authDecoded, 2);

// Verificar existencia de datos esenciales
$requiredFields = ['order_id', 'transaction_id', 'status_code', 'confirm_rcv_amnt'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        logCallback("Missing required field: $field");
        http_response_code(400);
        echo json_encode(['status' => 400, 'message' => "Missing field: $field"]);
        exit;
    }
}

// Preparar datos
$callbackData = [
    'order_id' => $_POST['order_id'],
    'ext_transaction_id' => $_POST['ext_transaction_id'] ?? '',
    'transaction_id' => $_POST['transaction_id'],
    'status_code' => (int)$_POST['status_code'],
    'note' => $_POST['note'] ?? '',
    'confirm_rcv_amnt' => (float)$_POST['confirm_rcv_amnt'],
    'confirm_rcv_amnt_curr' => $_POST['confirm_rcv_amnt_curr'] ?? '',
    'coin_rcv_amnt' => (float)($_POST['coin_rcv_amnt'] ?? 0),
    'coin_rcv_amnt_curr' => $_POST['coin_rcv_amnt_curr'] ?? '',
    'txn_time' => $_POST['txn_time'] ?? ''
];

// Verificar firma HMAC
ksort($callbackData);
$queryString = http_build_query($callbackData);
$expectedSignature = hash_hmac('sha256', $queryString, trim($GATEWAY['payerurl_secret_key']));

if (!hash_equals($expectedSignature, $authSignature)) {
    logCallback("Signature mismatch: expected=$expectedSignature, got=$authSignature");
    http_response_code(403);
    echo json_encode(['status' => 403, 'message' => 'Invalid signature']);
    exit;
}

// Si status_code no es 200, marcar como pendiente
if ($callbackData['status_code'] !== 200) {
    logTransaction('payerurl', $callbackData, 'Pending');
    logCallback("Payment not completed: status_code != 200");
    echo json_encode(['status' => 20000, 'message' => 'Order not completed']);
    exit;
}

// Agregar pago
$callbackData['FEE'] = 0.00;
addPayment(
    $callbackData['order_id'],
    $callbackData['transaction_id'],
    $callbackData['confirm_rcv_amnt'],
    $callbackData['FEE'],
    'InitPay USDT Gateway'
);

logTransaction('InitPay USDT Gateway', $_POST, 'Successful');
logCallback("Payment added for Order: {$callbackData['order_id']}");

echo json_encode(['status' => 2040, 'message' => 'Order updated successfully']);
exit;
