<?php
include 'db.php';
session_start();

$token = $_GET['token'] ?? '';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $token = $_POST['token'];

    if ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $message = "Password must be at least 8 characters, include an uppercase, a lowercase, and a number.";
    } else {
        // Verify token validity
        $stmt = $link->prepare("SELECT * FROM users WHERE reset_token=? AND reset_expires > ?");
        $now = date("U");
        $stmt->bind_param("si", $token, $now);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Update new password and clear token
            $stmt = $link->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE email=?");
            $stmt->bind_param("ss", $hashedPassword, $user['email']);
            $stmt->execute();

            $message = "Password successfully reset! You can now <a href='index.php'>login</a>.";
        } else {
            $message = "Invalid or expired reset link.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  
<head>
  <meta charset="UTF-8">
  <title>Choose New Password - FoodMood</title>
  <link rel="stylesheet" href="style.css">
</head>
  
<body>
  <div class="register-container">
    <h2>Set a New Password</h2>
    <p><?php echo $message; ?></p>

    <?php if (!$_POST) { ?>
                        
    <form method="post">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
      <input type="password" name="password" placeholder="Enter new password" required>
      <input type="password" name="confirm" placeholder="Confirm new password" required>
      <button type="submit">Reset Password</button>
    </form>
      
    <?php } ?>
  </div>
  
</body>
  
</html>
