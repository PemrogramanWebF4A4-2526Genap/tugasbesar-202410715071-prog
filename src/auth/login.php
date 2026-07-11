<?php

session_start();

require_once '../config/database.php';

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {

    $isPasswordValid = password_verify(
        $password,
        $user['password']
    );

    if ($isPasswordValid) {

        if ($user['status'] === 'suspended') {

            $_SESSION['error'] = 'Akun Anda telah disuspend admin.';
            
            header('Location: ../views/public/login.php');
            exit;

        }

        $_SESSION['user'] = $user;

        if (isset($_POST['remember'])) {

            setcookie(
                'remember_email',
                $user['email'],
                time() + (86400 * 30),
                '/'
            );

        } else {

            setcookie(
                'remember_email',
                '',
                time() - 3600,
                '/'
            );

        }

        if ($user['role'] == 'admin') {

            header('Location: ../views/admin/dashboard.php');

        } elseif ($user['role'] == 'seller') {

            header('Location: ../views/seller/dashboard.php');

        } else {

            header('Location: ../views/public/home.php');

        }

        exit;

    } else {
        $_SESSION['error'] = 'Password salah';
        header('Location: ../views/public/login.php');
        exit;
    }

} else {
    $_SESSION['error'] = 'Email tidak ditemukan';
    header('Location: ../views/public/login.php');
    exit;
}

$stmt->close();

?>