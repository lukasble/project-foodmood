<?php

// -------------------
// PHPMailer included
// -------------------
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'db.php'; 
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $link->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expires = date("U") + 3600; // expires in 1 hour

        // Store token and expiration in database
        $stmt = $link->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
        $stmt->bind_param("sis", $token, $expires, $email);
        $stmt->execute();

        // Build reset link
        $resetLink = "http://localhost/reset_password.php?token=" . $token;

        // -------------------
        // Send email using PHPMailer
        // -------------------
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'foodmoodweb@gmail.com'; 
            $mail->Password   = 'vudxpvvwbrlgyugy'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('foodmoodweb@gmail.com', 'FoodMood');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset your FoodMood password';
            $mail->Body    = "Click the following link to reset your password: <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            $message = "A password reset link has been sent to your email.";

        } catch (Exception $e) {
            $message = "Could not send email. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        $message = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    
<head>
  <meta charset="UTF-8">
  <title>Reset Password - FoodMood</title>
  <link rel="stylesheet" href="index_style.css">
</head>
    
<body>
    
  <div class="register-container">
    <h2>Reset Password</h2>
    <p><?php echo $message; ?></p>
      
    <form method="post">
      <input type="email" name="email" placeholder="Enter your email" required>
      <button type="submit">Send reset link</button>
    </form>
      
  </div>

</body>
    
</html>

        
