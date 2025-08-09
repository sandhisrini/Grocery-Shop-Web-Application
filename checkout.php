<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
// Remove product from cart if requested
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
        header("Location: checkout.php");
        exit;
    }
}

// Get cart from session
$cart = $_SESSION['cart'] ?? [];

// If cart is empty, redirect to cart page
if (!$cart) {
    header('Location: cart.php');
    exit;
}

$errors = [];
$success = false;
$products_in_cart = [];
$total_price = 0;

// Fetch product details for products in cart
if ($cart) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $products_in_cart = $stmt->fetchAll();

    // Calculate subtotal and total
    foreach ($products_in_cart as &$product) {
        $product['quantity'] = $cart[$product['id']];
        $product['subtotal'] = $product['quantity'] * $product['price'];
        $total_price += $product['subtotal'];
    }
}

// Process order on form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Insert order record
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $total_price]);
        $order_id = $pdo->lastInsertId();

        // Insert each order item
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($products_in_cart as $p) {
            $stmt_item->execute([$order_id, $p['id'], $p['quantity'], $p['price']]);
        }

        $pdo->commit();

        // Clear cart session
        unset($_SESSION['cart']);
        $success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = "Failed to place order: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout - Grocery Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: center;
        }

        table th {
            background-color: #4CAF50;
            color: white;
            font-weight: 600;
        }

        .remove-link {
            color: #d9534f;
            text-decoration: none;
            font-weight: bold;
        }

        .remove-link:hover {
            text-decoration: underline;
        }

        .total {
            text-align: right;
            font-size: 1.2rem;
            font-weight: 700;
            color: #4CAF50;
            margin-bottom: 25px;
        }

        .checkout-form button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 14px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
            transition: background-color 0.3s ease;
        }

        .checkout-form button:hover {
            background-color: #45a049;
        }

        .success-message {
            text-align: center;
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .errors {
            background-color: #f2dede;
            color: #a94442;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .back-link {
            text-align: center;
        }

        .back-link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Checkout</h2>

        <?php if ($success): ?>
            <div class="success-message">
                ✅ Order placed successfully!<br>
                <a href="index.php">Continue Shopping</a>
            </div>
        <?php else: ?>
            <?php if ($errors): ?>
                <ul class="errors">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price (₹)</th>
                        <th>Subtotal (₹)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products_in_cart as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= $p['quantity'] ?></td>
                            <td><?= number_format($p['price'], 2) ?></td>
                            <td><?= number_format($p['subtotal'], 2) ?></td>
                            <td><a href="checkout.php?remove=<?= $p['id'] ?>" class="remove-link" onclick="return confirm('Are you sure you want to remove this item?');">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="total">Total Amount: ₹<?= number_format($total_price, 2) ?></p>

            <form method="post" class="checkout-form">
                <button type="submit">Place Order</button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="cart.php">← Back to Cart</a>
        </div>
    </div>
</body>

</html>