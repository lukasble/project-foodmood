<?php
$allergens = [
  'dairy' => ['label' => 'Dairy', 'desc' => 'Milk, cheese, yogurt, butter, etc.'],
  'gluten' => ['label' => 'Gluten', 'desc' => 'Wheat, barley, rye, and products made from them.'],
  'legumes' => ['label' => 'Legumes', 'desc' => 'Beans, lentils, peas, peanuts.'],
  'cruciferous_vegetables' => ['label' => 'Cruciferous Vegetables', 'desc' => 'Broccoli, cauliflower, cabbage, Brussels sprouts.'],
  'alliums' => ['label' => 'Alliums', 'desc' => 'Onion, garlic, leeks, shallots.'],
  'fruits' => ['label' => 'Fruits', 'desc' => 'Various fruits; specify particular ones if needed.'],
  'sugar_alcohols_artificial_sweeteners' => ['label' => 'Sugar Alcohols & Artificial Sweeteners', 'desc' => 'e.g., xylitol, sorbitol, aspartame.'],
  'high_fat_fried' => ['label' => 'High-Fat & Fried Foods', 'desc' => 'Fried and very fatty foods.'],
  'spicy' => ['label' => 'Spicy Foods', 'desc' => 'Heavily spiced foods (e.g., chili).'],
  'acidic' => ['label' => 'Acidic Foods', 'desc' => 'Citrus, tomato, vinegar, etc.'],
  'caffeine' => ['label' => 'Caffeine', 'desc' => 'Coffee, tea, energy drinks, chocolate.'],
  'alcohol' => ['label' => 'Alcohol', 'desc' => 'All alcoholic beverages.'],
  'processed_food' => ['label' => 'Processed Foods', 'desc' => 'Cured meats and ready-made products with additives.'],
];
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

  <h1>Add Meal</h1>

  <form action="insert.php" method="POST" autocomplete="off">
    <label for="name">Meal Name:</label>
    <input type="text" id="name" name="name" maxlength="150"><br>

    <label for="eaten_at">When did you eat it?</label>
    <input type="datetime-local" id="eaten_at" name="eaten_at" required><br>

    <label for="experience">Experience:</label>
    <select name="experience" id="experience" required>
      <option value="0">Good</option>
      <option value="1">Bad</option>
    </select><br><br>

    <fieldset class="allergen-fieldset">
      <legend>Triggers</legend>
      <?php foreach ($allergens as $key => $data):
        $id = "allergen_$key";
        $tipId = "tip_$key";
        $label = htmlspecialchars($data['label'], ENT_QUOTES, 'UTF-8');
        $desc  = htmlspecialchars($data['desc'], ENT_QUOTES, 'UTF-8');
      ?>
        <div class="allergen">
          <input type="hidden" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" value="0">
          <input
            type="checkbox"
            id="<?= $id ?>"
            name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
            value="1"
            aria-describedby="<?= $tipId ?>"
            title="<?= $desc ?>"
          />
          <label for="<?= $id ?>"><?= $label ?></label>
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
</body>
</html>
