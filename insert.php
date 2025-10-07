<?php
// insert.php
include 'db.php';

/* if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo '<p class="msg msg--error">You must be logged in.</p>';
  exit;
} */


$user_id = 1;
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


$link->begin_transaction();

try {
  $sqlMeal = "INSERT INTO meal_logs (user_id, meal_name, eaten_at, experience)
              VALUES (?, ?, ?, ?)";
  $stmtMeal = $link->prepare($sqlMeal);
  if (!$stmtMeal) { throw new Exception('Prepare meal_logs misslyckades: '.$link->error); }

  $stmtMeal->bind_param("issi", $user_id, $meal_name, $eaten_at, $experience);
  if (!$stmtMeal->execute()) { throw new Exception('Execute meal_logs misslyckades: '.$stmtMeal->error); }
  $meal_log_id = $stmtMeal->insert_id;
  $stmtMeal->close();

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
      if (!$stmtMLC->execute()) {
        throw new Exception('Insert i meal_log_categories misslyckades: '.$stmtMLC->error);
      }
    }
    $stmtMLC->close();
  }

  $link->commit();
  echo "Sparat! (meal_log_id: $meal_log_id)";
} catch (Throwable $e) {
  $link->rollback();
  http_response_code(500);
  echo "Fel vid sparande: ".$e->getMessage();
} finally {
  $link->close();
}

?>