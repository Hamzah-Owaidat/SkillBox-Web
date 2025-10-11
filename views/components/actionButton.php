<?php
function actionButton($type, $url, $label = null) {
  $icons = [
    'add' => '<i class="bi bi-plus-circle"></i>',
    'edit' => '<i class="bi bi-pencil-square"></i>',
    'delete' => '<i class="bi bi-trash"></i>',
  ];

  $colors = [
    'add' => 'btn-success',
    'edit' => 'btn-warning',
    'delete' => 'btn-danger',
  ];

  $labelText = $label ?? ucfirst($type);
  $icon = $icons[$type] ?? '';
  $color = $colors[$type] ?? 'btn-secondary';

  echo "
    <a href='{$url}' class='btn btn-sm {$color} d-inline-flex align-items-center gap-1'>
      {$icon} {$labelText}
    </a>
  ";
}
