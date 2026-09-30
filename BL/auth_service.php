<?php

/**
 * Business Layer: Authentication and registration validation.
 * This layer contains all server-side validation logic.
 */

function validateLogin(string $email, string $password): array {
    $errors = [];

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    return $errors;
}

function validateRegister(
    string $first_name,
    string $last_name,
    string $email,
    string $student_id,
    string $password,
    string $confirm_password
): array {
    $errors = [];

   // sanitize
    $first_name = trim($first_name);
    $last_name  = trim($last_name);
    $email      = trim($email);
    $student_id = trim($student_id);

    if ($first_name === '') {
        $errors[] = "First name is required.";
    }
    if ($last_name === '') {
        $errors[] = "Last name is required.";
    }
    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if ($student_id === '') {
        $errors[] = "Student ID is required.";
    }

    if ($password === '') {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

   
    return $errors;
}
function validateUserUniqueness($conn, $email, $student_id)
{
    $errors = [];

    $email      = trim($email);
    $student_id = trim($student_id);

    if (!$conn || $conn->connect_error) {
        $errors[] = "Database connection problem.";
        return $errors;
    }

    $stmt = $conn->prepare("SELECT email, student_id FROM users WHERE email = ? OR student_id = ?");
    if (!$stmt) {
        $errors[] = "Internal error: failed to prepare query.";
        return $errors;
    }

    $stmt->bind_param("ss", $email, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if ($row['email'] === $email) {
            $errors[] = "An account with this email already exists.";
        }
        if ($row['student_id'] === $student_id) {
            $errors[] = "An account with this student ID already exists.";
        }
    }

    $stmt->close();

    return $errors;
}
?>
