<?php
session_start();

require_once __DIR__ . '/../BL/auth_service.php';

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $user_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Server-side validation in Business Layer
    $validationErrors = validateRegister($first_name, $last_name, $email, $student_id, $user_password, $confirm_password);

    if (!empty($validationErrors)) {
        // show first error
        $error_message = reset($validationErrors);
    } else {

          $servername = "192.168.100.10";
          $username = "remote_user";
          $password = "Aa123";
          $dbname = "tasksdb";
          $port = 3306;


        $conn = new mysqli($servername, $username, $password, $dbname, $port);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // hash password
        $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
        

          $uniqueErrors = validateUserUniqueness($conn, $email, $student_id);

      if (!empty($uniqueErrors)) {
          $error_message = reset($uniqueErrors);
      } else {
          // insert user
          $stmt = $conn->prepare("INSERT INTO users 
              (first_name, last_name, email, student_id, password_hash) 
              VALUES (?, ?, ?, ?, ?)");

          $stmt->bind_param("sssss", $first_name, $last_name, $email, $student_id, $hashed_password);

          if ($stmt->execute()) {
              $success_message = "Account created successfully! You can now log in.";
              $_POST = array();
          } else {
              $error_message = "Error: " . $stmt->error;
          }

        $conn->close();
      }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register • Student Task Tracker</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar">
  <div class="inner container">
    <div class="brand"><span class="dot"></span> Student Task Tracker</div>
    <div class="stack">
      <a href="home.html">Home</a>
      <a href="login.php">Login</a>
      <a href="register.php" class="active">Register</a>
      <a href="data_entry.php">New Task</a>
      <a href="view_data.php">Tasks</a>
    </div>
  </div>
</div>

  <div class="container">
    
  <h2>Create Account</h2>
  <?php if ($success_message): ?>
      <p style="color: green; text-align: center;"><?php echo $success_message; ?></p>
  <?php endif; ?>
  <?php if ($error_message): ?>
      <p style="color: red; text-align: center;"><?php echo $error_message; ?></p>
  <?php endif; ?>

  <form id="registerForm" action="register.php" method="post" class="grid" novalidate>
    <div class="row-2">
      <input class="input" id="first_name" placeholder="First name" name="first_name" required minlength="2" maxlength="50">
      <input class="input" id="last_name" placeholder="Last name" name="last_name" required minlength="2" maxlength="50">
    </div>
    <div class="row-2">
      <input class="input" id="email" type="email" placeholder="you@university.edu" name="email" required>
      <input class="input" id="student_id" placeholder="Student ID" name="student_id" required maxlength="20">
    </div>
    <div class="row-2">
      <input class="input" id="password" type="password" placeholder="Password" name="password" required minlength="6">
      <input class="input" id="confirm_password" type="password" placeholder="Confirm Password" name="confirm_password" required minlength="6">
    </div>
    <div class="actions">
      <button class="btn" type="submit">Create Account</button>
    </div>
  </form>
  <p class="small" id="registerError" style="text-align: center; color:#c81e1e; display:none;">
    Please fix the highlighted fields before submitting.
  </p>
  <p class="small" style="text-align: center;">Already have an account? <a href="login.php">Log in here</a>.</p>
  </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registerForm");
  if (!form) return;

  const firstName = document.getElementById("first_name");
  const lastName = document.getElementById("last_name");
  const email = document.getElementById("email");
  const studentId = document.getElementById("student_id");
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirm_password");
  const errorMsg = document.getElementById("registerError");

  form.addEventListener("submit", function (e) {
    let valid = true;
    [firstName, lastName, email, studentId, password, confirmPassword].forEach(f => {
      f.style.borderColor = "";
    });

    if (firstName.value.trim().length < 2) {
      firstName.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (lastName.value.trim().length < 2) {
      lastName.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (!email.value.includes("@") || !email.value.includes(".")) {
      email.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (studentId.value.trim().length === 0) {
      studentId.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (password.value.length < 6) {
      password.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (password.value !== confirmPassword.value) {
      confirmPassword.style.borderColor = "#c81e1e";
      valid = false;
    }

    if (!valid) {
      if (errorMsg) errorMsg.style.display = "block";
      e.preventDefault();
    } else {
      if (errorMsg) errorMsg.style.display = "none";
    }
  });
});
</script>

</body>
</html>