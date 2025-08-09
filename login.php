<?php
session_start();
require 'db.php'; // DB connection

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];      // <--- Added this
        $_SESSION['username'] = $username;

        // Redirect based on user role (simple admin check)
        if ($user['username'] === 'admin') {
            header("Location: admin_dashboard.php");
            exit();
        } else {
            header("Location: index.php");
            exit();
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <style>
        /* Reset */
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

        .error {
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

        /* Signup link */
        .signup-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 15px;
            color: #34495e;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .signup-link a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 700;
            margin-left: 6px;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
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
        <h2>Login</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <label>
                Username:
                <input type="text" name="username" placeholder="Enter username" required />
            </label>

            <label>
                Password:
                <input type="password" name="password" placeholder="Enter password" required />
            </label>

            <button type="submit">Login</button>
        </form>

        <span class="signup-link">
            Don't have an account?
            <a href="register.php">Sign Up</a>
        </span>
    </div>
</body>

</html>