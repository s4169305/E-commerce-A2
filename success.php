<?php
session_start();
include 'paypalconfig.php';
require_once __DIR__ . '/cart_helpers.php';

if (!empty($_GET['item_number']) && !empty($_GET['tx']) && !empty($_GET['amt']) && !empty($_GET['cc']) && !empty($_GET['st'])) {
    $item_number = $_GET['item_number'];
    $txn_id = $_GET['tx'];
    $payment_gross = $_GET['amt'];
    $currency_code = $_GET['cc'];
    $payment_status = $_GET['st'];
}

$mastercard = false;
if (isset($_GET['gateway']) && $_GET['gateway'] == 'mastercard' && isset($_SESSION['mastercard_payment'])) {
    $mastercard = true;
    $mc = $_SESSION['mastercard_payment'];
    $txn_id = $mc['transaction_id'];
    $payment_gross = $mc['amount'];
    $payment_status = $mc['status'];
}

$visa = false;
if (isset($_GET['gateway']) && $_GET['gateway'] == 'visa' && isset($_SESSION['visa_payment'])) {
    $visa = true;
    $visaData = $_SESSION['visa_payment'];
    $txn_id = $visaData['transaction_id'];
    $payment_gross = $visaData['amount'];
    $payment_status = $visaData['status'];
}

$googlepay = false;
if (isset($_GET['gateway']) && $_GET['gateway'] == 'googlepay') {
    $googlepay = true;
    $txn_id = 'GP' . time();
    $payment_gross = cart_total();
    $payment_status = 'Completed';
}

$stripe = false;
if (isset($_GET['gateway']) && $_GET['gateway'] == 'stripe') {
    $stripe = true;
    $txn_id = 'STR' . time();
    $payment_gross = cart_total();
    $payment_status = 'Completed';
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Payment Gateway</title>
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
<h1>Payment Info</h1>

<div class="container">
    <div class="status">

        <?php if (!empty($txn_id)) { ?>

        <h2 class="success">Your Payment has been Successful</h2>

        <p><b>Transaction ID:</b> <?php echo $txn_id; ?></p>
        <p><b>Paid Amount:</b> <?php echo $payment_gross; ?> AUD</p>
        <p><b>Payment Status:</b> <?php echo $payment_status; ?></p>
        <p>
            <b>Payment Method:</b>
            <?php if ($mastercard) { ?>
                Mastercard
            <?php } elseif ($visa) { ?>
                Visa
            <?php } elseif ($googlepay) { ?>
                Google Pay
            <?php } else { ?>
                PayPal
            <?php } ?>
        </p>

<br>

    <h3>Product Information</h3>
    <?php
    $total = 0;

    if(isset($_SESSION['cart'])) {
        foreach($_SESSION['cart'] as $item) {
            $itemTotal = $item['price'] * $item['qty'];
            $total += $itemTotal;
    ?>
        <p>
            <b>Product Name:</b>
            <?php echo $item['name']; ?>
            </p>
        <p>
            <b>Quantity:</b>
            <?php echo $item['qty']; ?>
        </p>
        <p>
            <b>Price:</b>
            $<?php echo number_format($itemTotal, 2); ?>
        </p>
    <hr>

    <?php
        }
    }
    ?>

<p>
    <b>Total:</b>
    $<?php echo number_format($total, 2); ?>
</p>

        <?php } else { ?>
            <h1 class="error">Your Payment has Failed</h1>
        <?php } ?>
    </div>
    <a href="index.php" class="btn-link">Back to Products</a>
</div>
</body>
</html>