<?php
include 'db.php';

// Fetch data from POST request
$meal_name = $_POST['name'];
$experience = $_POST['experience'];
$dairy = $_POST['dairy'];
$gluten = $_POST['gluten'];	
$legumes = $_POST['legumes'];
$cruciferous_vegetables = $_POST['cruciferous_vegetables'];
$alliums = $_POST['alliums'];
$fruits = $_POST['fruits'];
$sugar_alcohols_artificial_sweeteners = $_POST['sugar_alcohols_artificial_sweeteners'];
$high_fat_fried = $_POST['high_fat_fried'];
$spicy = $_POST['spicy'];
$acidic = $_POST['acidic'];
$caffeine = $_POST['caffeine'];	
$alcohol = $_POST['alcohol'];
$processed_food = $_POST['processed_food'];

$sql = "INSERT INTO meal_logs (meal_name, experience, dairy, gluten, legumes, cruciferous_vegetables, alliums, fruits, sugar_alcohols_artificial_sweeteners, high_fat_fried, spicy, acidic, caffeine, alcohol, processed_food) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $link->prepare($sql);

// s for string i for integer
$stmt->bind_param(
    "ssiiiiiiiiiiiii", 
    $meal_name, 
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
    $message = "New record created successfully!";
} else {
    $message = "Error: " . $stmt->error;
}

// Close the database connection
$link->close();
?>