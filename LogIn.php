<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// اتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "root", "wecare", 8889);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';

    if (empty($email) || empty($password) || empty($role)) {
        $error = "Please fill all fields.";
    } else {
        // التحقق من المستخدم في قاعدة البيانات
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
}
?>


<!DOCTYPE html>
<html>
<head>
   <title>Login</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
   <link rel="stylesheet" type="text/css" href="css/login_style.css">
   <link rel="stylesheet" type="text/css" href="css/styling.css">
</head>
<body>
   <header class="header_section">
      <div class="container">
         <a href="HomePage.html">
            <img src="img/logo.png" alt="logo" class="header_logo">
         </a>
      </div>
   </header>
   
   <div class="container mt-5">
      <form method="post" class="login">
         <div class="form-group text-center">
            <h3>Login</h3>
         </div>

         <?php if (isset($error)) { echo "<div class='alert alert-danger text-center'>$error</div>"; } ?>

         <div class="form-group">
            <label for="email">Email Address:</label>
            <div class="input-group">
               <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
               </div>
               <input type="email" class="form-control" name="email" placeholder="Email Address" required>
            </div>
         </div>

         <div class="form-group">
            <label for="password">Password:</label>
            <div class="input-group">
               <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-key"></i></span>
               </div>
               <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
         </div>

         <div class="form-group">
            <label>Role:</label>
            <div>
               <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="role" value="patient" required>
                  <label class="form-check-label">Patient</label>
               </div>
               <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="role" value="doctor" required>
                  <label class="form-check-label">Doctor</label>
               </div>
            </div>
         </div>

         <div class="form-group text-center">
            <button type="submit" class="btn login_btn">Login</button>
         </div>
      </form>
   </div>
</body>
</html>
