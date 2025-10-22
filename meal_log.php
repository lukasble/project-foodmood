
<?php
include 'login_control.php';
include 'db.php';

$uid = (int)$_SESSION['user_id'];

//Limit for how many entries to show 

    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;

?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <title>FoodMood: Statistics</title>
    <link rel="stylesheet" href="style_home.css">
</head>
<body>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff5e6, #ffe1b3);
            min-height: 100vh;
        }

        .meal-log-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 80%;
            max-width: 900px;
            margin: 40px auto;
            font-family: 'Poppins', sans-serif;
        }

        .meal-log-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .meal-log-table th {
            background-color: #ff7b00;
            color: white;
            text-transform: uppercase;
            padding: 12px;
        }

        .meal-log-table td {
            padding: 10px;
            border-bottom: 1px solid #f5d7a2;
        }

        .meal-log-table tr:nth-child(even) {
            background-color: #fff5e6;
        }

        .meal-log-table tr:hover {
            background-color: #ffe6cc;
        }

        .good-exp {
            color: green;
            font-weight: 600;
        }

        .bad-exp {
            color: red;
            font-weight: 600;
        }

        .button {
            display: inline-block;
            padding: 12px;
            background-color: #ff7b00;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
            margin: 10px;
        }

        .button:hover {
            background-color: #e66f00;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .delete-button {
            background: none;
            border: none;
            color: #d9534f;
            font-size: 18px;
            cursor: pointer;
            transition: 0.2s;
        }

        .delete-button:hover {
            color: #c9302c;
            transform: scale(1.1);
        }
    </style>


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
    // Updating an experience

    if (isset($_POST['update_experience'])) {
     $id = (int)$_POST['id'];
     $new_experience = (int)$_POST['experience'];

     $stmt = $link->prepare("UPDATE meal_logs SET experience = ? WHERE id = ? AND user_id = ?");
     $stmt->bind_param("iii", $new_experience, $id, $uid);
     $stmt->execute();
     $stmt->close();
    }

    // Updating meal name

    if(isset($_POST['update_meal_name'])) {
     $id = (int)$_POST['id'];
     $new_meal_name = $_POST['meal_name'];

     $stmt = $link->prepare("UPDATE meal_logs SET meal_name = ? WHERE id = ? AND user_id = ?");
     $stmt->bind_param("sii", $new_meal_name, $id, $uid);
     $stmt->execute();
     $stmt->close();
    }

    // Deleting an entry

    if (isset($_POST['delete_entry'])) {
     $id = (int)$_POST['id'];
     
     $stmt = $link->prepare("DELETE FROM meal_logs WHERE id = ? AND user_id = ?");
     $stmt->bind_param("ii", $id, $uid);
     $stmt->execute();
     $stmt->close();
    }

    if ($limit == "all") {
        $stmt = $link->prepare("
           SELECT m.id, m.meal_name, m.eaten_at, m.experience,
                   GROUP_CONCAT(c.name SEPARATOR ', ') AS categories
            FROM meal_logs m
            LEFT JOIN meal_log_categories mlc ON m.id = mlc.meal_log_id
            LEFT JOIN categories c ON mlc.category_id = c.id
            WHERE m.user_id = ?
            GROUP BY m.id
            ORDER BY m.eaten_at DESC; 
        ");
        $stmt->bind_param("i", $uid);
} else {
    $limit = (int)$limit;
    $stmt = $link->prepare("
            SELECT m.id, m.meal_name, m.eaten_at, m.experience,
                   GROUP_CONCAT(c.name SEPARATOR ', ') AS categories
            FROM meal_logs m
            LEFT JOIN meal_log_categories mlc ON m.id = mlc.meal_log_id
            LEFT JOIN categories c ON mlc.category_id = c.id
            WHERE m.user_id = ?
            GROUP BY m.id
            ORDER BY m.eaten_at DESC
            LIMIT ?;
        ");
        $stmt->bind_param("ii", $uid, $limit);
}

$stmt->execute(); 
$result = $stmt->get_result();

    echo '<table class="meal-log-table">';
    echo '<thead>
    <tr>
    <th>Meal</th>
    <th>Time Eaten</th>
    <th>Experience</th>
    <th>Categories</th>
    <th>Actions</th>
    </tr>
    </thead>';
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
            <td>
                <form method='post' style='margin:0; display:inline-block;'>
                    <input type='hidden' name='id' value='" . $row["id"] . "'>
                    <input type='text' name='meal_name' value='" . htmlspecialchars($row["meal_name"]) . "' onchange='this.form.submit()' style='width: 150px;'>
                    <input type='hidden' name='update_meal_name' value='1'>
            </td>
            <td>" . htmlspecialchars($row["eaten_at"]) . "</td>
            <td>
            <form method='post' style='margin:0; display:inline-block;'>
                <input type='hidden' name='id' value='" . $row["id"] . "'>
                <select name='experience' class='" . $experienceClass . "' onchange='this.form.submit()'>
                    <option value='0' " . ($row["experience"] == 0 ? "selected" : "") . ">Good</option>
                    <option value='1' " . ($row["experience"] == 1 ? "selected" : "") . ">Bad</option>
                </select>
                <input type='hidden' name='update_experience' value='1'>
            </form>
            </td>
            <td>" . ($row["categories"] ? htmlspecialchars($row["categories"]) : '—') . "</td>
            <td style='text-align:center;'>
            <form method='post' style='margin:0; display:inline-block;'>
                <input type='hidden' name='id' value='" . $row["id"] . "'>
                <input type='hidden' name='delete_entry' value='1'>
                <button type='submit' class='delete-button' onclick=\"return confirm('Are you sure you want to delete this entry?');\">🗑️</button>
            </form>
            </td>
        </tr>";
    }
    echo '</tbody></table>';

    $stmt->close();
    $link->close();
    
    ?>
<div class="button-container">
        <a href="home.php" class="button">Back to Home</a>
        <a href="analysis_basic.php" class="button">Make Basic Frequency Analysis</a>
    </div>

</div>



<?php
    if (session_status() === PHP_SESSION_NONE) { 
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        echo '<a href="logout.php" class="logout-btn">Log out</a>';
    }
?>

</body>
</html>

