<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo '<p class="msg msg--error">You must be logged in.</p>';
  exit;
}
?>