<?php

$label = $label ?? 'Input';
$name = $name ?? 'input';
$type = $type ?? 'text';
$placeholder = $placeholder ?? '';
$required = $required ?? false;
$value = $value ?? '';

?>

<div class="mb-5">

  <label class="block mb-2 font-medium text-gray-700">

    <?= htmlspecialchars($label) ?>

  </label>

  <input
    type="<?= $type ?>"
    name="<?= $name ?>"
    value="<?= htmlspecialchars($value) ?>"
    placeholder="<?= htmlspecialchars($placeholder) ?>"
    <?= $required ? 'required' : '' ?>
    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
  >

</div>