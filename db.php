<?php
$db = pg_connect('host=000.000.000.000 port=5432 user=your_user password=your_pass dbname=your_db connect_timeout=5')
    or die (pg_last_error($db));
if (!$db) {
  echo "An error occurred.\n";
  exit;
}

?>
