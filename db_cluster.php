<?php
$db_cluster = mssql_connect('sqlcluster', 'WorkflowSQLUser', 't2*Zax60s')
    or die (pg_last_error($db_cluster));
if (!$db_cluster) {
  echo "An error occurred.\n";
  exit;
}

?>
