<?php

$type = $type ?? 'primary';
$text = $text ?? 'Button';

$baseClass = "px-5 py-3 rounded-xl font-medium transition duration-200";

$styles = [
  'primary' => 'bg-emerald-500 hover:bg-emerald-600 text-white',
  'secondary' => 'border border-gray-300 text-gray-700 hover:bg-gray-100',
  'danger' => 'bg-red-500 hover:bg-red-600 text-white'
];

$class = $styles[$type] ?? $styles['primary'];

?>

<button class="<?= $baseClass . ' ' . $class ?>">
  <?= $text ?>
</button>