<?php
$allergens = [
  'dairy' => ['label' => 'Dairy', 'desc' => 'Milk, cheese, yogurt, butter, etc.'],
  'gluten' => ['label' => 'Gluten', 'desc' => 'Wheat, barley, rye, and products made from them.'],
  'legumes' => ['label' => 'Legumes', 'desc' => 'Beans, lentils, peas, peanuts.'],
  'cruciferous' => ['label' => 'Cruciferous Vegetables', 'desc' => 'Broccoli, cauliflower, cabbage, Brussels sprouts.'],
  'alliums' => ['label' => 'Alliums', 'desc' => 'Onion, garlic, leeks, shallots.'],
  'fruits' => ['label' => 'Fruits', 'desc' => 'Various fruits; specify particular ones if needed.'],
  'sugar_alcohols' => ['label' => 'Sugar Alcohols and Artificial Sweeteners', 'desc' => 'e.g., xylitol, sorbitol, aspartame.'],
  'highfat' => ['label' => 'High-Fat and Fried Foods', 'desc' => 'Fried and very fatty foods.'],
  'spicy' => ['label' => 'Spicy Foods', 'desc' => 'Heavily spiced foods (e.g., chili).'],
  'acidic' => ['label' => 'Acidic Foods', 'desc' => 'Citrus, tomato, vinegar, etc.'],
  'caffeine' => ['label' => 'Caffeine', 'desc' => 'Coffee, tea, energy drinks, chocolate.'],
  'alcohol' => ['label' => 'Alcohol', 'desc' => 'All alcoholic beverages.'],
  'processed' => ['label' => 'Processed Foods', 'desc' => 'Cured meats and ready-made products with additives.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Entry</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">

  <p>Enter meal:</p><br>

  <form action="insert.html" method="POST">
    <label for="name">Meal Name:</label>
    <input type="text" id="name" name="name"><br>

    <fieldset class="allergen-fieldset">
      <legend>Allergens</legend>
      <?php foreach ($allergens as $key => $data): 
        $id = "allergen_$key";
        $tipId = "tip_$key";
        $label = htmlspecialchars($data['label'], ENT_QUOTES, 'UTF-8');
        $desc  = htmlspecialchars($data['desc'], ENT_QUOTES, 'UTF-8');
      ?>
        <div class="allergen">
          <input
            type="checkbox"
            id="<?= $id ?>"
            name="<?= $id ?>"
            value="1"
            aria-describedby="<?= $tipId ?>"
            title="<?= $desc ?>" />
          <label for="<?= $id ?>"><?= $label ?></label>
          <div id="<?= $tipId ?>" class="tooltip" role="tooltip">
            <?= $desc ?>
          </div>
        </div>
      <?php endforeach; ?>
    </fieldset><br>

    <label for="state">State:</label>
    <select name="state" id="state">
      <option value="0">No ache</option>
      <option value="1">Tummy ache</option>
    </select><br><br>

    <input type="submit" value="Add Meal">
  </form><br>

  <a href="index.html" class="btn">Back to index</a>
</div>
</body>
</html>
