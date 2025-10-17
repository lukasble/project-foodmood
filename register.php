<?php 
include 'db.php';
session_start();

require_once __DIR__.'/user_agreement_config.php';

$rendered_terms_version = TERMS_VERSION_CURRENT;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $posted_version = $_POST['terms_version_shown'] ?? null;

    // Password validation
    $passwordPattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/";

    if (!preg_match($passwordPattern, $password)) {
        $error = "Password must be at least 8 characters long, include one lowercase letter, one uppercase letter, and one number.";
    } else {
    
        // Hashed password 
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $link->prepare("INSERT INTO users (email, password_hash, terms_accepted_at, terms_version) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $hashedPassword, $terms_accepted, $terms_version);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now log in.";
        } else {
            $error = "Error: " . $stmt->error;
        }

    if ($posted_version !== TERMS_VERSION_CURRENT) {
        $error = "The user agreement has been updated. Please review the latest version before continuing.";
    }  

    else {
        $terms_accepted_at = gmdate('Y-m-d H:i:s');

        // Hashed password 
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Generation of verification token
        // DELETE?? $token = bin2hex(random_bytes(16));

        $stmt = $link->prepare("INSERT INTO users (email, password_hash, terms_accepted_at, terms_version) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $hashedPassword, $terms_accepted_at, $posted_version);

        if ($stmt->execute()) {
            $success = "Registration successful! You can now log in.";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
        $link->close();
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
    <style>
        .accept-terms {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 8px 0 16px;
        }
        .accept-terms input[type="checkbox"] {
            width: auto;
            margin: 0;
            flex: 0 0 auto;
        }
        .accept-terms label { margin: 0; }
        .accept-terms a { text-decoration: underline; }
        .accept-terms input[type="checkbox"]{
            accent-color:#ff7b00;
            transform:scale(1.3);
            transform-origin:center;
            vertical-align:middle;
        }

        input[type="password"] {
            position: relative;
        }

        input[type="password"][title]:hover {
            cursor: help;
        }
        
    </style>
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

            <!-- Tooltip text via title attribute -->
            <input type="password" 
                   name="password" 
                   placeholder="Enter your password" 
                   required
                   title="Password must be minimum 8 characters, one lowercase, one uppercase, and one number.">

            <input type="hidden" name="terms_version_shown" value="<?php echo htmlspecialchars($rendered_terms_version); ?>">

            <div class="accept-terms">
            <input type="checkbox" id="accept_terms" name="accept_terms" value="1" required>
            <label for="accept_terms"> 
                I accept the <a href="user_agreement.php?v=<?php echo urlencode($rendered_terms_version); ?>"
                target="_blank" rel="noopener">terms of service</a>
            </label>
            </div>

            <button type="submit" name="register">Create account</button>
        </form>

        <p>Already have an account?</p>
        <a href="index.php"><button type="button">Back to Login</button></a>

    </div>
    
</body>
</html>
