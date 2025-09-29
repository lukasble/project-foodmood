

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>FoodMood: Statistics</title>
    <link rel="stylesheet" href="style.css">
</head>

<a href="home.php" class="btn">Back to home</a>

<div class="meal-log-container">
    <h1>My Meal Logs</h1>

    <?php
    include 'db.php';

    echo '<table><tr><th>Meal</th><th>Time Eaten</th><th>Experience</th></tr>';

    $sql = "SELECT meal_name, eaten_at, experience
        FROM meal_logs;

    $result = $link->query($sql);


    while($row = $result->fetch_assoc()) { 
                                          
        echo "<tr>
                <td>" . $row["meal_name"] . "</td>
                <td>" . $row["eaten_at"] . "</td>
                <td>" . $row["experience"] . "</td>
              </tr>";
}

echo '</table>';
    ?>
</div>



//Namn Tid Mag reaktion Knapp där det står beräkna statistik/analysera data
//Show ## entries button
//Koppla artiklar till mest visade, knapp för att visa meals som oftast ger positiva eller negativa reaktioner
