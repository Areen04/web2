<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// اتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "root", "wecare", 8889);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';

    if (empty($email) || empty($password) || empty($role)) {
        $error = "Please fill all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, firstName, lastName, password FROM " . ($role == "doctor" ? "doctor" : "patient") . " WHERE emailAddress = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $firstname, $lastname, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $firstname . " " . $lastname;
                $_SESSION['role'] = $role;

                // توجيه المستخدم حسب الدور
                if ($role == "doctor") {
                    header("Location: Doctor’s-Page.html");
                } else {
                    header("Location: pationt-page.html");
                }
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Email not registered or role mismatch.";
        }
    }

    // خزن الرسالة في السيشن وارجع لصفحة الفورم
    $_SESSION['login_error'] = $error;
    header("Location: login_form.php");
    exit();
}
?>
