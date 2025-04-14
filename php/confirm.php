<?php
session_start();
header('Content-Type: application/json');
include("db_connect.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo json_encode(false);
    exit();
}

if (!isset($_POST['id'])) {
    echo json_encode(false);
    exit();
}

$appointment_id = intval($_POST['id']);
$query = "UPDATE Appointment SET status = 'Confirmed' WHERE id = $appointment_id";

if ($connection->query($query)) {
    echo json_encode(true);
} else {
    echo json_encode(false);
}
?>
