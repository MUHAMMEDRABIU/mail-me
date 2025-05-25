<?php
session_start();
require __DIR__ . '/config/database.php';

$email = $password = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Store user session as a single array
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email']
                ];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Login failed: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a valid email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Mail-me App</title>
</head>
<body>
  <h2>Login</h2>

  <?php if ($error): ?>
    <p style="color:red;"><?= $error ?></p>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="email" name="email" placeholder="Email Address" value="<?= htmlentities($email) ?>" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
  </form>

  <br>
  <a href="register.php">New user? Register</a>
</body>
</html>
