<?php
    header("Content-Type: application/json");
    require '../rank_rate_inc/db.php';

    $insertShiftRankSql = "INSERT into public.review_ratings_test(full_name, quarter, year, shift_ranking)
                        VALUES($1, $2, $3, $4)
                        ON conflict (full_name, quarter, year)
                        DO
                            UPDATE
                                SET shift_ranking=EXCLUDED.shift_ranking;
                        ";

    if(isset($_POST['shiftRankData'])) {
        $rankArray = $_POST['shiftRankData'];
        $rankArray = json_decode($rankArray, true);
    }

    // Last two elements are quarter, year - pop them out so the SQL statement doesn't have to be changed, and we don't have to re-caclulate them

    $year = array_pop($rankArray);
    $quarter = array_pop($rankArray);
    $year = $year['year'];
    $quarter = $quarter['quarter'];

    foreach ($rankArray as $element) {
        $empName = $element[0];
        $shift_rank = $element[1];
        // Note that due to the for loop, named statements such as "insert_shift_rank" are not possible, since the 2nd iteration would throw an error
        $result = pg_prepare($db, "", $insertShiftRankSql)
            or die (pg_last_error($db));
        $result = pg_execute($db, "", array($empName, $quarter, $year, $shift_rank))
            or die (pg_last_error($db));
    }

    pg_close($db);
?>
