<?php 
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hashed password 
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Generation of verification token
    // DELETE?? $token = bin2hex(random_bytes(16));

    $stmt = $link->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashedPassword);

    if (stmt->execute()) {
        $success = "Registering successful! You can now log in.";
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
    $link->close();

}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodMood - Register</title>
    <link rel="stylesheet" href="style_home.css">
</head>

<body>
    <div class="container">

      <img src="FoodMood_logo.png" alt="FoodMood Logo" class="logo">
        
      <h2>/Create account</h2>
      <?php
        if (!empty($error)) echo "<p class='error'>$error</p>";
        if (!empty($success)) echo "<p class='success'>$success</p>";
      ?>
    
  
      <form action="register.php" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <button type="submit" name="register">Create account</button>
      </form>

      <p>Already have an account?</p>
      <a href="index.php"><button type="button" class="btn">Back to Login</button></a>
    </div>

</body>
</html>
