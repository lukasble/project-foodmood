
<?php
include 'db.php';
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>FoodMood: Statistics</title>
    <link rel="stylesheet" href="style_home.css">
</head>
<body>

<style>
    .good-exp {
    color: green;
    font-weight: bold;
}
.bad-exp {
    color: red;
    font-weight: bold;
}
</style>

<div class="meal-log-container">
    <h1>My Meal Log</h1>

    <?php
  
    $sql = "SELECT id, meal_name, eaten_at, experience 
            FROM meal_logs
            ORDER BY eaten_at DESC";

    $result = $link->query($sql);

    echo '<table class="meal-log-table">';
    echo '<thead><tr><th>Meal</th><th>Time Eaten</th><th>Experience</th></tr></thead>';
    echo '<tbody>';

    while($row = $result->fetch_assoc()) { 

        $experienceText = "";

        if ($row["experience"] == 0) {
            $experienceText = "Good";
            $experienceClass = "good-exp";
        } else {
            $experienceText = "Bad";
            $experienceClass = "bad-exp";
        }
                                          
        echo "<tr>
                <td>" . $row["meal_name"] . "</td>
                <td>" . $row["eaten_at"] . "</td>
                <td class='$experienceClass'>" . $experienceText . "</td>
              </tr>";
        }

    echo '</tbody></table>';

    $link->close();
    
    ?>

</div>

<a href="home.php" class="btn">Back to home</a>

<a href="analysis_basic.php" class="btn">Make Basic Frequency Analysis</a>

</body>
</html>


//Show ## entries button

//Koppla artiklar till mest visade, knapp för att visa meals som oftast ger positiva eller negativa reaktioner
//Kunna ändra experience på en måltid
//Visa i rätt ordning

//Avklarade
//Fixa bad or good experience 