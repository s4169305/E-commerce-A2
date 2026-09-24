<?php
session_start();
require_once __DIR__ . '/cart-helpers.php';
require_once __DIR__ . '/square-config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST required']);
    exit;
}

$accessToken = $squareAccessToken ?? '';
$locationId = $squareLocationId ?? '';
$input = json_decode(file_get_contents('php://input'), true);
$sourceId = trim((string)($input['source_id'] ?? ''));

if ($accessToken === '' || $locationId === '') {
    http_response_code(503);
    echo json_encode(['error' => 'Square Sandbox credentials are not configured']);
    exit;
}

if ($sourceId === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Square payment token is missing']);
    exit;
}

$amount = (int)round(cart_total() * 100);
if ($amount < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'The cart is empty']);
    exit;
}

$payload = json_encode([
    'source_id' => $sourceId,
    'idempotency_key' => bin2hex(random_bytes(16)),
    'amount_money' => [
        'amount' => $amount,
        'currency' => 'AUD',
    ],
    'location_id' => $locationId,
    'autocomplete' => true,
]);

$curl = curl_init('https://connect.squareupsandbox.com/v2/payments');
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'Square-Version: 2025-01-23',
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_TIMEOUT => 20,
]);
$response = curl_exec($curl);
$httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Square request failed', 'detail' => $curlError]);
    exit;
}

$data = json_decode($response, true);
if ($httpCode < 200 || $httpCode >= 300 || empty($data['payment']['id'])) {
    http_response_code($httpCode >= 400 ? $httpCode : 502);
    echo json_encode([
        'error' => $data['errors'][0]['detail'] ?? 'Square payment was declined',
        'square_errors' => $data['errors'] ?? [],
    ]);
    exit;
}

echo json_encode([
    'payment_id' => $data['payment']['id'],
    'status' => $data['payment']['status'] ?? 'COMPLETED',
]);
