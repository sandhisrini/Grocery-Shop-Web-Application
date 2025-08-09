<?php
session_start();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (!$username) {
        $errors[] = 'Username is required';
    }
    if (!$email) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    if (!$password) {
        $errors[] = 'Password is required';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    if (!$errors) {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already taken';
        }

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered';
        }
    }

    if (!$errors) {
        // Insert user into DB
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['username'] = $username;

        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Register</title>
    <style>
        /* Reset some default browser styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            padding: 40px 50px;
            max-width: 420px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
            text-align: left;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 30px;
            letter-spacing: 1.2px;
        }

        /* Error box */
        .errors {
            background-color: #ffe6e6;
            border-left: 6px solid #ff4d4d;
            color: #b30000;
            padding: 16px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            list-style-type: none;
        }

        form label {
            display: flex;
            flex-direction: column;
            font-weight: 600;
            color: #34495e;
            font-size: 15px;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            margin-top: 8px;
            padding: 14px 16px;
            font-size: 16px;
            border: 1.8px solid #d1d9e6;
            border-radius: 8px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2c3e50;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 10px #4CAF50;
            outline: none;
        }

        button {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 16px 0;
            font-size: 18px;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #4CAF50;
            box-shadow: 0 6px 15px #4CAF50;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            font-size: 15px;
            color: #7f8c8d;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .login-link a {
            color: #4CAF50;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #4CAF50;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .container {
                padding: 30px 25px;
            }

            h2 {
                font-size: 1.8rem;
            }

            button {
                padding: 14px 0;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Register</h2>

        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" novalidate>
            <label>
                Username:
                <input type="text" name="username" required />
            </label>

            <label>
                Email:
                <input type="email" name="email" required />
            </label>

            <label>
                Password:
                <input type="password" name="password" required />
            </label>

            <label>
                Confirm Password:
                <input type="password" name="confirm_password" required />
            </label>

            <button type="submit">Register</button>
        </form>

        <p class="login-link">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>

</html>