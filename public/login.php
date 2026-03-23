<?php
session_start();

if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $valid_username = 'admin';
    $valid_password = 'password123';

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit();
    } else {
        $err = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        /* ===== GENERAL ===== */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f5f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* ===== LOGIN CARD ===== */
        .login {
            background: white;
            padding: 35px 40px;
            border-radius: 12px;
            width: 350px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            border: 1px solid #e4e4e4;
        }

        .login h2 {
            margin-top: 0;
            font-weight: 600;
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        /* ===== INPUT FIELDS ===== */
        .login label {
            font-size: 14px;
            color: #444;
            margin-bottom: 5px;
            display: block;
        }

        .login input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .login input:focus {
            border-color: #3b4cca;
            outline: none;
        }

        /* ===== ERROR MESSAGE ===== */
        .error {
            color: #d63031;
            background: #ffeaea;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ffb4b4;
            font-size: 14px;
        }

        /* ===== BUTTON ===== */
        .login button {
            width: 100%;
            padding: 11px;
            background: #3b4cca;
            color: white;
            font-size: 15px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .login button:hover {
            background: #2f3ea5;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            font-size: 14px;
            color: #2563eb;
            font-weight: 500;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

    <div class="login">

    
        <a href="index.php" class="back-btn">← Back to Home</a>

        <h2>Login</h2>

        <?php if ($err): ?>
            <div class="error"><?= $err ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>

    </div>

</body>
</html>