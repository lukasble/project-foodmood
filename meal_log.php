
<?php
include 'db.php';

// Updating an experience

if (isset($_POST['update_experience'])) {
    $id = (int)$_POST['id'];
    $new_experience = (int)$_POST['experience'];

    $stmt = $link->prepare("UPDATE meal_logs SET experience = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_experience, $id);
    $stmt->execute();
    $stmt->close();
}

//Limit for how many entries to show 

$limit = isset($_GET['limit']) ? $_GET['limit'] : 10;

if ($limit == "all") {
    $sql = "SELECT id, meal_name, eaten_at, experience 
            FROM meal_logs
            ORDER BY eaten_at DESC";
} else {
    $limit = (int)$limit;
    $sql = "SELECT id, meal_name, eaten_at, experience 
            FROM meal_logs
            ORDER BY eaten_at DESC
            LIMIT $limit";
}

$result = $link->query($sql);

?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>FoodMood: Statistics</title>
    <link rel="stylesheet" href="style_home.css">
</head>
<body>


<div class="meal-log-container">
    <h1>My Meal Log</h1>

<form method="get">
        <label for="limit">Show entries:</label>
        <select name="limit" id="limit" onchange="this.form.submit()">
            <option value="10" <?php if ($limit == 10) echo 'selected'; ?>>10</option>
            <option value="20" <?php if ($limit == 20) echo 'selected'; ?>>20</option>
            <option value="50" <?php if ($limit == 50) echo 'selected'; ?>>50</option>
            <option value="all" <?php if ($limit == "all") echo 'selected'; ?>>All</option>
        </select>
    </form>

    <?php

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
                <td>
                    <form method='post' style='margin:0;'>
                    <input type='hidden' name='id' value='" . $row["id"] . "'>
                    <select name='experience' class='" . $experienceClass . "' onchange='this.form.submit()'>
                        <option value='0' " . ($row["experience"] == 0 ? "selected" : "") . ">Good</option>
                        <option value='1' " . ($row["experience"] == 1 ? "selected" : "") . ">Bad</option>
                    </select>
                    <input type='hidden' name='update_experience' value='1'>
                </form>
                </td>
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

