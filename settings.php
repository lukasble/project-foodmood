<?php
// settings.php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /index.php'); exit; }

if (!isset($_SESSION['lookback_min'])) {
  $_SESSION['lookback_min'] = 60; // default ON
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $on = isset($_POST['lag_on']) ? 1 : 0;
  $_SESSION['lookback_min'] = $on ? 60 : 0;
  $saved = true;
}
$on = ($_SESSION['lookback_min'] ?? 60) > 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Settings</title>
  <link rel="stylesheet" href="/style.css"> 
</head>
<body class="analysis-bg">
  <div class="analysis-wrap">
    <form id="analysis" method="post">
      <h1>Settings</h1>
      <p>Control the “lag window” used in the analysis. When ON, the frequency analysis
        considers exposures of other meals in the last 60 minutes before a bad experience; when OFF, the frequency analysis doesn’t use a timeframe at all and only considers the manual logged bad experiences.</p>

      <label class="allergen" style="align-items:center;">
        <input type="checkbox" name="lag_on" <?= $on ? 'checked' : '' ?>>
        <span>Use 60-minute lag window</span>
      </label>

      <button type="submit" class="btn" style="margin-top:14px;">Save</button>

      <?php if (!empty($saved)): ?>
        <p style="margin-top:10px;">Saved, current setting: <strong><?= $on ? 'ON (60 min)' : 'OFF (0 min)' ?></strong></p>
      <?php endif; ?>

      <div class="analysis-links" style="margin-top:16px;">
        <a class="btn" href="/home.php">Home</a>
      </div>
    </form>
  </div>
</body>
</html>
