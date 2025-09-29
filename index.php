<?php
// index.php - login page
session(start);
include: 'db.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodMood - Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="login-container">
        <h2>/Login</h2>
        <form action="index.php" method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <buttom type="submit" name="login">/Login</buttom>
        </form>

    // Create account button
        <p>Don't have an account?</p>
        <a href="register.php"><button type="button" class="create-account-btn">/Create account</button>button></a>
    </div>  
</body>
</html>
