<?php
session_start();
require_once __DIR__ . '/cart-helpers.php';
require_once __DIR__ . '/square-config.php';

$cartItems = cart_items();
$cartTotal = cart_total();
$squareApplicationId = $squareApplicationId ?? '';
$squareLocationId = $squareLocationId ?? '';

if (empty($cartItems)) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Square Checkout</title>
    <style>
        :root {
            --ink: #191919;
            --muted: #707070;
            --line: #e6e6e6;
            --accent: #e6b943;
            --panel: #fafafa;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--ink);
            background: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .checkout-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(320px, 42%) minmax(420px, 58%);
        }

        .order-summary {
            display: flex;
            justify-content: center;
            background: var(--panel);
            padding: 72px 8vw 60px;
        }

        .summary-content,
        .payment-content {
            width: 100%;
            max-width: 430px;
        }

        .brand {
            display: inline-block;
            margin-bottom: 52px;
            color: var(--ink);
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
        }

        .summary-content h1 {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: 500;
        }

        .total {
            margin: 0 0 42px;
            font-size: 38px;
            letter-spacing: -1px;
        }

        .item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 0;
            border-top: 1px solid var(--line);
        }

        .item img {
            width: 54px;
            height: 54px;
            border-radius: 8px;
            object-fit: cover;
        }

        .item-details { flex: 1; }
        .item-name, .item-price { font-size: 14px; }
        .item-qty { color: var(--muted); font-size: 13px; margin-top: 4px; }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 22px 0;
            border-top: 1px solid var(--line);
            color: var(--muted);
            font-size: 14px;
        }

        .summary-row.total-row {
            color: var(--ink);
            font-size: 16px;
        }

        .payment-panel {
            display: flex;
            justify-content: center;
            padding: 72px 8vw 60px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 54px;
            color: var(--muted);
            font-size: 14px;
            text-decoration: none;
        }

        .payment-content h2 {
            margin: 0 0 26px;
            font-size: 24px;
            font-weight: 500;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 13px;
        }

        #square-card-container {
            max-width: 100%;
            margin: 0 0 20px;
        }

        #square-status {
            min-height: 24px;
            color: #b00000;
            font-size: 14px;
        }

        .checkout-btn {
            width: 100%;
            border: 0;
            border-radius: 4px;
            background: var(--accent);
            color: #191919;
            padding: 15px 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .checkout-btn:disabled {
            cursor: not-allowed;
            opacity: .55;
        }

        .secure-note {
            margin-top: 18px;
            color: var(--muted);
            font-size: 12px;
            text-align: center;
        }

        @media (max-width: 760px) {
            .checkout-page { display: block; }
            .order-summary, .payment-panel { padding: 36px 24px; }
            .brand { margin-bottom: 32px; }
            .back-link { margin-bottom: 30px; }
            .total { font-size: 32px; }
        }
    </style>
</head>
<body>
    <div class="checkout-page">
        <aside class="order-summary">
            <div class="summary-content">
                <a class="brand" href="index.php">ALICE'S ELECTRONIC BIKE SHOP</a>
                <h1>Order total</h1>
                <p class="total">A$<?php echo number_format($cartTotal, 2); ?></p>

                <?php foreach ($cartItems as $item) { ?>
                    <div class="item">
                        <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                        <div class="item-details">
                            <div class="item-name"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="item-qty">Quantity <?php echo (int)$item['qty']; ?></div>
                        </div>
                        <div class="item-price">A$<?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
                    </div>
                <?php } ?>

                <div class="summary-row"><span>Subtotal</span><span>A$<?php echo number_format($cartTotal, 2); ?></span></div>
                <div class="summary-row total-row"><span>Total due</span><strong>A$<?php echo number_format($cartTotal, 2); ?></strong></div>
            </div>
        </aside>

        <main class="payment-panel">
            <div class="payment-content">
                <a class="back-link" href="billing-page.php">&larr; Back to payment methods</a>
                <h2>Pay with Square</h2>
                <span class="form-label">Card information</span>
                <p id="square-status" role="status"></p>
                <div id="square-card-container"></div>
                <button type="button" class="checkout-btn" id="square-pay-btn" disabled>Pay A$<?php echo number_format($cartTotal, 2); ?></button>
                <p class="secure-note">Payments are securely processed by Square Sandbox.</p>
            </div>
        </main>
    </div>

    <script>
        window.squareConfig = {
            applicationId: <?php echo json_encode($squareApplicationId); ?>,
            locationId: <?php echo json_encode($squareLocationId); ?>
        };
    </script>
    <script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
    <script src="square.js"></script>
</body>
</html>
