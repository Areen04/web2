<?php
session_start();
include 'php/db_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: HomePage.html");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_doctors') {
    $speciality = $_POST['speciality'];
    $stmt = $connection->prepare("SELECT id, firstName, lastName FROM Doctor WHERE SpecialityID = ?");
    $stmt->bind_param("i", $speciality);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctors = array();
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    echo json_encode($doctors);
    exit();
}

$sql = "SELECT DISTINCT s.id, s.speciality FROM speciality s JOIN Doctor d ON s.id = d.SpecialityID";
$result = $connection->query($sql);
$specialties = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $specialties[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && !isset($_POST['action'])) {
    $doctor_id = intval($_POST['id']);
    $patient_id = $_SESSION['user_id'];
    $date = htmlspecialchars($_POST['date']);
    $time = htmlspecialchars($_POST['time']);
    $reason = htmlspecialchars($_POST['reason']);
    $status = "Pending";

    if (empty($date) || empty($time) || empty($reason)) {
        die("Missing required fields!");
    }

    $checkDoctor = $connection->prepare("SELECT id FROM Doctor WHERE id = ?");
    $checkDoctor->bind_param("i", $doctor_id);
    $checkDoctor->execute();
    $result = $checkDoctor->get_result();
    if ($result->num_rows == 0) {
        die("Invalid doctor selection.");
    }
    $checkDoctor->close();

    $sql = "INSERT INTO appointment (DoctorID, PatientID, date, time, reason, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iissss", $doctor_id, $patient_id, $date, $time, $reason, $status);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Your appointment has been booked successfully!";
        header("Location: pationt-page.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment</title>
    <link rel="shortcut icon" href="img/icon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Numans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="css/style_aptmnt.css" />
    <link rel="stylesheet" type="text/css" href="css/styling.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="hero_area">
    <div class="hero_bg_box">
        <img src="img/hero-bg.png" alt="background">
    </div>
    <header class="header_section">
        <div class="container">
            <img src="img/logo2.png" alt="logo" class="header_logo">
        </div>
    </header>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="appointment-container">
                    <h2 class="text-center">Book an Appointment</h2>
                    <form method="POST" action="apt.php">
                        <div class="mb-3">
                            <label for="specialty" class="form-label">Select Speciality:</label>
                            <select name="specialty" id="specialty" class="form-control" required>
                                <option value="">-- Select Speciality --</option>
                                <?php foreach ($specialties as $specialty): ?>
                                    <option value="<?= $specialty['id'] ?>">
                                        <?= htmlspecialchars($specialty['speciality']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="doctor" class="form-label">Select Doctor:</label>
                            <select name="id" id="doctor" class="form-control" required>
                                <option value="">-- Select Doctor --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date:</label>
                            <input type="date" name="date" id="date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Time:</label>
                            <input type="time" name="time" id="time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Visit:</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Book Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
</div>
<script>
$('#specialty').on('change', function () {
    var selectedSpecialty = $(this).val();
    if (selectedSpecialty !== '') {
        $.ajax({
            url: 'apt.php',
            type: 'POST',
            data: {
                action: 'get_doctors',
                speciality: selectedSpecialty
            },
            success: function (response) {
                var doctors = JSON.parse(response);
                var options = '<option value="">-- Select Doctor --</option>';
                doctors.forEach(function (doc) {
                    options += '<option value="' + doc.id + '">' + doc.firstName + ' ' + doc.lastName + '</option>';
                });
                $('#doctor').html(options);
            }
        });
    } else {
        $('#doctor').html('<option value="">-- Select Doctor --</option>');
    }
});
</script>
</body>
</html>
