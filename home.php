<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo '<p class="msg msg--error">You must be logged in.</p>';
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodMood: Home</title>
  <link rel="stylesheet" href="index_style.css">
</head>

<body>
  <div class="login-container">
      <img src="FoodMood_logo.png" alt="FoodMood Logo" class="logo" />

      <h2>Welcome!</h2>

      <a href="entry.php"><button type="button">Add entry</button></a> <br>
      <a href="meal_log.php"><button type="button">Show meal log</button></a> <br>

      <hr class="rounded">

      <a href="user_agreement.php"><button type="button">View user agreement</button></a> <br>     
  </div>

  <?php
    if (session_status() === PHP_SESSION_NONE) { 
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        echo '<a href="logout.php"><button type="button" class="logout-btn">Log out</button></a>';
    }
    ?>
  
</body> 
