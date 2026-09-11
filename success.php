<?php
// Include configuration file	
session_start();
include 'paypalconfig.php';

// If transaction data is available in the URL 
if(!empty($_GET['item_number']) && !empty($_GET['tx']) 
&& !empty($_GET['amt']) && !empty($_GET['cc']) 
&& !empty($_GET['st'])){ 
		// Get transaction information from URL 
		$item_number = $_GET['item_number'];  
		$txn_id = $_GET['tx']; 
		$payment_gross = $_GET['amt']; 
		$currency_code = $_GET['cc']; 
		$payment_status = $_GET['st']; 
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Payment Gateway</title>
</head>
<body>
<h1>Payment Info</h1>

<div class="container">
    <div class="status">

        <?php if (!empty($txn_id)) { ?>

        <h2 class="success">Your Payment has been Successful</h2>

    
        <p><b>Transaction ID:</b> <?php echo $txn_id; ?></p>
        <p><b>Paid Amount:</b> <?php echo $payment_gross; ?> AUD</p>
        <p><b>Payment Status:</b> <?php echo $payment_status; ?></p>

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