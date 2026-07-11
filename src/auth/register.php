<?php

require_once '../config/database.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Prepared statement
$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, role)
     VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    header('Location: ../views/public/login.php');
    exit;
} else {
    echo "Register gagal: " . $stmt->error;
}

$stmt->close();