<?php
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
  header('Location: novel.php?id=' . $id);
} else {
  header('Location: Dashboard.php?page=Library');
}
exit();