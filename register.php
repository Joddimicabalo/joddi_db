<?php
session_start();
require 'db.php';

$errors = [];
$username = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($username)) {
        $errors[] = "Username is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, password)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param("sss", $username, $email, $hashed);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: login.php?registered=1");
            exit;
        } else {
            if ($stmt->errno == 1062) {
                $errors[] = "Username or Email already exists";
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="box">
        <h2>Register</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input
                type="text"
                name="username"
                placeholder="Username"
                value="<?= htmlspecialchars($username) ?>"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email"
                value="<?= htmlspecialchars($email) ?>"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm Password"
                required
            >

            <button type="submit">Register</button>
        </form>

        <p>
            Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>