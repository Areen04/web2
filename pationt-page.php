<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (isset($_SESSION['message'])) {
    echo "<script>alert('" . $_SESSION['message'] . "');</script>";
    unset($_SESSION['message']);
}
?>

<?php

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: HomePage.html");
    exit();
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'php/db_connect.php';

$patient_id = $_SESSION['user_id'];

// Retrieve patient information
$query = "SELECT firstName, lastName, emailAddress, Gender, DoB FROM patient WHERE id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc() ?? []; 

// Retrieve all appointments for this patient
$query = "SELECT a.id, a.date, a.time, a.status, d.firstName, d.lastName, d.uniqueFileName 
          FROM appointment a
          JOIN doctor d ON a.DoctorID = d.id
          WHERE a.PatientID = ? AND a.status != 'done'
          ORDER BY a.date, a.time ASC";


$stmt = $connection->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient's HomePage</title>
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
                    <a href="logout.php" class="text-white fw-bold text-decoration-none fs-4">
                        <img src="img/logoutIcon.png" alt="Logout" width="50">
                    </a>
                </div>
                <img src="img/logo2.PNG" alt="WeCare Logo" class="header_logo">
                <br><br><br>
                <h1 class="display-5 fw-bold">Welcome, <?= htmlspecialchars($patient['firstName'] . " " . $patient['lastName']) ?>!</h1>
            </div>
        </div>
    </header>
    
    <div class="container my-5">
        <div class="card shadow">
            <div class="card-header text-white" style="background-color: #38B2AC;">
                <h3>Patient Information</h3>
            </div>
            <div class="card-body">
                <p><strong>First Name:</strong> <?= htmlspecialchars($patient['firstName']) ?></p>
                <p><strong>Last Name:</strong> <?= htmlspecialchars($patient['lastName']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($patient['emailAddress']) ?></p>
            </div>
        </div>
    </div>
    
    <div class="container my-4">
        <a href="apt.php" class="btn btn-primary">Book an Appointment</a>
    </div>
    
    <div class="container my-5">
        <h3 class="mb-4">Upcoming Appointments</h3>
        <table class="table table-striped table-bordered shadow">
            <thead style="background-color: #38B2AC; color: white;">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Doctor's Name</th>
                    <th>Doctor's Photo</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
           <tbody>
    <?php while ($appointment = $appointments->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($appointment['date']) ?></td>
        <td><?= htmlspecialchars($appointment['time']) ?></td>
        <td>Dr. <?= htmlspecialchars($appointment['firstName'] . " " . $appointment['lastName']) ?></td>
        <td>
            <img src="uploads/<?= htmlspecialchars($appointment['uniqueFileName']) ?>" alt="Doctor's photo" width="90" height="90">
        </td>
        <td><?= htmlspecialchars($appointment['status']) ?></td>
        <td>
    <a href="cancel.php?id=<?= $appointment['id'] ?>" 
       class="btn btn-danger btn-sm" 
       onclick="return confirm('Are you sure you want to cancel this appointment?');">
        Cancel
    </a>
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
