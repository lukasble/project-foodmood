<?php

#Insertdata.php

#insert.php -- is the php code to receive the input from index.php
#HTML Form and insert the new movie data into the database. 
#It should redirects back to index.php after success or fail (a message 
#will be nice to have in both cases).
include 'db.php';

// Fetch data from POST request
$mname = $_POST['mname'];
$myear = $_POST['myear'];
$mgenre = $_POST['mgenre'];
$mrating = $_POST['mrating'];

$sql = "INSERT INTO movies (mname, myear, mgenreid, mrating) VALUES (?, ?, ?, ?)";
$stmt = $link->prepare($sql);

// s for string i for integer
$stmt->bind_param("ssii", $mname, $myear, $mgenre, $mrating);
$result = $stmt->execute();

    
if ($result) {        
    $message = "New record created successfully!";
} else {
    $message = "Error: " . $stmt->error;
}

// Close the database connection
$link->close();
?>