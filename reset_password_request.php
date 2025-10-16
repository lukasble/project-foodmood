<?php
require 'db.php'; // include your DB connection
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expires = date("U") + 3600; // expires in 1 hour

        // Store token and expiration in database (create columns if not existing)
        $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
        $stmt->bind_param("sis", $token, $expires, $email);
        $stmt->execute();

        // Build reset link (change the domain to your actual server)
        $resetLink = "http://localhost/foodmood/reset_password.php?token=" . $token;

        // Send email
        $subject = "Reset your FoodMood password";
        $body = "Click the following link to reset your password: " . $resetLink;
        $headers = "From: no-reply@foodmood.com\r\n";

        mail($email, $subject, $body, $headers);

        $message = "A password reset link has been sent to your email.";
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

        
