<?php
require __DIR__ . '/config/database.php';

$name = $email = $password = $success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = htmlspecialchars(trim($_POST['name']));
    $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    if (!empty($name) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = "User with this email already exists.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPassword]);

                $success = "Registration successful!";
                $name = $email = $password = ""; // Clear form values
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill out all fields with valid information.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Registration - Mail-me App</title>
</head>
<body>
  <h2>Register</h2>

  <?php if ($success): ?>
    <p style="color:green;"><?= $success ?></p>
  <?php elseif ($error): ?>
    <p style="color:red;"><?= $error ?></p>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="text" name="name" placeholder="Full Name" value="<?= htmlentities($name) ?>" required><br><br>
    <input type="email" name="email" placeholder="Email Address" value="<?= htmlentities($email) ?>" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Register</button>
  </form>

  <br>
  <a href="login.php">Login</a>
</body>
</html>
