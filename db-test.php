<?php
$link = mysqli_connect('db', 'drupal', 'drupal', 'drupal');
if (!$link) {
  die('Could not connect: ' . mysqli_connect_error());
}
echo 'Connected successfully';
?>
