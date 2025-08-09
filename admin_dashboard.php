<?php
session_start();
require 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all users
$stmt = $pdo->query("SELECT id, username, email FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all orders
$stmt = $pdo->query("
    SELECT orders.id, users.username, orders.total_price, orders.created_at
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin Dashboard</title>
    <style>
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
            padding: 30px 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 35px 40px;
            border-radius: 18px;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.07);
        }

        h1 {
            font-weight: 700;
            font-size: 2.8rem;
            text-align: center;
            margin-bottom: 40px;
            color: #102a43;
        }

        h2 {
            font-weight: 600;
            font-size: 1.8rem;
            margin-top: 50px;
            margin-bottom: 24px;
            color: #334e68;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            margin-bottom: 40px;
        }

        thead tr th {
            background-color: #4CAF50;
            color: white;
            font-weight: 600;
            padding: 14px 18px;
            text-align: left;
            border-radius: 12px 12px 0 0;
        }

        tbody tr {
            background: white;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.3s ease;
            border-radius: 12px;
        }

        tbody tr:hover {
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.12);
        }

        tbody tr td {
            padding: 12px 18px;
            vertical-align: middle;
        }

        tbody tr td:first-child {
            border-radius: 12px 0 0 12px;
        }

        tbody tr td:last-child {
            border-radius: 0 12px 12px 0;
        }

        img {
            max-width: 50px;
            height: auto;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 6px 14px rgba(255, 127, 80, 0.15);
        }

        a.button {
            background: #4CAF50;
            color: white;
            padding: 8px 18px;
            border-radius: 18px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            box-shadow: 0 6px 14px rgba(76, 175, 80, 0.5);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
            display: inline-block;
            margin-right: 8px;
        }

        a.button:hover {
            background-color: #43a047;
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.7);
        }

        a.delete {
            background: #e53935;
            box-shadow: 0 6px 14px rgba(229, 57, 53, 0.5);
        }

        a.delete:hover {
            background-color: #b71c1c;
            box-shadow: 0 8px 20px rgba(183, 28, 28, 0.7);
        }

        .logout-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #e53935;
            color: white;
            padding: 12px 22px;
            border-radius: 30px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 6px 14px rgba(229, 57, 53, 0.6);
            transition: background-color 0.3s ease;
            user-select: none;
            z-index: 1000;
        }

        .logout-link:hover {
            background: #b71c1c;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }

            h1 {
                font-size: 2.2rem;
            }

            h2 {
                font-size: 1.4rem;
                margin-top: 40px;
            }

            thead tr th,
            tbody tr td {
                padding: 10px 12px;
            }

            img {
                max-width: 40px;
            }

            a.button {
                padding: 6px 14px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <a href="logout.php" class="logout-link">Logout</a>

    <div class="container">
        <h1>Welcome, Admin</h1>

        <h2>Manage Products</h2>
        <a class="button" href="add_product.php">Add Product</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['description']) ?></td>
                        <td><img src="images/<?= htmlspecialchars($p['image']) ?>" alt="Product"></td>
                        <td>
                            <a class="button" href="edit_product.php?id=<?= $p['id'] ?>">Edit</a>
                            <a class="button delete" href="delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete product?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Manage Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>View Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total Price</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?= $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['username']) ?></td>
                        <td>₹<?= number_format($o['total_price'], 2) ?></td>
                        <td><?= $o['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>