<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: LogIn.html");
    exit();
}
$doctor_id = $_SESSION['user_id'];

?>

<?php include 'db_connect.php';


if ($connection->connect_error) {
    die("connectin field" . $connection->connect_error);
}


if (!isset($_SESSION['doctor_id'])) {
    
    header("Location: LogIn.html");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];
$query = "SELECT d.firstName, d.lastName, d.emailAddress, d.uniqueFileName, s.speciality 
          FROM Doctor d 
          JOIN Speciality s ON d.SpecialityID = s.id 
          WHERE d.id = $doctor_id";

$result = $connection->query($query);


if ($result->num_rows == 1) {
    $doctor = $result->fetch_assoc();
} else {
    echo "<p class='text-danger'>error can't find doctor information</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor's HomePage</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="icon" type="image/png" href="img/icon.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Numans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/styling.css">
</head>

<body class="bg-light">

    
    <header>
        <div class="bg-primary text-white text-center py-5" style="background-size: cover; height: 300px; background-image: url('img/hero-bg.png');">
            <div class="container">
                <div class="position-absolute top-0 end-0 m-3">
                    <a href="HomePage.html" class="text-white fw-bold text-decoration-none fs-4">Log Out</a>
                </div>
                <div class="container">
                    <img src="img/logo2.png" alt="logo" class="header_logo">
                </div>
                <h1 class="display-5 fw-bold">Welcome, Dr. <?= $doctor['firstName']; ?></h1>
            </div>
        </div>
    </header>

    
<div class="container my-5">
    <div class="card shadow">
        <div class="card-header text-white" style="background-color: #38B2AC;">
            <h3>Doctor Information</h3>
        </div>
        <div class="card-body d-flex align-items-center">
            
            <img src="uploads/<?= htmlspecialchars($doctor['uniqueFileName']) ?>" 
     alt="Doctor Image" 
     class="rounded-circle me-4" 
     style="width: 120px; height: 120px; object-fit: cover;">


            
            
            <div>
            <p><strong>First Name:</strong> <?= $doctor['firstName']; ?></p>
            <p><strong>Last Name:</strong> <?= $doctor['lastName']; ?></p>
            <p><strong>ID:</strong> <?= $doctor_id; ?></p>
            <p><strong>Speciality:</strong> <?= $doctor['speciality']; ?></p>
            <p><strong>Email:</strong> <?= $doctor['emailAddress']; ?></p>

            </div>
        </div>
    </div>
</div>


    
    <?php
$doctor_id = $_SESSION['doctor_id'];

$query = "SELECT 
    a.id AS appointment_id,
    a.date,
    a.time,
    a.reason,
    a.status,
    p.firstName,
    p.lastName,
    p.Gender,
    TIMESTAMPDIFF(YEAR, p.DoB, CURDATE()) AS age
FROM Appointment a
JOIN Patient p ON a.PatientID = p.id
WHERE a.DoctorID = $doctor_id
AND a.status IN ('Pending', 'Confirmed')
ORDER BY a.date, a.time";

$result = $connection->query($query);

if (!$result) {
    die("Query failed: " . $connection->error);
}
?>

    <div class="container my-5">
        <h3 class="mb-4">Upcoming Appointments</h3>
        <table class="table table-striped table-bordered shadow">
            <thead style="background-color: #38B2AC; color: white;">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Patient's Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Reason for Visit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
<?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['date'] ?></td>
        <td><?= date("g:i A", strtotime($row['time'])) ?></td>
        <td><?= $row['firstName'] . ' ' . $row['lastName'] ?></td>
        <td><?= $row['age'] ?></td>
        <td><?= $row['Gender'] ?></td>
        <td><?= $row['reason'] ?></td>
        <td>
            <?php if ($row['status'] == 'Pending'): ?>
                <a href="confirm.php?id=<?= $row['appointment_id'] ?>" class="btn btn-warning btn-sm">Confirm</a>
            <?php elseif ($row['status'] == 'Confirmed'): ?>
                <a href="preMed.php?id=<?= $row['appointment_id'] ?>" class="btn btn-success btn-sm">Prescribe</a>
            <?php endif; ?>
        </td>
    </tr>
<?php endwhile; ?>
</tbody>

        </table>
    </div>
<?php 
$doctor_id = $_SESSION['doctor_id'];

$query = "
SELECT DISTINCT
    p.id AS patient_id,
    p.firstName,
    p.lastName,
    p.Gender,
    TIMESTAMPDIFF(YEAR, p.DoB, CURDATE()) AS age,
    GROUP_CONCAT(m.MedicationName SEPARATOR ', ') AS medications
FROM Patient p
JOIN Appointment a ON p.id = a.PatientID
LEFT JOIN Prescription pr ON a.id = pr.AppointmentID
LEFT JOIN Medication m ON pr.MedicationID = m.id
WHERE a.DoctorID = $doctor_id
GROUP BY p.id
";

$patients_result = $connection->query($query);

if (!$patients_result) {
    die("Query failed: " . $connection->error);
}

?>
    
    <div class="container my-5">
        <h3 class="mb-4">Your Patients</h3>
        <table class="table table-striped table-bordered shadow">
            <thead style="background-color: #38B2AC; color: white;">
                <tr>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Medications</th>
                    <th>Medication Link</th>
                </tr>
            </thead>
            <tbody>
<?php while ($row = $patients_result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['firstName'] . ' ' . $row['lastName'] ?></td>
        <td><?= $row['age'] ?></td>
        <td><?= $row['Gender'] ?></td>
        <td><?= $row['medications'] ? $row['medications'] : 'N/A' ?></td>
        <td>
            <a href="preMed.php?patient_id=<?= $row['patient_id'] ?>" class="btn btn-success btn-sm">Prescribe</a>
        </td>
    </tr>
<?php endwhile; ?>
</tbody>

        </table>
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
</body>
</html>