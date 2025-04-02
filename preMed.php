<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include 'db_connect.php';

if (!isset($_GET['appointment_id'])) {
    die("Appointment ID not provided.");
}

$appointment_id = intval($_GET['appointment_id']);

// Get patient info
$query = "SELECT p.firstName, p.lastName, p.Gender, p.DoB, p.emailAddress, a.PatientID 
          FROM Appointment a 
          JOIN Patient p ON a.PatientID = p.id 
          WHERE a.id = $appointment_id";

$result = $connection->query($query);

if ($result->num_rows != 1) {
    die("Invalid appointment or patient not found.");
}

$patient = $result->fetch_assoc();
$doctor_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medications = isset($_POST['medication']) ? $_POST['medication'] : [];

    // Update appointment status
    $connection->query("UPDATE Appointment SET status = 'Done' WHERE id = $appointment_id");

    // Insert prescriptions
    foreach ($medications as $medication_id) {
        $stmt = $connection->prepare("INSERT INTO Prescription (AppointmentID, MedicationID) VALUES (?, ?)");
        $stmt->bind_param("ii", $appointment_id, $medication_id);
        $stmt->execute();
    }

    echo "<script>alert('Medications prescribed successfully!'); window.location.href = 'Doctor-Page.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescribe Medication</title>
    <link href="https://fonts.googleapis.com/css2?family=Numans&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="img/icon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/styling.css">
    <link rel="stylesheet" type="text/css" href="css/stylePreMed.css">
</head>
<body>
<header class="header_section">
    <div class="container">
        <img src="img/logo.png" alt="logo" class="header_logo">
    </div>
</header>
<div class="container">
    <div class="prescription-container">
        <h2>Patient's Medications</h2>

        <p><strong>Name:</strong> <?= $patient['firstName'] . ' ' . $patient['lastName'] ?></p>
        <p><strong>Gender:</strong> <?= $patient['Gender'] ?></p>
        <p><strong>Date of Birth:</strong> <?= $patient['DoB'] ?></p>
        <p><strong>Email:</strong> <?= $patient['emailAddress'] ?></p>

        <form method="POST" id="prescriptionForm">
            <input type="hidden" name="appointment_id" value="<?= $appointment_id ?>">

            <div class="mb-3">
                <label class="form-label">Medications:</label> <br>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="aspirin" name="medication[]" value="1">
                    <label class="form-check-label" for="aspirin">Aspirin</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ibuprofen" name="medication[]" value="2">
                    <label class="form-check-label" for="ibuprofen">Ibuprofen</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="paracetamol" name="medication[]" value="3">
                    <label class="form-check-label" for="paracetamol">Paracetamol</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="antibiotics" name="medication[]" value="4">
                    <label class="form-check-label" for="antibiotics">Antibiotics</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-submit" style="background-color: #40beaf;border-color: #40beaf;">Submit</button>
        </form>
    </div>
</div>
<br><br>
<footer style="background: #38B2AC; color: white; padding: 10px; margin-top: 50px; text-align: center; font-size: 0.9em;">
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: auto; height: 50px;">
        <p style="margin: 0;">&copy; 2025 WeCare</p>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
            <h5 style="margin: 0; font-size: 0.9em;">Contact Us</h5>
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="https://web.whatsapp.com/" target="_blank">
                    <img src="img/facebook-icon (1).png" alt="WhatsApp" style="width: 20px; height: 20px;">
                </a>
                <a href="https://www.instagram.com/" target="_blank">
                    <img src="img/instagram-icon (1).png" alt="Instagram" style="width: 20px; height: 20px;">
                </a>
                <a href="https://www.snapchat.com/" target="_blank">
                    <img src="img/twitter-icon.png" alt="Snapchat" style="width: 20px; height: 20px;">
                </a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
