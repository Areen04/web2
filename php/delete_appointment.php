<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    echo "false";
    exit();
}

if (isset($_POST['id'])) {
    $appointmentId = intval($_POST['id']);
    $stmt = $connection->prepare("DELETE FROM appointment WHERE id = ? AND PatientID = ?");
    $stmt->bind_param("ii", $appointmentId, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo "true";
    } else {
        echo "false";
    }
    $stmt->close();
} else {
    echo "false";
}
?>
