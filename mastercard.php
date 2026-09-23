<?php
session_start();

$cartTotal = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartTotal += $item['price'] * $item['qty'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['mastercard_payment'] = [
        'cardholder_name' => $_POST['cardholder_name'] ?? '',
        'amount' => $cartTotal,
        'status' => 'Completed',
        'transaction_id' => 'MC' . time()
    ];

    header('Location: success.php?gateway=mastercard');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mastercard Payment</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .payment-box { max-width: 700px; margin: auto; border: 1px solid #ccc; padding: 20px; }
        input { width: 95%; padding: 10px; margin-bottom: 15px; }
        button { background: #d50000; color: white; border: none; padding: 12px 25px; cursor: pointer; }
    </style>
</head>
<body>
<div class="payment-box">
    <h2>Mastercard Payment</h2>
    <img src="assets/img/mastercard.jpg" alt="Mastercard" width="150">
    <form method="post">
        <label for="card_number">Card Number</label>
        <input id="card_number" type="text" name="card_number" required>

        <label for="cardholder_name">Cardholder Name</label>
        <input id="cardholder_name" type="text" name="cardholder_name" required>

        <label for="expiry_date">Expiry Date</label>
        <input id="expiry_date" type="text" name="expiry_date" placeholder="MM/YY" required>

        <label for="cvv">CVV</label>
        <input id="cvv" type="text" name="cvv" required>

        <button type="submit">PAY NOW</button>
    </form>
    <h3>Total: $<?php echo number_format($cartTotal, 2); ?></h3>
</div>
</body>
</html>
