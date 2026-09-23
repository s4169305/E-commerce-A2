<?php
include_once "paypalconfig.php";
require_once __DIR__ . '/cart-helpers.php';

function normalizePaypalCountry($country) {
    $country = trim((string)$country);
    if ($country === '') {
        return 'AU';
    }

    $map = [
        'australia' => 'AU',
        'au' => 'AU',
        'united states' => 'US',
        'usa' => 'US',
        'united states of america' => 'US',
        'us' => 'US',
        'united kingdom' => 'GB',
        'uk' => 'GB',
        'canada' => 'CA',
        'new zealand' => 'NZ',
        'singapore' => 'SG',
        'india' => 'IN',
    ];

    $key = strtolower($country);
    if (isset($map[$key])) {
        return $map[$key];
    }

    return strtoupper(substr($country, 0, 2));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$billing = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'address' => '',
    'address2' => '',
    'city' => '',
    'state' => '',
    'zip' => '',
    'country' => '',
    'username' => ''
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $billing = [
        'firstname' => trim($_POST['firstname'] ?? ''),
        'lastname' => trim($_POST['lastname'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'address2' => trim($_POST['address2'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'zip' => trim($_POST['zip'] ?? ''),
        'country' => trim($_POST['country'] ?? ''),
        'username' => trim($_POST['username'] ?? '')
    ];
    $_SESSION['billing'] = $billing;
}

if (isset($_SESSION['billing']) && is_array($_SESSION['billing'])) {
    $billing = array_merge($billing, $_SESSION['billing']);
}

$cartTotal = cart_total();

$squareApplicationId = trim((string)(getenv('SQUARE_SANDBOX_APPLICATION_ID') ?: ($_SERVER['SQUARE_SANDBOX_APPLICATION_ID'] ?? '')));
$squareLocationId = trim((string)(getenv('SQUARE_SANDBOX_LOCATION_ID') ?: ($_SERVER['SQUARE_SANDBOX_LOCATION_ID'] ?? '')));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Billing & Payment</title>
    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!-- Fontawesome core CSS -->
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" />
    <!--GOOGLE FONT -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <!--Slide Show Css -->
    <link href="assets/ItemSlider/css/main-style.css" rel="stylesheet" />
    <!-- custom CSS here -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <style>
        .payment-box {
            width: 700px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 20px;
            background: #fff;
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

        button:hover {
            opacity: 0.9;
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

        .payment-box {
            width: 700px;
            margin: 40px auto;
            border: 1px solid #ccc;
            padding: 20px;
            background: #fff;
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
            display: inline-block;
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
            display: block;
        }

        .payment-options input[type="radio"]:checked + img {
            border: 3px solid #007bff;
            border-radius: 5px;
        }

        .checkout-container {
            display: none;
        }

        .checkout-btn {
            display: inline-block;
            min-width: 180px;
        }

        #square-card-container {
            max-width: 420px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php"><strong>ALICE'S</strong> ELECTRONIC BIKE Shop</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#">Track Order</a></li>
                    <li><a href="#">Login</a></li>
                    <li><a href="#">Signup</a></li>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">24x7 Support <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><strong>Call: </strong>+61-000-000-000</a></li>
                            <li><a href="#"><strong>Mail: </strong>info@alicebikeshop.com</a></li>
                            <li class="divider"></li>
                            <li><a href="#"><strong>Address: </strong>
                                <div>
                                    Melbourne,<br />
                                    VIC 3000, AUSTRALIA
                                </div>
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <form class="navbar-form navbar-right" role="search">
                    <div class="form-group">
                        <input type="text" placeholder="Enter Keyword Here ..." class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

<div class="payment-box" style="font-family: sans-serif;">
    <form id="billing-form" action="billing-page.php" method="post">
        <h2>Billing Address</h2>

        <div class="row">
            <div class="field">
                <label>First Name</label>
                <input type="text" name="firstname" value="<?php echo htmlspecialchars($billing['firstname'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="John" required>
            </div>
        <div class="field">
                <label>Last Name</label>
                <input type="text" name="lastname" value="<?php echo htmlspecialchars($billing['lastname'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Doeskip" required>
            </div>
        </div>

        <div class="row">
            <label>Email</label>
        </div>
        <div class="row">
            <input type="email" name="email" value="<?php echo htmlspecialchars($billing['email'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="you@example.com" required>
        </div>

        <div class="row">
            <label>Address</label>
        </div>
        <div class="row">
            <input type="text" name="address" value="<?php echo htmlspecialchars($billing['address'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="1 Cheeseman Ave" required>
        </div>

        <div class="row">
            <label>Address 2 (Optional)</label>
        </div>
        <div class="row">
            <input type="text" name="address2" value="<?php echo htmlspecialchars($billing['address2'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Apartment or suite">
        </div>

        <div class="row">
            <div class="field">
                <label>City/Suburb</label>
                <input type="text" name="city" value="<?php echo htmlspecialchars($billing['city'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="City/Suburb" required>
            </div>
            <div class="field">
                <label>Country</label>
                <input type="text" name="country" value="<?php echo htmlspecialchars($billing['country'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Country" required>
            </div>
            <div class="field">
                <label>State</label>
                <input type="text" name="state" value="<?php echo htmlspecialchars($billing['state'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="State" required>
            </div>
            <div class="field">
                <label>Zip</label>
                <input type="text" name="zip" value="<?php echo htmlspecialchars($billing['zip'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Zip" required>
            </div>
        </div>

        <button type="submit">Save Details</button>
    </form>

    <br>

    <h3>Total: $<?php echo number_format($cartTotal, 2); ?></h3>
</div>

<div class="payment-box" style="font-family: sans-serif;">
        <h2>Select Payment Option</h2>

        <div class="payment-options">
            <label>
                <input type="radio" name="payment" value="stripe">
                <img src="assets/img/Stripe-Logo.png" alt="Stripe">
            </label>

            <label>
                <input type="radio" name="payment" value="googlepay" id="googlepay">
                <img src="assets/img/googlepay.png" alt="Google Pay">
            </label>

            <label>
                <input type="radio" name="payment" value="paypal" id="paypal">
                <img src="assets/img/paypal.png" alt="PayPal">
            </label>

            <label>
                <input type="radio" name="payment" value="square" id="Square">
                <img src="assets/img/Square-Logo.png" alt="Square">
            </label>
        </div>

        <div class="checkout-container" id="paypal-checkout-container">
            <form action="<?php echo PAYPAL_URL; ?>" method="post" style="padding: 0; margin: 0;" onsubmit="return preparePayPalForm();">
                <input type="hidden" name="cmd" value="_xclick" />
                <input type="hidden" name="business" value="<?php echo PAYPAL_ID; ?>" />
                <input type="hidden" name="item_name" value="Electric Bike Order">
                <input type="hidden" name="item_number" value="ORDER001">
                <input type="hidden" name="amount" value="<?php echo $cartTotal; ?>">
                <input type="hidden" name="currency_code" value="<?php echo PAYPAL_CURRENCY; ?>" />
                <input type="hidden" name="cancel_return" value="<?php echo htmlspecialchars(PAYPAL_RETURN_URL, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="return" value="<?php echo PAYPAL_RETURN_URL; ?>">
                <input type="hidden" name="notify_url" value="<?php echo PAYPAL_NOTIFY_URL; ?>">
                <input type="hidden" name="address_override" value="1">
                <input type="hidden" name="first_name" id="paypal-first-name" value="<?php echo htmlspecialchars($billing['firstname'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="last_name" id="paypal-last-name" value="<?php echo htmlspecialchars($billing['lastname'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="email" id="paypal-email" value="<?php echo htmlspecialchars($billing['email'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="address1" id="paypal-address1" value="<?php echo htmlspecialchars($billing['address'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="address2" id="paypal-address2" value="<?php echo htmlspecialchars($billing['address2'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="city" id="paypal-city" value="<?php echo htmlspecialchars($billing['city'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="state" id="paypal-state" value="<?php echo htmlspecialchars($billing['state'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="zip" id="paypal-zip" value="<?php echo htmlspecialchars($billing['zip'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="country" id="paypal-country" value="<?php echo htmlspecialchars(normalizePaypalCountry($billing['country']), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="custom" value="<?php echo htmlspecialchars(json_encode([
                    'username' => $billing['username'],
                    'address2' => $billing['address2'],
                ], JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="checkout-btn" onclick="return validatePayPalAddress();">CHECKOUT NOW</button>
            </form>
        </div>

        <div class="checkout-container" id="google-checkout-container">
            <button type="button" class="checkout-btn" id="google-pay-btn">CHECKOUT NOW</button>
        </div>

        <div class="checkout-container" id="stripe-checkout-container">
            <button type="button" class="checkout-btn" id="stripe-btn">CHECKOUT NOW</button>
        </div>

        <div class="checkout-container" id="square-checkout-container">
            <button type="button" class="checkout-btn" id="square-checkout-btn">CHECKOUT NOW</button>
        </div>
</div>

<script>
const paymentMethods = document.querySelectorAll('input[name="payment"]');
const paypalContainer = document.getElementById('paypal-checkout-container');
const googleCheckoutContainer = document.getElementById('google-checkout-container');
const stripeContainer = document.getElementById('stripe-checkout-container');
const squareContainer = document.getElementById('square-checkout-container');

function validatePayPalAddress() {
    const requiredFields = [
        document.querySelector('input[name="address"]'),
        document.querySelector('input[name="city"]'),
        document.querySelector('input[name="state"]'),
        document.querySelector('input[name="zip"]'),
        document.querySelector('input[name="country"]')
    ];

    const hasMissing = requiredFields.some(field => !field || !field.value.trim());
    if (hasMissing) {
        alert('Please complete your billing address before paying with PayPal. City, state, zip, and country are required.');
        return false;
    }

    return true;
}

function preparePayPalForm() {
    if (!validatePayPalAddress()) {
        return false;
    }

    const fields = {
        'paypal-first-name': 'firstname',
        'paypal-last-name': 'lastname',
        'paypal-email': 'email',
        'paypal-address1': 'address',
        'paypal-address2': 'address2',
        'paypal-city': 'city',
        'paypal-state': 'state',
        'paypal-zip': 'zip'
    };

    Object.entries(fields).forEach(([paypalId, billingName]) => {
        document.getElementById(paypalId).value = document.querySelector(`[name="${billingName}"]`).value.trim();
    });

    const country = document.querySelector('[name="country"]').value.trim();
    const countryCodes = {
        australia: 'AU', au: 'AU',
        'united states': 'US', usa: 'US', us: 'US',
        'united kingdom': 'GB', uk: 'GB',
        canada: 'CA', 'new zealand': 'NZ', singapore: 'SG', india: 'IN'
    };
    document.getElementById('paypal-country').value = countryCodes[country.toLowerCase()] || country.substring(0, 2).toUpperCase();

    return true;
}

paymentMethods.forEach(method => {
    method.addEventListener('change', function() {
        const selected = this.value;

        paypalContainer.style.display = selected === 'paypal' ? 'block' : 'none';
        googleCheckoutContainer.style.display = selected === 'googlepay' ? 'block' : 'none';
        stripeContainer.style.display = selected === 'stripe' ? 'block' : 'none';
        squareContainer.style.display = selected === 'square' ? 'block' : 'none';
    });
});
</script>

<script>
const cartTotal = "<?php echo $cartTotal; ?>";
</script>

<script src="googlepay.js"></script>
<script async src="https://pay.google.com/gp/p/js/pay.js" onload="onGooglePayLoaded()"></script>

<script>
const googlePayButton = document.getElementById('google-pay-btn');
if (googlePayButton) {
    googlePayButton.addEventListener('click', function () {
        if (typeof onGooglePaymentButtonClicked === 'function') {
            onGooglePaymentButtonClicked();
        }
    });
}

const stripeButton = document.getElementById('stripe-btn');
if (stripeButton) {
    stripeButton.addEventListener('click', function () {
        window.location.href = 'stripe-checkout.php';
    });
}

const squareCheckoutButton = document.getElementById('square-checkout-btn');
if (squareCheckoutButton) {
    squareCheckoutButton.addEventListener('click', function () {
        window.location.href = 'square-checkout.php';
    });
}

</script>

</body>
</html>
