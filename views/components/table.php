<?php
function renderTable($headers, $rows, $actions = false) {
  echo "<div class='table-responsive'>";
  echo "<table class='table table-striped align-middle shadow-sm'>";
  echo "<thead class='table-dark'><tr>";

  foreach ($headers as $header) {
    echo "<th>{$header}</th>";
  }

  if ($actions) {
    echo "<th class='text-center'>Actions</th>";
  }

  echo "</tr></thead><tbody>";

  foreach ($rows as $row) {
    echo "<tr>";
    foreach ($row as $cell) {
      echo "<td>{$cell}</td>";
    }

    if ($actions && isset($row['id'])) {
      echo "<td class='text-center'>";
      actionButton('edit', '/dashboard/users/edit/' . $row['id']);
      echo " ";
      actionButton('delete', '/dashboard/users/delete/' . $row['id']);
      echo "</td>";
    }

    echo "</tr>";
  }

  echo "</tbody></table></div>";
}
