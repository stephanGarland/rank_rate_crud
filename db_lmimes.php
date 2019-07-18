<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    $db_lmimes = oci_connect('MFG_IF', 'IFMIMFG01', 'SMIM');
    if (!$db_lmimes) {
        $e = oci_error();
        trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
    }
?>
