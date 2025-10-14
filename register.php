<?php 
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check password strength
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $error = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Generation of verification token (optional)
        // $token = bin2hex(random_bytes(16));

        $stmt = $link->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now log in.";
        } else {
            $error = "Error: " . $stmt->error;
        }
    } 
} 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodMood - Register</title>
    <link rel="stylesheet" href="register_style.css">
</head>

<body class="register-page">
    <div class="register-container">

      <img src="FoodMood_logo.png" alt="FoodMood Logo" class="logo">
        
      <h2>Create account</h2>
        
      <?php
        if (!empty($error)) echo "<p class='error'>$error</p>";
        if (!empty($success)) echo "<p class='success'>$success</p>";
      ?>
  
      <form action="register.php" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" 
                   placeholder="Enter your password" 
                   pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}"
                   title="Must be at least 8 characters long, include one uppercase letter, one lowercase letter, and one number"
                   required>

            <small class="password-hint">
                Password must be at least 8 characters, include an uppercase letter, a lowercase letter, and a number.
            </small>
          
            <button type="submit" name="register">Create account</button>
      </form>

      <p>Already have an account?</p>
      <a href="index.php"><button type="button">Back to Login</button></a>

    </div>
    
</body>
</html>
