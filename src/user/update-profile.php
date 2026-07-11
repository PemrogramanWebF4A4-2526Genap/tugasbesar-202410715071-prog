<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../views/public/login.php');
    exit;
}

$userId = intval($_SESSION['user']['id']);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

if ($name === '' || $email === '') {
    header('Location: ../views/profile.php?error=1');
    exit;
}

$emailEsc = mysqli_real_escape_string($conn, $email);
$nameEsc = mysqli_real_escape_string($conn, $name);

$checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email='$emailEsc' AND id!='$userId' LIMIT 1");
if (mysqli_num_rows($checkEmail) > 0) {
    header('Location: ../views/profile.php?error=1');
    exit;
}

$profileImage = '';

$currentUser = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT profile_image FROM users WHERE id='$userId' LIMIT 1"
    )
);

$currentProfile = $currentUser['profile_image'] ?? '';

$updateFields = "name='$nameEsc', email='$emailEsc'";
if ($password !== '') {
    if ($password !== $passwordConfirm) {
        header('Location: ../views/profile.php?error=1');
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $updateFields .= ", password='" . mysqli_real_escape_string($conn, $hash) . "'";
}

if (!empty($_FILES['profile_image']['name'])) {

    $allowed = ['image/png', 'image/jpg', 'image/jpeg'];

    if (!in_array($_FILES['profile_image']['type'], $allowed)) {

        header('Location: ../views/profile.php?error=1');
        exit;

    }

    if ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) {

        header('Location: ../views/profile.php?error=1');
        exit;

    }

    $ext = strtolower(
        pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION)
    );

    $profileImage = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

    $uploadPath = __DIR__ . '/../uploads/sellers/' . $profileImage;

    if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {

        header('Location: ../views/profile.php?error=1');
        exit;

    }

    if (!empty($currentProfile)) {

        $oldPath = __DIR__ . '/../uploads/sellers/' . $currentProfile;

        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }

    }

    $updateFields .= ", profile_image='" . mysqli_real_escape_string($conn, $profileImage) . "'";

}

$updateSql = "UPDATE users SET $updateFields WHERE id='$userId'";
if (!mysqli_query($conn, $updateSql)) {
    header('Location: ../views/profile.php?error=1');
    exit;
}

$newUser = mysqli_query($conn, "SELECT * FROM users WHERE id='$userId' LIMIT 1");
if ($newUser && mysqli_num_rows($newUser) > 0) {
    $_SESSION['user'] = mysqli_fetch_assoc($newUser);
}

header('Location: ../views/profile.php?updated=1');
exit;
