<?php
session_start();
require 'db.php';

// Search
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
    $stmt->execute(['%' . $search . '%']);
} else {
    $stmt = $pdo->query("SELECT * FROM products");
}
$products = $stmt->fetchAll();

// Check if current user is admin
$is_admin = isset($_SESSION['username']) && $_SESSION['username'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Grocery Store</title>
    <style>
        /* Import a modern font */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
                Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
            margin: 0;
            min-height: 100vh;
            color: #102a43;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        header {
            background: #243b55;
            color: #f0f4f8;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(36, 59, 85, 0.3);
            position: sticky;
            top: 0;
            z-index: 10;
            transition: background-color 0.3s ease;
        }

        header:hover {
            background-color: #1c2f44;
        }

        header h1 {
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: 1.5px;
            user-select: none;
        }

        nav a {
            color: #f0f4f8;
            margin-left: 20px;
            font-weight: 600;
            font-size: 1rem;
            position: relative;
            padding-bottom: 4px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        nav a::after {
            content: '';
            position: absolute;
            width: 0%;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #4CAF50;
            transition: width 0.3s ease;
        }

        nav a:hover,
        nav a:focus {
            color: #4CAF50;
        }

        nav a:hover::after,
        nav a:focus::after {
            width: 100%;
        }

        main {
            max-width: 1200px;
            margin: 40px auto 60px;
            padding: 0 30px;
        }

        h2 {
            font-weight: 700;
            font-size: 2.4rem;
            text-align: center;
            margin-bottom: 30px;
            color: #334e68;
            letter-spacing: 1.2px;
        }

        form[method="get"] {
            max-width: 450px;
            margin: 0 auto 40px;
            display: flex;
            gap: 12px;
            box-shadow: 0 4px 14px rgba(102, 126, 234, 0.15);
            border-radius: 30px;
            background: white;
            padding: 6px 15px;
            transition: box-shadow 0.3s ease;
        }

        form[method="get"]:focus-within {
            box-shadow: 0 4px 20px #4CAF50;
        }

        form input[type="text"] {
            flex-grow: 1;
            border: none;
            outline: none;
            font-size: 1rem;
            padding: 12px 20px;
            border-radius: 30px;
            color: #334e68;
            font-weight: 500;
            transition: box-shadow 0.3s ease;
        }

        form input[type="text"]:focus {
            box-shadow: 0 0 8px #4CAF50;
        }

        form button {
            background: #4CAF50;
            border: none;
            border-radius: 30px;
            padding: 12px 28px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 12px #4CAF50;
        }

        form button:hover,
        form button:focus {
            background-color: #4CAF50;
            box-shadow: 0 8px 20px #4CAF50;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 30px;
        }

        .product {
            background: white;
            border-radius: 18px;
            padding: 24px 20px 36px;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.07);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: default;
            display: flex;
            flex-direction: column;
            align-items: center;
            user-select: none;
        }

        .product:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.15);
        }

        .product img {
            max-width: 180px;
            height: 160px;
            object-fit: contain;
            border-radius: 12px;
            margin-bottom: 18px;
            filter: drop-shadow(0 3px 6px #4CAF50);
            transition: filter 0.3s ease;
        }

        .product:hover img {
            filter: drop-shadow(0 6px 14px #4CAF50);
        }

        .product h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #102a43;
            margin-bottom: 6px;
            min-height: 48px;
            text-align: center;
        }

        .product p {
            font-size: 1.15rem;
            font-weight: 600;
            color: #4CAF50;
            margin-bottom: 16px;
        }

        .admin-actions,
        form[action="add_to_cart.php"] {
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: auto;
        }

        .admin-actions a {
            background: #4CAF50;
            padding: 8px 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            color: white;
            box-shadow: 0 6px 14px #4CAF50;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }

        .admin-actions a.delete {
            background: #4CAF50;
            box-shadow: 0 6px 14px #4CAF50;
        }

        .admin-actions a:hover {
            background-color: #4CAF50;
            box-shadow: 0 8px 18px #4CAF50;
        }

        .admin-actions a.delete:hover {
            background-color: #4CAF50;
            box-shadow: 0 8px 18px #4CAF50;
        }

        form[action="add_to_cart.php"] input[type="number"] {
            width: 65px;
            border: 2px solid #4CAF50;
            border-radius: 14px;
            padding: 6px 8px;
            font-weight: 700;
            color: #4CAF50;
            font-size: 1rem;
            text-align: center;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        form[action="add_to_cart.php"] input[type="number"]:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 8px #4CAF50;
        }

        form[action="add_to_cart.php"] button {
            background: #4CAF50;
            border: none;
            border-radius: 14px;
            padding: 8px 20px;
            color: white;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 14pxrgb(59, 135, 62);
        }

        form[action="add_to_cart.php"] button:hover,
        form[action="add_to_cart.php"] button:focus {
            background-color: #4CAF50;
            box-shadow: 0 8px 20px #4CAF50;
        }

        p[style*="text-align:center"] {
            color: #627d98;
            font-weight: 600;
            font-size: 1.2rem;
            user-select: none;
            padding: 40px 0;
        }
    </style>
</head>

<body>
    <header>
        <h1>Grocery Store</h1>
        <nav>
            <?php if (isset($_SESSION['username'])): ?>
                <?php if ($is_admin): ?>
                    <a href="manage_users.php">Manage Users</a>
                    <a href="manage_orders.php">Manage Purchases</a>
                    <a href="add_product.php">Add Product</a>
                <?php else: ?>
                    <a href="cart.php">Cart</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="register.php">Register</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h2 style="text-align:center;">Products</h2>
        <form method="get" action="index.php" style="max-width:400px;margin:0 auto 20px;display:flex;gap:10px;">
            <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <div class="products">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                    <div class="product">
                        <img src="images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <p>₹<?= number_format($p['price'], 2) ?></p>

                        <?php if ($is_admin): ?>
                            <div class="admin-actions">
                                <a href="edit_product.php?id=<?= $p['id'] ?>">Edit</a>
                                <a href="delete_product.php?id=<?= $p['id'] ?>" class="delete" onclick="return confirm('Delete this product?');">Delete</a>
                            </div>
                        <?php else: ?>
                            <form action="add_to_cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="number" name="quantity" value="1" min="1">
                                <button type="submit">Add to Cart</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">No products found.</p>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>