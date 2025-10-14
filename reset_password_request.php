<?php
require 'db.php'; // include your DB connection
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expires = date("U") + 3600; // expires in 1 hour

        // Store token and expiration in database (create columns if not existing)
        $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE email=?");
        $stmt->bind_param("sis", $token, $expires, $email);
        $stmt->execute();

        // Build reset link (change the domain to your actual server)
        $resetLink = "http://localhost/foodmood/reset_password.php?token=" . $token;

        
