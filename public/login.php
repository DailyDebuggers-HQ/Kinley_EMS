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
        <link rel="stylesheet" href="/enrollment_system/public/assets/css/login.css">
    </head>
    <body>
        <div class="login">
            <h2>Login</h2>

            <?php if ($err): ?>
                <div class="error"><?= $err ?></p>
            <?php endif; ?>

            <form method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <br><br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <br><br>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
</html>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>