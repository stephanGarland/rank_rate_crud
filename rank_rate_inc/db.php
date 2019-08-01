<?php
$db = pg_connect('host=YOUR_HOST port=5432 user=YOUR_USER password=YOUR_PASS dbname=YOUR_DB connect_timeout=5')
    or die (pg_last_error($db));
if (!$db) {
  echo "An error occurred.\n";
  exit;
}

?>
