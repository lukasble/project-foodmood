<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FoodMood: Home</title>
  <link rel="stylesheet" href="style_home.css">
</head>

<body>
  <div class="login-container">
      <a href="entry.php" class="btn">Add entry</a><br>
      <!-- <a href="experience.php" class="btn">Add experience</a><br> --->
      <a href="meal_log.php" class="btn">Show meal log</a><br>
  </div>

  <?php
    if (session_status() === PHP_SESSION_NONE) { 
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        echo '<a href="logout.php" class="logout-btn">Log out</a>';
    }
    ?>
  
</body> 
