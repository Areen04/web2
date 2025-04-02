

<?php
file_put_contents("debug_log.txt", json_encode($_POST), FILE_APPEND);

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT);

$role = ucfirst(strtolower(trim($_POST['user_role'] ?? '')));
echo "<script>alert('Role received: $role');</script>";


    $id = trim($_POST['id'] ?? '');
    $gender = $_POST['gender'] ?? NULL;
    $dob = $_POST['dob'] ?? NULL;
    $speciality = $_POST['speciality'] ?? NULL;

    $checkStmt = $connection->prepare("SELECT id FROM patient WHERE id = ? UNION SELECT id FROM doctor WHERE id = ?");
    $checkStmt->bind_param("ss", $id, $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Error: ID already exists.'); window.history.back();</script>";
        exit;
    }
$emailCheckStmt = $connection->prepare("SELECT emailAddress FROM patient WHERE emailAddress = ? UNION SELECT emailAddress FROM doctor WHERE emailAddress = ?");
$emailCheckStmt->bind_param("ss", $email, $email);
$emailCheckStmt->execute();
$emailResult = $emailCheckStmt->get_result();

if ($emailResult->num_rows > 0) {
    echo "<script>alert('Error: Email is already registered.'); window.location.href = 'SignUp.html';</script>";
    exit;
}
    $profile_picture = "";
    if ($role === "Doctor" && isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
        $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            echo "<script>alert('Invalid image file.'); window.history.back();</script>";
            exit;
        }

        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $uniqueFileName = uniqid() . "." . $ext;
        $target_file = $target_dir . $uniqueFileName;

        if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            echo "<script>alert('Upload failed.'); window.history.back();</script>";
            exit;
        }

        $profile_picture = $uniqueFileName;
    }
echo "<script>alert('Role received: $role');</script>";

    if ($role === "Patient") {
        $stmt = $connection->prepare("INSERT INTO patient (id, firstName, lastName, Gender, DoB, emailAddress, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $id, $firstname, $lastname, $gender, $dob, $email, $password);
           $_SESSION['patient_id'] = $id;
    $_SESSION['user_id'] = $id;        
    $_SESSION['role'] = 'patient'; 
        $redirect = "pationt-page.php";
    } elseif ($role === "Doctor") {
        $stmt = $connection->prepare("INSERT INTO doctor (id, firstName, lastName, uniqueFileName, SpecialityID, emailAddress, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $id, $firstname, $lastname, $profile_picture, $speciality, $email, $password);
     $_SESSION['doctor_id'] = $id;
$_SESSION['user_id'] = $id;
$_SESSION['role'] = 'doctor';

        $redirect = "Doctor-Page.php";
    } else {
        echo "<script>alert('Invalid role.'); window.history.back();</script>";
        exit;
    }

    if ($stmt->execute()) {
    $_SESSION['success'] = "Registration successful!";
header("Location: $redirect");
exit();

    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }
}
?>
