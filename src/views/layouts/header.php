<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/UAS_INFO2425_202410715071_Muhammad_Abdika');
}
if (!defined('UPLOAD_URL')) {
  define('UPLOAD_URL', BASE_URL . '/src/uploads');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>UMKM Marketplace</title>

  <!-- Tailwind CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/src/assets/css/output.css">

  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/src/assets/images/favicon.png">

  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="bg-gray-100 text-gray-800">
<?php include __DIR__ . '/navbar.php'; ?>