<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: LogIn.php");
    exit();
}

include 'db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: patient_homepage.php?error=InvalidAppointment");
    exit();
}

$appointment_id = intval($_GET['id']);
$patient_id = $_SESSION['user_id'];

// First, delete prescriptions related to the appointment
$query1 = "DELETE FROM prescription WHERE AppointmentID = ?";
$stmt1 = $connection->prepare($query1);
$stmt1->bind_param("i", $appointment_id);
$stmt1->execute();
$stmt1->close();

// Now, delete the appointment
$query2 = "DELETE FROM appointment WHERE id = ? AND PatientID = ?";
$stmt2 = $connection->prepare($query2);
$stmt2->bind_param("ii", $appointment_id, $patient_id);

if ($stmt2->execute()) {
    header("Location: pationt-page.php?success=AppointmentCanceled");
} else {
    header("Location: pationt-page.php?error=CancelFailed");
}

$stmt2->close();
$connection->close();
exit();
