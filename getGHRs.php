<?php
    require '../rank_rate_inc/db.php';
    $sup = $_POST['sup_name'];
    $qtr = $_POST['qtr'];
    $year = $_POST['year'];

    /*
        If the user has deleted records, this will allow the bulk adder
        to provide a template, based on the last quarter
    */
    if ($qtr == '1') {
        $year = strval(intval($year) - 1);
        $qtr = '4';
    }
    else {
        $qtr = strval(intval($qtr) - 1);
    }

    $getGHRSql = "SELECT DISTINCT ON (ghr_id)
                ghr_id
                FROM public.review_ratings_test
                WHERE
                    reports_to_name LIKE $1
                AND
                    quarter LIKE $2
                AND
                    year LIKE $3;
                ";

    $resultGHRs = pg_prepare($db, "get_ghrs", $getGHRSql);
    $resultGHRs = pg_execute($db, "get_ghrs", array($sup, $qtr, $year))
        or die (pg_last_error($db));
    $resultGHRsArr = pg_fetch_all($resultGHRs)
        or die (pg_last_error($db));
    header('Content-Type: application/json');
    echo json_encode($resultGHRsArr);
    pg_close($db);
?>
