<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$id = (int)$_GET['id'];

// Fetch product to edit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: admin_dashboard.php");
    exit;
}

$errors = [];
$name = $product['name'];
$description = $product['description'];
$price = $product['price'];
$image = $product['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');

    // Image upload handling
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $uploadDir = 'images/';
        $destPath = $uploadDir . $fileName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = "Only JPG, PNG, GIF images are allowed.";
        }

        if (empty($errors)) {
            if (!move_uploaded_file($fileTmpPath, $destPath)) {
                $errors[] = "Error uploading the image.";
            } else {
                $image = $fileName;
            }
        }
    }

    // Validate inputs
    if (!$name) $errors[] = "Product name is required.";
    if (!$description) $errors[] = "Description is required.";
    if (!$price || !is_numeric($price)) $errors[] = "Valid price is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $image, $id]);

        header("Location: admin_dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
            max-width: 500px;
            margin: auto;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type=text],
        input[type=number],
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type=file] {
            margin-bottom: 15px;
        }

        img.current-image {
            display: block;
            margin-bottom: 15px;
            max-width: 150px;
            border-radius: 6px;
            object-fit: cover;
        }

        button {
            background-color: #27ae60;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #1e8449;
        }

        .errors {
            background-color: #ffe6e6;
            color: #b30000;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            list-style-type: none;
        }
    </style>
</head>

<body>
    <h2>Edit Product</h2>

    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
        <label>Name:
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
        </label>

        <label>Description:
            <textarea name="description" rows="4" required><?= htmlspecialchars($description) ?></textarea>
        </label>

        <label>Price:
            <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>" required>
        </label>

        <label>Current Image:</label>
        <img src="images/<?= htmlspecialchars($image) ?>" alt="Current Image" class="current-image">

        <label>Change Image:
            <input type="file" name="image" accept="image/*">
        </label>

        <button type="submit">Update Product</button>
    </form>
</body>

</html>