<?php

function cart_items(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return [];
    }

    $items = [];
    foreach ($_SESSION['cart'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $name = trim((string)($item['name'] ?? ''));
        if ($name === '') {
            continue;
        }

        $items[] = [
            'name' => $name,
            'price' => (float)($item['price'] ?? 0),
            'qty' => max(1, (int)($item['qty'] ?? 1)),
            'image' => (string)($item['image'] ?? ''),
            'description' => (string)($item['description'] ?? ''),
        ];
    }

    return $items;
}

function cart_total(): float
{
    $total = 0.0;
    foreach (cart_items() as $item) {
        $total += $item['price'] * $item['qty'];
    }

    return $total;
}

function stripe_line_items(): array
{
    $items = [];
    foreach (cart_items() as $item) {
        $items[] = [
            'price_data' => [
                'currency' => 'aud',
                'product_data' => [
                    'name' => $item['name'],
                    'description' => $item['description'],
                ],
                'unit_amount' => (int)round($item['price'] * 100),
            ],
            'quantity' => $item['qty'],
        ];
    }

    return $items;
}
