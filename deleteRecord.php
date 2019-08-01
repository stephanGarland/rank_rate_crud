<?php

    require '../rank_rate_inc/db.php';
    $row_id = $_POST['row_id'];
    $deleteRecordSql = "DELETE FROM public.review_ratings_test WHERE id = $row_id;";
    $result = pg_query($db, $deleteRecordSql);
    header('Content-Type: application/json');

?>
