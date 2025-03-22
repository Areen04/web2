<?php

include("db_connect.php");


if (!isset($_GET['id'])) {
    die("Appointment ID is missing");
}

$appointment_id = intval($_GET['id']);


$query = "UPDATE Appointment SET status = 'Confirmed' WHERE id = $appointment_id";

if ($connection->query($query)) {
    
    header("Location: Doctor-Page.php");
    exit();
} else {
    echo "Failed to update appointment: " . $connection->error;
}
?>
