<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// اتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "root", "wecare", 8889);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// التحقق من الطلب
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
    $lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $password = isset($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_DEFAULT) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : NULL;
    $dob = isset($_POST['dob']) ? trim($_POST['dob']) : NULL;
    $speciality = isset($_POST['speciality']) ? trim($_POST['speciality']) : NULL;


    $checkStmt = $conn->prepare("SELECT id FROM patient WHERE id = ? UNION SELECT id FROM doctor WHERE id = ?");
    $checkStmt->bind_param("ss", $id, $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        die("<script>alert('Error: ID already exists. Please use a different ID.');</script>");
    }

   
$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true); 
}

// تحميل الصورة للطبيب فقط
$profile_picture = "";
if ($role == "Doctor" && isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
    $allowedExtensions = ["jpg", "jpeg", "png", "gif"];
    $image_extension = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));

    if (!in_array($image_extension, $allowedExtensions)) {
        die("<script>alert('Invalid file type. Please upload an image file.');</script>");
    }

    $uniqueFileName = uniqid() . "." . $image_extension;
    $target_file = $target_dir . $uniqueFileName;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        $profile_picture = $uniqueFileName;
    } else {
        die("<script>alert('File upload failed.');</script>");
    }
}


 
if ($role == "Patient") {
    $stmt = $conn->prepare("INSERT INTO patient (id, firstName, lastName, Gender, DoB, emailAddress, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $id, $firstname, $lastname, $gender, $dob, $email, $password);
    $redirectPage = "pationt-page.html"; 
} elseif ($role == "Doctor") {
    $stmt = $conn->prepare("INSERT INTO doctor (id, firstName, lastName, uniqueFileName, SpecialityID, emailAddress, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $id, $firstname, $lastname, $profile_picture, $speciality, $email, $password);
    $redirectPage = "Doctor’s-Page.html"; 
} else {
    die("<script>alert('Invalid role selected.');</script>");
}

if ($stmt->execute()) {
    echo "<script>alert('Registration successful!'); window.location='$redirectPage';</script>";
} else {
    echo "<script>alert('Error: " . $stmt->error . "');</script>";
}

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sign Up</title>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
   <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
   <link rel="stylesheet" type="text/css" href="css/signup_style.css">
   <link rel="stylesheet" type="text/css" href="css/styling.css">
   <link rel="shortcut icon" href="img/icon.png" type="image/png">
   <script>
      function toggleForm(role) {
         document.getElementById("patientForm").style.display = role === "Patient" ? "block" : "none";
         document.getElementById("doctorForm").style.display = role === "Doctor" ? "block" : "none";
      }
   </script>
</head>
<body>
   <header class="header_section">
      <div class="container">
         <a href="HomePage.html">
            <img src="img/logo.png" alt="logo" class="header_logo">
         </a>
      </div>
   </header>
   
   <div class="signup">
      <h3 class="text-center">Sign Up</h3>

      <div class="form-group text-center">
         <label for="roleSelection">Role:</label>
         <div id="roleSelection">
            <input type="radio" name="role" id="rolePatient" value="Patient" onclick="toggleForm('Patient')" checked>
            <label for="rolePatient">Patient</label>
            <input type="radio" name="role" id="roleDoctor" value="Doctor" onclick="toggleForm('Doctor')">
            <label for="roleDoctor">Doctor</label>
         </div>
      </div>

      <!-- Patient Form -->
      <form id="patientForm" method="POST" action="signup.php" class="mt-4">
         <input type="hidden" name="role" value="Patient">
         <div class="form-row">
            <div class="col">
               <div class="input-group">
                  <div class="input-group-prepend">
                     <span class="input-group-text"><i class="fas fa-user"></i></span>
                  </div>
                  <input type="text" class="form-control" name="firstname" placeholder="First Name" required>
               </div>
            </div>
            <div class="col">
               <div class="input-group">
                  <div class="input-group-prepend">
                     <span class="input-group-text"><i class="fas fa-user"></i></span>
                  </div>
                  <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
               </div>
            </div>
         </div>
         <div class="form-group mt-3">
            <div class="input-group">
               <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-id-card"></i></span>
               </div>
               <input type="text" class="form-control" name="id" placeholder="ID" required>
            </div>
         </div>
         <div class="form-group">
            <div class="input-group">
               <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
               </div>
               <select class="form-control" name="gender">
                  <option selected hidden disabled value="">Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
               </select>
            </div>
         </div>
         <div class="form-group">
            <div class="input-group">
               <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
               </div>
               <input type="date" class="form-control" name="dob">
            </div>
         </div>
         <div class="form-group">
            <div class="input-group">
               <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
               </div>
               <input type="email" class="form-control" name="email" placeholder="Email Address" required>
            </div>
         </div>
         <div class="form-group">
            <div class="input-group">
               <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-lock"></i></span>
               </div>
               <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
         </div>
         <div class="text-center">
            <button type="submit" class="btn signup_btn">Register</button>
         </div>
     </form>

      <!-- Doctor Form -->
     <!-- Doctor Form -->
<form id="doctorForm" method="POST" action="signup.php" enctype="multipart/form-data" class="mt-4" style="display: none;">
   <input type="hidden" name="role" value="Doctor">
   <div class="form-row">
      <div class="col">
         <div class="input-group">
            <div class="input-group-prepend">
               <span class="input-group-text"><i class="fas fa-user"></i></span>
            </div>
            <input type="text" class="form-control" name="firstname" placeholder="First Name" required>
         </div>
      </div>
      <div class="col">
         <div class="input-group">
            <div class="input-group-prepend">
               <span class="input-group-text"><i class="fas fa-user"></i></span>
            </div>
            <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
         </div>
      </div>
   </div>
   <div class="form-group mt-3">
      <div class="input-group">
         <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
         </div>
         <input type="text" class="form-control" name="id" placeholder="ID" required>
      </div>
   </div>
   <div class="form-group">
      <div class="input-group">
         <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-camera"></i></span>
         </div>
         <input type="file" class="form-control-file" name="photo" required>
      </div>
   </div>
   <div class="form-group">
      <div class="input-group">
         <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
         </div>
         <select class="form-control" name="speciality" required>
            <option selected hidden disabled value="">Speciality</option>
            <option value="1">Cardiology</option>
            <option value="2">Dentistry</option>
            <option value="3">Dermatology</option>
         </select>
      </div>
   </div>
   <!-- Email Field -->
   <div class="form-group">
      <div class="input-group">
         <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
         </div>
         <input type="email" class="form-control" name="email" placeholder="Email Address" required>
      </div>
   </div>
   <!-- Password Field -->
   <div class="form-group">
      <div class="input-group">
         <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
         </div>
         <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
   </div>
   <div class="text-center">
      <button type="submit" class="btn signup_btn">Register</button>
   </div>
</form>

   </div>
</body>
</html>
