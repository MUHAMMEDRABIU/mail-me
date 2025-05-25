<?php 
session_start();
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user       = $_SESSION['user'];
$sender_id  = $user['id'];
$senderName = $user['name'];
$senderEmail = $user['email'];

$to_email = trim($_POST['to_email'] ?? '');
$subject  = trim($_POST['subject'] ?? '');
$message  = trim($_POST['message'] ?? '');
$feedback = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (filter_var($to_email, FILTER_VALIDATE_EMAIL) && !empty($subject) && !empty($message)) {
        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'];

            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($to_email);

            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();

            // Insert into DB
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_email, subject, body) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sender_id, $to_email, $subject, $message]);

            $feedback = "Message sent successfully to $to_email!";
        } catch (Exception $e) {
            $feedback = "Error: {$mail->ErrorInfo}";
        }
    } else {
        $feedback = "Please enter a valid recipient email, subject, and message.";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
  <title>Send Mail - Mail-me</title>
</head>
<body>
  <h2>Send Mail as <span style="color: green;"><?= htmlentities($user['email']) ?></span></h2>

  <?php if (!empty($feedback)): ?>
    <p style="color:<?= str_contains($feedback, 'success') ? 'green' : 'red' ?>;">
      <?= htmlentities($feedback) ?>
    </p>
  <?php endif; ?>

  <form method="POST" action="">
  <input type="email" name="to_email" placeholder="Recipient Email" required><br><br>
  <input type="text" name="subject" placeholder="Subject" required><br><br>
  <textarea name="message" rows="5" placeholder="Your message..." required></textarea><br><br>
  <button type="submit">Send Mail</button>
</form>


  <br>
  <a href="logout.php">Back to Home</a>
</body>
</html>
