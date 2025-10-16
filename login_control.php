<?php
session_start();

if (empty($_SESSION['user_id'])) {
  $return = urlencode($_SERVER['REQUEST_URI']);
  header("Location: index.php?return=$return");
  exit;
}
?>
