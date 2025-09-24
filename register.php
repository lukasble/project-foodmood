<?php 
require 'db.php';
session_start();

if (isset($_POST['login']));  // Note to check: that the 'login' buttom is called 'login'
  $email = $_POST['email'];
  $password = $_POST['password'];

// Delete later, hashed password 
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Deleter later, generation of verification token
$token = bin2hex(random_bytes(16));


  

?> 
