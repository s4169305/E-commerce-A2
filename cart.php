<?php
session_start();
require_once __DIR__ . '/cart-helpers.php';

// Adding items to the cart
if (isset($_POST['add_to_cart'])) {
    $name = trim((string)($_POST['name'] ?? ''));
    $price = (float)($_POST['price'] ?? 0);
    $image = trim((string)($_POST['image'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));

    if ($name !== '') {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as $index => $item) {
            if (($item['name'] ?? '') === $name) {
                $_SESSION['cart'][$index]['qty'] = (int)($item['qty'] ?? 1) + 1;
                $_SESSION['cart'][$index]['price'] = $price;
                $_SESSION['cart'][$index]['image'] = $image;
                $_SESSION['cart'][$index]['description'] = $description;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'name' => $name,
                'price' => $price,
                'image' => $image,
                'description' => $description,
                'qty' => 1,
            ];
        }
    }

    header('Location: cart.php');
    exit();
}

// Removing items from the cart
if (isset($_POST['remove_index'])) {
    $index = (int)($_POST['remove_index'] ?? -1);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: cart.php');
    exit();
}

// Updating item quantity in the cart
if (isset($_POST['update_qty'])) {
    $index = (int)($_POST['update_index'] ?? -1);
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['qty'] = $qty;
    }

    header('Location: cart.php');
    exit();
}

// Getting cart items and total price
$cartItems = cart_items();
$cartTotal = cart_total();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
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


<form method="post">
<div class="cart-container">
    <div class="cart-header" style="margin-left: 20px;">
        <h1>Shopping Cart</h1>
        <div>Product</div>
        <div class="description">Product Description</div>
        <div class="qty">Qty</div>
        <div class="price">
            Price
        </div>
        <div class="total">
            Total
        </div>
    </div>

<?php
if(isset($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $index => $item) {
?>
<div class="cart-item">
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

<form action="billing-page.php" method="post">
    <button type="submit" class="checkout-btn">
        Checkout
    </button>
</form>

</div>

</html>
