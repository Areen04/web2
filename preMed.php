<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php';

// تأكيد تسجيل الدخول كـ دكتور
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header("Location: LogIn.html");
    exit();
}

$doctor_id = $_SESSION['user_id'];

// تحقق من توفر patient_id
if (!isset($_GET['patient_id'])) {
    die("Patient ID not provided.");
}

$patient_id = intval($_GET['patient_id']);

// جلب أحدث موعد Confirmed لهذا المريض مع هذا الدكتور
$appointment_result = $connection->query("
    SELECT id 
    FROM Appointment 
    WHERE PatientID = $patient_id AND DoctorID = $doctor_id AND status = 'Confirmed'
    ORDER BY date DESC, time DESC
    LIMIT 1
");

if ($appointment_result->num_rows !== 1) {
    die("No confirmed appointment found for this patient.");
}

$appointment = $appointment_result->fetch_assoc();
$appointment_id = $appointment['id'];

// جلب معلومات المريض
$query = "SELECT firstName, lastName, Gender, DoB, emailAddress 
          FROM Patient 
          WHERE id = $patient_id";
$result = $connection->query($query);

if ($result->num_rows !== 1) {
    die("Patient not found.");
}

$patient = $result->fetch_assoc();

// عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medications = isset($_POST['medication']) ? $_POST['medication'] : [];

    // تحديث حالة الموعد
    $connection->query("UPDATE Appointment SET status = 'Done' WHERE id = $appointment_id");

    // إضافة الأدوية بدون تكرار
    foreach ($medications as $medication_id) {
        $check = $connection->query("
            SELECT * FROM Prescription 
            WHERE AppointmentID = $appointment_id AND MedicationID = $medication_id
        ");
        if ($check->num_rows == 0) {
            $stmt = $connection->prepare("INSERT INTO Prescription (AppointmentID, MedicationID) VALUES (?, ?)");
            $stmt->bind_param("ii", $appointment_id, $medication_id);
            $stmt->execute();
        }
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
<body style="min-height: 100vh; display: flex; flex-direction: column;">
<header class="header_section">
    <div class="container">
        <img src="img/logo.png" alt="logo" class="header_logo">
    </div>
</header>
<main style="flex: 1;">
<div class="container mt-5">
    <div class="prescription-container">
        <h2>Patient's Medications</h2>
        <p><strong>Name:</strong> <?= $patient['firstName'] . ' ' . $patient['lastName'] ?></p>
        <p><strong>Gender:</strong> <?= $patient['Gender'] ?></p>
        <p><strong>Date of Birth:</strong> <?= $patient['DoB'] ?></p>
        <p><strong>Email:</strong> <?= $patient['emailAddress'] ?></p>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Medications:</label><br>
                <?php
$med_result = $connection->query("SELECT id, MedicationName FROM Medication");
while ($med = $med_result->fetch_assoc()):
?>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="medication[]" value="<?= $med['id'] ?>" id="med<?= $med['id'] ?>">
        <label class="form-check-label" for="med<?= $med['id'] ?>"><?= htmlspecialchars($med['MedicationName']) ?></label>
    </div>
<?php endwhile; ?>

            </div>
            <button type="submit" class="btn btn-primary" style="background-color: #40beaf; border-color: #40beaf;">Submit</button>
        </form>
    </div>
</div>
</main>
<footer style="background: #38B2AC; color: white; padding: 10px; margin-top: 50px; text-align: center;">
    <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: auto;">
        <p>&copy; 2025 WeCare</p>
        <div>
            <h5>Contact Us</h5>
            <div style="display: flex; gap: 10px;">
                <a href="https://web.whatsapp.com/" target="_blank"><img src="img/facebook-icon (1).png" width="20"></a>
                <a href="https://www.instagram.com/" target="_blank"><img src="img/instagram-icon (1).png" width="20"></a>
                <a href="https://www.snapchat.com/" target="_blank"><img src="img/twitter-icon.png" width="20"></a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>
