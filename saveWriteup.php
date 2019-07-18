<?php
    header("Content-Type: application/json");
    require 'db.php';

    $insertWriteupSql = "INSERT into public.review_ratings(ghr_id, full_name, quarter, year, writeup)
                    VALUES($1, $2, $3, $4, $5)
                    ON conflict (ghr_id, quarter, year)
                    DO
                        UPDATE
                    	   SET writeup=EXCLUDED.writeup;
                    ";

    if(isset($_POST['writeupData'])) {
        $writeupData = $_POST['writeupData'];
        $writeupData = json_decode($writeupData, true);
    }

    $empGHR = $writeupData[0];
    $empName = $writeupData[1];
    $quarter = $writeupData[2];
    $year = $writeupData[3];
    $writeup = $writeupData[4];


    $result = pg_prepare($db, "insert_writeup", $insertWriteupSql)
        or die (pg_last_error($db));
    $result = pg_execute($db, "insert_writeup", array($empGHR, $empName, $quarter, $year, $writeup))
        or die (pg_last_error($db));

    pg_close($db);
?>
