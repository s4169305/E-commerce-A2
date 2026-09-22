<?php
session_start();

$cartTotal = 0;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartTotal += $item['price'] * $item['qty'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['mastercard_payment'] = [
        'cardholder_name' => $_POST['cardholder_name'],
        'amount' => $cartTotal,
        'status' => 'Completed',
        'transaction_id' => 'MC' . time()
    ];
    header("Location: success.php?gateway=mastercard");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mastercard Payment</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .payment-box {
            width: 700px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 20px;
        }

        input {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
        }

        button {
            background: #d50000;
            color: white;
            border: none;
            padding: 12px 25px;
            cursor: pointer;
        }
        .mastercard-logo {
            width: 150px;
            height: auto;
            display: block;
            margin: 20px auto;
        }
        .row {
            display: flex;
            gap: 20px;
        }

        .field {
            flex: 1;
        }

        .field input {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>

<body>

<div class="payment-box">

<h2>Mastercard Payment</h2>

    <?php if(isset($error)): ?>
    <p style="color:red;">
        <?php echo $error; ?>
    </p>
    <?php endif; ?>

    <p>
    <img src="assets/img/mastercard.jpg" alt="Mastercard" class="mastercard-logo">

    <h2>Billing Address</h2>

    <div class="row">
        <div class="field">
            <label>First Name</label>
            <input type="text" placeholder="John" required>
        </div>

        <div class="field">
            <label>Last Name</label>
            <input type="text" placeholder="Doeskip" required>
        </div>
    </div>

    <label>Email</label>
        <input type="text"
            placeholder="you@example.com"
            required>

    <label>Address</label>
        <input type="text"
            placeholder="1 Cheeseman Ave"
            required>
        
    <label>Address 2 (Optional)</label>
        <input type="text"
            placeholder="Apartment or suite">

    <div class="row">
        <div class="field">
            <label>Country</label>
            <input type="text" placeholder="Country" required>
        </div>

        <div class="field">
            <label>State</label>
            <input type="text" placeholder="State" required>
        </div>

        <div class="field">
            <label>Zip</label>
            <input type="text" placeholder="Zip" required>
        </div>
    </div>



<br>


<h2>Payment Information</h2>
    <form method="post">

        <label>Card Number</label>
        <input type="text"
            name="card_number"
            placeholder="5555 5555 5555 4444"
            required>

        <label>Cardholder Name</label>
        <input type="text"
            name="cardholder_name"
            placeholder="John Doeskip"
            required>

        <label>Expiry Date</label>
        <input type="text"
            name="expiry_date"
            placeholder="MM/YY"
            required>

        <label>CVV</label>
        <input type="text"
            name="cvv"
            placeholder="123"
            required>

        <button type="submit">
            PAY NOW
        </button>

    </form>

    <h3>
        Total: $
        <?php echo number_format($cartTotal, 2); ?>
    </h3>

</div>

</body>
</html>
