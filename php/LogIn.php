<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';

    if (empty($email) || empty($password) || empty($role)) {
        $error = "Please fill all fields.";
    } else {
        $stmt = $connection->prepare("SELECT id, firstName, lastName, password FROM " . ($role == "doctor" ? "doctor" : "patient") . " WHERE emailAddress = ?");
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
    $_SESSION['user_id'] = $id;
    $_SESSION['doctor_id'] = $id; 
    $_SESSION['user_name'] = $firstname . " " . $lastname;
    $_SESSION['role'] = $role;
    header("Location: ../Doctor-Page.php");
} else {
    $_SESSION['user_id'] = $id;
    $_SESSION['patient_id'] = $id; 
    $_SESSION['user_name'] = $firstname . " " . $lastname;
    $_SESSION['role'] = $role;
    header("Location: ../pationt-page.php");
}
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Email not registered or role mismatch.";
        }
    }

   
    $_SESSION['login_error'] = $error;
 echo "<script>
  localStorage.setItem('loginError', '" . addslashes($error) . "');
  window.location.href = '../LogIn.html';
</script>";
exit();

  
}
?>
