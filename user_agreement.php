<?php
session_start();

$TERMS_VERSION = '1.0';
$LAST_UPDATED = '2025-10-14'

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
  </style>
</head>

<body>
<div class="agreement-container">
    <h1>User Agreement (v{$TERMS_VERSION})</h1>
    <p><em>Last updated: {$LAST_UPDATED}</em></p>
    <h2>Instroduction</h2>
    <p>text</p>
    <h2>Sensetive data</h2>
    <p>text</p>
    <h2>Your responsibilities</h2>
    <p>text</p>
    <h2>Data usage</h2>
    <p>text</p>
    <h2>Disclaimer</h2>
    <p>text</p>
    <h2>Changes</h2>
    <p>text</p>

    <a href="home.php"><button type="button">Back to home</button></a>

</div>
</body>

