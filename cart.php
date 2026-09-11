<?php
// Including the configuration file
include_once "paypalconfig.php";
session_start();

// Adding item to shopping cart
if (isset($_POST['add_to_cart'])) {
    $_SESSION['cart'][] = [
        'name'  => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image'],
        'description' => $_POST['description'],
        'qty' => 1
    ];
}

// Removing items in the shopping cart
if(isset($_POST['remove_index'])) {
    $index = $_POST['remove_index'];
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

// Updating price to match quantity
if (isset($_POST['update_qty'])) {
    $index = $_POST['update_index'];
    $_SESSION['cart'][$index]['qty'] = (int)$_POST['qty'];
    header("Location: cart.php");
    exit();
}

// Calculating total price of items
$cartTotal = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartTotal += $item['price'] * $item['qty'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
        .cart-container {
            width: 80%;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .cart-header, .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .cart-item {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .cart-item img {
            max-width: 100px;
        }
        .cart-item p {
            margin: 0;
        }
        .cart-item .description {
            flex: 2;
            padding: 0 10px;
        }
        .cart-item .price, .cart-item .qty, .cart-item .total {
            flex: 1;
            text-align: center;
        }
        .cart-item .qty input {
            width: 50px;
            text-align: center;
        }
        .update-btn, .remove-btn {
            background-color: black;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .payment-section {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .payment-section h2 {
            margin-bottom: 20px;
        }
        .payment-options {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .payment-options label {
            cursor: pointer;
            border: 1px solid #ccc;
            padding: 8px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .payment-options label:hover {
            border-color: #007bff;
        }
        .payment-options input[type="radio"] {
            display: none;
        }
        .payment-options img {
            width: 100px;
            height: 60px;
            object-fit: contain;
        }
        .payment-options input[type="radio"]:checked + img {
            border: 3px solid #007bff;
            border-radius: 5px;
        }
        .checkout-container {
            display: none;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .checkout-btn {
            background: #d50000;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }
        .checkout-btn:hover {
            background: #b00000;
        }
        #mastercard-checkout-container {
            display: none;
        }
    </style>
</head>
<body>

<h1>Shopping Cart</h1>

<form method="post">
<div class="cart-header">
    <div>Product</div>
    <div class="description">Product Description</div>
    <div class="qty">Qty</div>
    <div class="price">
        $<?php echo number_format($item['price'], 2); ?>
    </div>
    <div class="total">
        $
        <?php
        echo number_format(
            $item['price'] * $item['qty'],
            2
        );
        ?>
    </div>
</div>
<?php
if(isset($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $index => $item) {
?>
<div class="cart-item">
    <div>
        <input type="checkbox"
               name="selected[]"
               value="<?php echo $index; ?>">
    </div>
    <div>
        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>"
            width="100">
    </div>

    <!-- Item description-->
    <div class="description">
        <p>
            <strong><?php echo $item['name']; ?></strong>
        </p>
        <p>
            <?php echo $item['description']; ?>
        </p>
        <p>
            Availability:
            <span style="color:green;">
                Online
            </span>
            <span style="color:blue;">
                Immediate Pick-up
            </span>
        </p>
    </div>

    <!-- Item quantity -->
    <div class="qty">
        <form method="post">
            <input type="hidden"
                name="update_index"
                value="<?php echo $index; ?>">
            <input type="number"
                name="qty"
                min="1"
                value="<?php echo $item['qty']; ?>">
            <button type="submit"
                    name="update_qty">
                Update
            </button>
        </form>
    </div>

    <!-- Item price-->
    <div class="price">
        $<?php echo $item['price']; ?>
    </div>

    <!-- Removing items-->
    <form method="post">
        <input type="hidden" name="remove_index"
            value="<?php echo $index; ?>">

        <button type="submit" class="remove-btn">
        REMOVE
        </button>
    </form>
</div>
<?php
    }
}
?>


<div style="text-align:right; margin-top:20px;">
    <h3>Total: $<?php echo number_format($cartTotal, 2); ?></h3>
</div>



    <!-- Adding 4 payment options -->
    <div class="payment-section">
        <h2>Select Payment Option</h2>

        <div class="payment-options">
            <label>
                <input type="radio" name="payment" value="visa">
                <img src="assets/img/visa.jpg">
            </label>

            <label>
                <input type="radio" name="payment" value="mastercard">
                <img src="assets/img/mastercard.jpg">
            </label>

            <label>
                <input type="radio" name="payment" value="googlepay" id="googlepay">
                <img src="assets/img/googlepay.png">
            </label>

            <label>
                <input type="radio" name="payment" value="paypal" id="paypal">
                <img src="assets/img/paypal.png">
            </label>
        </div>
        <div id="google-pay-container" style="display:none;">
                <h3>Pay with Google Pay</h3>

                <script src="index.js"></script>

                <script async
                        src="https://pay.google.com/gp/p/js/pay.js"
                        onload="onGooglePayLoaded()">
                </script>

                <div id="google-pay-button"></div>
        </div>

            <!-- define PayPal button and send data -->
					<form action="<?php echo PAYPAL_URL; ?>" method="post" style="padding: 0; margin: 0;">

						<input type="hidden" name="cmd" value="_xclick" />

                    <!-- Identify your business so that you can collect the payments. -->
						<input type="hidden" name="business" value="<?php echo PAYPAL_ID; ?>" />

                    <!-- Specify details about the item that buyers will purchase. part of this field will be used in ipn.php-->
						<input type="hidden" name="item_name" value="Electric Bike Order">
						<input type="hidden" name="item_number" value="ORDER001">
						<input type="hidden" name="amount" value="<?php echo $cartTotal; ?>">
						<input type="hidden" name="currency_code" value="<?php echo PAYPAL_CURRENCY; ?>" />

                    <!-- Specify URLs -->
						<input type="hidden" name="return" value="<?php echo PAYPAL_RETURN_URL; ?>">
						<input type="hidden" name="notify_url" value="<?php echo PAYPAL_NOTIFY_URL; ?>">

                    <div class="checkout-container"
                        id="paypal-checkout-container">
                        <button type="submit"
                                class="checkout-btn">
                            CHECKOUT NOW
                        </button>
                    </div>

                </form>

            <!-- define Google Pay button and send data -->
                <div class="checkout-container"
                    id="google-checkout-container">
                    <button type="button"
                            class="checkout-btn"
                            id="google-pay-btn">

                
                        CHECKOUT NOW
                    </button>
                </div>
            
            <!-- define Mastercard button and send data -->
            <div class="checkout-container"
                id="mastercard-checkout-container">

                <button type="button"
                        class="checkout-btn"
                        id="mastercard-btn">

                    CHECKOUT NOW

                </button>

            </div>

    </div>
</div>


<!-- Opening the correct payment page for the payment option selected -->
<script>
// defining payment method constants
const paymentMethods =
    document.querySelectorAll('input[name="payment"]');

const paypalContainer =
    document.getElementById('paypal-checkout-container');

const googleCheckoutContainer =
    document.getElementById('google-checkout-container');

const mastercardContainer =
    document.getElementById(
        'mastercard-checkout-container'
    );

paymentMethods.forEach(method => {
    method.addEventListener('change', function() {
        if (this.value === 'paypal') {
            paypalContainer.style.display = 'flex';
            googleCheckoutContainer.style.display = 'none';
            mastercardContainer.style.display = 'none';

        } else if (this.value === 'googlepay') {
            paypalContainer.style.display = 'none';
            googleCheckoutContainer.style.display = 'flex';
            mastercardContainer.style.display = 'none';

        } else if (this.value === 'mastercard') {
            paypalContainer.style.display = 'none';
            googleCheckoutContainer.style.display = 'none';
            mastercardContainer.style.display = 'flex';

        } else {
            paypalContainer.style.display = 'none';
            googleCheckoutContainer.style.display = 'none';
            mastercardContainer.style.display = 'none';

        }

    });

});
</script>


<!-- Script for Google Pay -->
<script>
const cartTotal = "<?php echo $cartTotal; ?>";
</script>

<script src="googlepay.js"></script>

<script async
    src="https://pay.google.com/gp/p/js/pay.js"
    onload="onGooglePayLoaded()">
</script>

<script>
document.getElementById('google-pay-btn').addEventListener('click', function() {
    onGooglePaymentButtonClicked();
});
</script>

<script>
document.getElementById('mastercard-btn').addEventListener('click', function() {
    window.location.href = "mastercard.php";
});
</script>


</body>
</html>
