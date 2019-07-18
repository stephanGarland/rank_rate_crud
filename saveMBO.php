<?php
    header("Content-Type: application/json; charset=UTF-8");
    require 'db.php';

    if(isset($_POST['mboScore'])) {
        $mboScore = $_POST['mboScore'];
        $mboScore = json_decode($mboScore, true);
    }

    $empGHR = $mboScore[0];
    $quarter = $mboScore[1];
    $year = $mboScore[2];
    $overall_rating = $mboScore[3];
    $overall_numeric_rating = $mboScore[4];

    $insertMBOSql = "UPDATE public.review_ratings
                SET
                    overall_numeric_rating = $1,
                    overall_rating = $2
                WHERE
                    ghr_id = $3
                AND
                    quarter = $4
                AND
                    year = $5;
                ";


    $result = pg_prepare($db, "save_mbos", $insertMBOSql)
        or die (pg_last_error($db));
    $result = pg_execute($db, "save_mbos", array($overall_numeric_rating, $overall_rating, $empGHR, $quarter, $year))
        or die (pg_last_error($db));

    pg_close($db);
?>
