<?php
// index.php - login page
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $link->prepare("SELECT id, password_hash, terms_version FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password_hash'])) {
            // Save user session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $email;
            $_SESSION['terms_version'] = $row['terms_version'];
            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }

    $stmt->close();
}
$link->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodMood - Login</title>
    <link rel="stylesheet" href="index_style.css">
</head>

<body>
    <div class="login-container">

        <img src="FoodMood_logo.png" alt="FoodMood Logo" class="logo" />
        
        <h2>Login</h2>
        
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

        <form action="index.php" method="post">
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit" name="login">Login</button>
        </form>

        <p><a href="reset_password_request.php">Forgot your password?</a></p>

        <p>Don't have an account?</p>
        <a href="register.php"><button type="button">Create account</button></a>
    </div>  
</body>
</html>
