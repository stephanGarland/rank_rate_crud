<?php
    require '../rank_rate_inc/db.php';
    $ghr_id = $_GET['ghr_id'];
    $qtr = $_GET['qtr'];
    $year = $_GET['year'];
    $checkRecordSql = "SELECT
                ghr_id,
                quarter,
                year
                FROM public.review_ratings_test
                WHERE
                    ghr_id = $1
                AND
                    quarter = $2
                AND
                    year = $3;
                ";
    $result = pg_prepare($db, "getRecord", $checkRecordSql);
    $result = pg_execute($db, "getRecord", array($ghr_id, $qtr, $year))
        or die (pg_last_error($db));
    $resultArr = pg_fetch_all($result)
        or die (pg_last_error($db));
    header('Content-Type: application/json');
    echo json_encode($resultArr);
    pg_close($db);

  ?>
