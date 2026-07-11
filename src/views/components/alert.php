<?php

$alertType = $alertType ?? 'success';
$message = $message ?? 'Alert message';

$styles = [
  'success' => 'bg-green-100 text-green-700 border-green-300',
  'error' => 'bg-red-100 text-red-700 border-red-300',
  'warning' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
];

$class = $styles[$alertType] ?? $styles['success'];

?>

<div class="border px-4 py-3 rounded-xl <?= $class ?>">

  <?= $message ?>

</div>