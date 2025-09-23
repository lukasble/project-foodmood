<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>entry</title>
    <link rel="stylesheet" href="style.css">
</head>

<div class="container">
    <body>
    <p>Enter meal:</p><br>

    <form action="insert.html" method="POST">
        <label>Meal Name:</label>
        <input type="text" name="name"><br>

        <?php
        $allergens = [
            "dairy" => "Dairy",
            "gluten" => "Gluten",
            "legumes" => "Legumes",
            "cruciferous" => "Cruciferous Vegetables",
            "alliums" => "Alliums",
            "fruits" => "Fruits",
            "sugar_alcohols" => "Sugar Alcohols and Artificial Sweeteners",
            "highfat" => "High-Fat and Fried Foods",
            "spicy" => "Spicy Foods",
            "acidic" => "Acidic Foods",
            "caffeine" => "Caffeine",
            "alcohol" => "Alcohol",
            "processed" => "Processed Foods"
        ];
        ?>

        <p>Allergens:</p><br>

        <?php foreach ($allergens as $key => $label): ?>
            <label for="allergen_<?php echo $key; ?>"><?php echo $label; ?>:</label>
            <input type="checkbox" id="allergen_<?php echo $key; ?>" 
                name="allergen_<?php echo $key; ?>" value="1"><br>
        <?php endforeach; ?>


        <label>State:</label>
        <select name="state" id="state">
            <option value="0">No ache</option>
            <option value="1">Tummy ache</option>
        </select><br>

        <input type="submit" value="Add Meal">
    
    </form><br>
    </body>
</div>
</html>


<a href="index.html" class="btn">Back to index</a>
