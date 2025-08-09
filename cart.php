<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $product_id => $qty) {
        $qty = max(1, (int)$qty);
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: cart.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$products_in_cart = [];
$total = 0.0;

if ($cart) {
    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($cart));
    $products_in_cart = $stmt->fetchAll();

    foreach ($products_in_cart as &$product) {
        $product['quantity'] = $cart[$product['id']];
        $product['subtotal'] = $product['quantity'] * $product['price'];
        $total += $product['subtotal'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
                Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            margin: 20px;
            min-height: 100vh;
            color: #102a43;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h2 {
            font-weight: 700;
            font-size: 2.4rem;
            text-align: center;
            margin-bottom: 30px;
            color: #334e68;
            letter-spacing: 1.2px;
            user-select: none;
        }

        table {
            width: 90%;
            max-width: 900px;
            margin: 0 auto 40px;
            border-collapse: separate;
            border-spacing: 0 8px;
            background: transparent;
            box-shadow: none;
        }

        thead {
            background-color: #243b55;
            color: white;
            border-radius: 12px;
            user-select: none;
        }

        thead th {
            padding: 14px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 1.1rem;
        }

        tbody tr {
            background: white;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.07);
            border-radius: 14px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        tbody tr:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.15);
        }

        tbody td {
            padding: 16px 20px;
            border-bottom: none;
            font-size: 1rem;
            color: #334e68;
        }

        input[type="number"] {
            width: 60px;
            padding: 8px 10px;
            border-radius: 12px;
            border: 2px solid #4CAF50;
            font-weight: 600;
            color: #4CAF50;
            font-size: 1rem;
            text-align: center;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="number"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 8px #4CAF50;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .remove-link {
            color: #4CAF50;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s ease;
            user-select: none;
        }

        .remove-link:hover {
            color: #4CAF50;
            text-decoration: underline;
        }

        .update-button,
        .checkout-button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin: 20px auto 0;
            display: block;
            box-shadow: 0 8px 20pxrgb(66, 152, 69);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
            text-align: center;
            max-width: 220px;
        }

        .checkout-button {
            background-color: #3498db;
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.6);
            margin-top: 10px;
        }

        .update-button:hover,
        .checkout-button:hover,
        .update-button:focus,
        .checkout-button:focus {
            background-color: #4CAF50;
            box-shadow: 0 10px 28px #4CAF50;
            outline: none;
        }

        .link-section {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 600;
            font-size: 1rem;
            color: #334e68;
            user-select: none;
        }

        .link-section a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 700;
            margin: 0 8px;
            transition: color 0.3s ease;
        }

        .link-section a:hover,
        .link-section a:focus {
            color: #4CAF50;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h2>Your Cart</h2>

    <?php if ($products_in_cart): ?>
        <form method="post" action="cart.php">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products_in_cart as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td>
                                <input type="number" name="quantities[<?= $p['id'] ?>]" value="<?= $p['quantity'] ?>" min="1" />
                            </td>
                            <td>₹<?= number_format($p['price'], 2) ?></td>
                            <td>₹<?= number_format($p['subtotal'], 2) ?></td>
                            <td>
                                <a class="remove-link" href="cart.php?remove=<?= $p['id'] ?>" onclick="return confirm('Remove this item?')">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
                        <td colspan="2"><strong>₹<?= number_format($total, 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <button type="submit" class="update-button">Update Cart</button>
        </form>

        <div class="link-section">
            <a href="index.php">← Add More Products</a> |
            <a href="checkout.php">Proceed to Checkout →</a>
        </div>
    <?php else: ?>
        <p style="text-align:center;">Your cart is empty. <a href="index.php">Start shopping</a></p>
    <?php endif; ?>
</body>

</html>