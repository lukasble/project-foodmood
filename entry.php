<?php
include 'login_control.php';
include 'db.php';

$sql = "SELECT id, name, COALESCE(description,'') AS description 
        FROM categories
        ORDER BY name";
$result = $link->query($sql);

$allergens = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id   = (int)$row['id'];
        $name = $row['name']; 

        $label = ucwords(str_replace('_', ' ', $name));

        $allergens[$id] = [
            'label' => $label,
            'desc'  => $row['description']
        ];
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Foodmood: Add Meal</title>
  <link rel="stylesheet" href="style_home.css">
</head>
<body>
<div class="container">

  <h2>Please enter details of your meal:</h2>

  <form action="insert.php" method="POST" autocomplete="off">
  <div class="form-row">
    <label for="name">Meal Name:</label>
    <input type="text" id="name" name="name" maxlength="150">
  </div>

  <div class="form-row">
    <label for="eaten_at">Eaten at:</label>
    <input type="datetime-local" id="eaten_at" name="eaten_at" required>
  </div>

  <div class="form-row">
    <label for="experience">Experience:</label>
    <select name="experience" id="experience" required>
      <option value="0">Good</option>
      <option value="1">Bad</option>
    </select><br>
  </div>

    <fieldset class="allergen-fieldset">
      <legend>Categories</legend>
      <?php foreach ($allergens as $catId => $data):
        $idAttr = "allergen_$catId";
        $tipId = "tip_$catId";
        $label = htmlspecialchars($data['label'], ENT_QUOTES, 'UTF-8');
        $desc  = htmlspecialchars($data['desc'], ENT_QUOTES, 'UTF-8');
      ?>
        <div class="allergen">
          <input
            type="checkbox"
            id="<?= $idAttr ?>"
            name="allergens[]"
            value="<?= (int)$catId ?>"
            aria-describedby="<?= $tipId ?>"
            title="<?= $desc ?>"
          />
          <label for="<?= $idAttr ?>"><?= $label ?></label>
          <div id="<?= $tipId ?>" class="tooltip" role="tooltip">
            <?= $desc ?>
          </div>
        </div>
      <?php endforeach; ?>
    </fieldset><br>

    <input type="submit" class="btn" value="Add Meal">
  </form><br>

  <a href="home.php" class="btn">Back to home</a>
</div>

<script>
(function () {
  const input = document.getElementById('eaten_at');
  if (!input.value) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    input.value = now.toISOString().slice(0,16);
  }
})();
</script>

<?php
  if (session_status() === PHP_SESSION_NONE) { 
      session_start();
  }
  if (isset($_SESSION['user_id'])) {
      echo '<a href="logout.php"><button type="button" class="logout-btn">Log out</button></a>';
  }
?>
</body>
</html>
