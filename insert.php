<?php
include 'login_control.php';
include 'db.php';

$uid  = (int)$_SESSION['user_id'];
$meal_name = isset($_POST['name']) && $_POST['name'] !== '' ? trim($_POST['name']) : null;

if (!empty($_POST['eaten_at'])) {
  $eaten_at_raw = $_POST['eaten_at'];
  $eaten_at = str_replace('T', ' ', $eaten_at_raw);
  if (strlen($eaten_at_raw) === 16) { $eaten_at .= ':00'; }
} else {
  $eaten_at = date('Y-m-d H:i:s');
}

$experience = isset($_POST['experience']) ? (int) $_POST['experience'] : 0;

$posted = isset($_POST['allergens']) && is_array($_POST['allergens']) ? $_POST['allergens'] : [];
$category_ids = array_values(array_unique(array_map('intval', $posted)));

$is_ok = false;
$message = '';

$link->begin_transaction();

try {
  $sqlMeal = "INSERT INTO meal_logs (user_id, meal_name, eaten_at, experience) VALUES (?, ?, ?, ?)";
  $stmtMeal = $link->prepare($sqlMeal);
  if (!$stmtMeal) { throw new Exception('Prepare meal_logs misslyckades: '.$link->error); }

  $stmtMeal->bind_param("issi", $uid, $meal_name, $eaten_at, $experience);
  $stmtMeal->execute();

  $meal_log_id = (int)$link->insert_id;
  $stmtMeal->close();

  if ($meal_log_id <= 0) {
    throw new Exception('Insert ID saknas eller ogiltigt efter insert i meal_logs.');
  }

  if (!empty($category_ids)) {
    $ids_csv = implode(',', $category_ids);
    $check = $link->query("SELECT id FROM categories WHERE id IN ($ids_csv)");
    $valid_ids = [];
    while ($row = $check->fetch_assoc()) { $valid_ids[] = (int)$row['id']; }
    $category_ids = $valid_ids;
  }

  if (!empty($category_ids)) {
    $stmtMLC = $link->prepare("INSERT INTO meal_log_categories (meal_log_id, category_id) VALUES (?, ?)");
    if (!$stmtMLC) { throw new Exception('Prepare meal_log_categories misslyckades: '.$link->error); }

    foreach ($category_ids as $cid) {
      $stmtMLC->bind_param("ii", $meal_log_id, $cid);
      $stmtMLC->execute();
    }
    $stmtMLC->close();
  }

  $link->commit();
  $message = "Sparat!";
  $is_ok = true;

} catch (Throwable $e) {
  $link->rollback();
  $message = "Fel vid sparande: ".$e->getMessage();
  $is_ok = false;
} finally {
  $link->close();
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
      <img src="FoodMood_logo.png" alt="FoodMood Logo"
           class="logo <?= $is_ok ? '' : 'logo--error' ?>" />
      <h2 class="msg <?= $is_ok ? 'msg--ok' : 'msg--error' ?>">
        <?= htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8') ?>
      </h2>
      <a href="meal_log.php"><button type="button">Show meal log</button></a> <br>
      <a href="home.php"><button type="button">Home</button></a> <br>
  </div>
</body>
</html>
