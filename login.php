<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$username = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = "Both fields are required.";
    } else {
        try {
            $stmt = $conn->prepare(
                "SELECT id, username, password
                 FROM users
                 WHERE username = ?"
            );

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header("Location: dashboard.php");
                exit;
            }

            $errors[] = "Invalid username or password.";

        } catch (mysqli_sql_exception $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = "A database error occurred. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="box">
        <h2>Login</h2>

        <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
            <p class="success">Registration successful! Please log in.</p>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input
                type="text"
                name="username"
                placeholder="Username"
                value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                autocomplete="username"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>