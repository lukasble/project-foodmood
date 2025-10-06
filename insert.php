<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FoodMood: Home</title>
  <link rel="stylesheet" href="style_home.css">
</head>

<?php
// insert.php
include 'db.php';

// Hardcoded user_id until login is ready
$user_id = 1;

// consider using $_SESSION instead
// https://medium.com/@jpmorris/how-to-build-a-php-login-form-using-sessions-c7fb6d8ecebe

function flag($key) {
  return isset($_POST[$key]) ? (int) ($_POST[$key] ? 1 : 0) : 0;
}

$meal_name = isset($_POST['name']) && $_POST['name'] !== '' ? trim($_POST['name']) : null;

// eaten_at: datetime-local to Y-m-d H:i:s
if (!empty($_POST['eaten_at'])) {
  $eaten_at_raw = $_POST['eaten_at'];
  $eaten_at = str_replace('T', ' ', $eaten_at_raw) . (strlen($eaten_at_raw) === 16 ? ':00' : '');
} else {
  $eaten_at = date('Y-m-d H:i:s');
}

$experience = isset($_POST['experience']) ? (int) $_POST['experience'] : 0;

// Flags
$dairy = flag('dairy');
$gluten = flag('gluten');
$legumes = flag('legumes');
$cruciferous_vegetables = flag('cruciferous_vegetables');
$alliums = flag('alliums');
$fruits = flag('fruits');
$sugar_alcohols_artificial_sweeteners = flag('sugar_alcohols_artificial_sweeteners');
$high_fat_fried = flag('high_fat_fried');
$spicy = flag('spicy');
$acidic = flag('acidic');
$caffeine = flag('caffeine');
$alcohol = flag('alcohol');
$processed_food = flag('processed_food');

$sql = "INSERT INTO meal_logs
(user_id, meal_name, eaten_at, experience,
 dairy, gluten, legumes, cruciferous_vegetables, alliums, fruits,
 sugar_alcohols_artificial_sweeteners, high_fat_fried, spicy, acidic, caffeine, alcohol, processed_food)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $link->prepare($sql);
if (!$stmt) {
  exit('Prepare failed: ' . $link->error);
}

$stmt->bind_param(
  "issiiiiiiiiiiiiii",
  $user_id,
  $meal_name,
  $eaten_at,
  $experience,
  $dairy,
  $gluten,
  $legumes,
  $cruciferous_vegetables,
  $alliums,
  $fruits,
  $sugar_alcohols_artificial_sweeteners,
  $high_fat_fried,
  $spicy,
  $acidic,
  $caffeine,
  $alcohol,
  $processed_food
);


$result = $stmt->execute();

if ($result) {
?>
  <body>
    <div class="container">
      <h1> Meal added to log! </h1>
      <a href="home.php" class="btn">Back to home</a><br>
    </div>
  </body>
<?php
} else {
  echo "Error: " . $stmt->error;
}


$stmt->close();
$link->close();
