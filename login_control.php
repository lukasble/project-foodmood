<?php
session_start();

if (empty($_SESSION['user_id'])) {
  $return = urlencode($_SERVER['REQUEST_URI']);
  header("Location: index.php?return=$return");
  exit;
}

require_once __DIR__.'/user_agreement_config.php';

$user_terms_version = $_SESSION['terms_version'] ?? null;

if ($user_terms_version !== TERMS_VERSION_CURRENT) {
  $return = urlencode($_SERVER['REQUEST_URI']);
  header("Location: user_agreement.php?return=$return");
  exit;
}
?>
