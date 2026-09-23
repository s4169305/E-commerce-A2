<?php
session_start();
require_once __DIR__ . '/cart-helpers.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/secrets.php';

$cartItems = cart_items();
$cartTotal = cart_total();

if (empty($cartItems)) {
    header('Location: index.php');
    exit();
}

$stripe = new \Stripe\StripeClient($stripeSecretKey);
$YOUR_DOMAIN = 'http://localhost/A2/E-commerce-A2';

$lineItems = [];
foreach ($cartItems as $item) {
    $lineItems[] = [
        'price_data' => [
            'currency' => 'aud',
            'product_data' => [
                'name' => $item['name'],
                'description' => $item['description'],
            ],
            'unit_amount' => (int) round($item['price'] * 100),
        ],
        'quantity' => $item['qty'],
    ];
}

$checkout_session = $stripe->checkout->sessions->create([
    'line_items' => $lineItems,
    'mode' => 'payment',
    'success_url' => $YOUR_DOMAIN . '/success.php?gateway=stripe',
    'cancel_url' => $YOUR_DOMAIN . '/billing-page.php',
    'metadata' => [
        'cart_items' => json_encode($cartItems),
        'total' => (string)$cartTotal,
    ],
]);

header('HTTP/1.1 303 See Other');
header('Location: ' . $checkout_session->url);
exit();

