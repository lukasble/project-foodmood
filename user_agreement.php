<?php
session_start();
$isLoggedIn = !empty($_SESSION['user_id']); 

require_once __DIR__.'/user_agreement_config.php';

$version = $_GET['v'] ?? TERMS_VERSION_CURRENT;
$file = termsFile($version);

if (!is_file($file)) {
  http_response_code(404);
  exit('Requested terms version not found.');
}

$agreementHtml = file_get_contents($file);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodMood: User agreement</title>
  <link rel="stylesheet" href="index_style.css">
  <style> 
    .agreement-container {
        background: #ffffff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        text-align: left;
        width: 800px;
    }
    .center {
        display: flex;
        justify-content: center;
        align-items: center;
    }
  </style>
</head>

<body>
<div class="agreement-container"> 
  <main class="terms-container">
      <strong>Version:</strong> <?= htmlspecialchars($version) ?>
    <?= $agreementHtml ?>
  </main>
  <div class="center">
    <?php if ($isLoggedIn): ?>
      <a href="home.php"><button type="button">Back to home</button></a>
    <?php else: ?>
      <a href="register.php"><button type="button">Back to register</button></a>
    <?php endif; ?>
  </div>
</div>
</body>

