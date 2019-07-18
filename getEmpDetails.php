<?php
    require 'db.php';
    $empGHR = $_GET['urlGHR'];
    $curQuarter = $_GET['curQuarter'];
    $curYear = $_GET['curYear'];

    $getNameSql = "SELECT *
                FROM public.review_employees emp
                RIGHT JOIN public.review_ratings rating
                    ON emp.ghr_id = rating.ghr_id
                WHERE (
                        (emp.ghr_id = $1)
                        AND
                        (quarter::integer = $2)
                        AND
                        (year::integer = $3)
                    );
                ";

    $getAllRatingsSql = "SELECT overall_numeric_rating
                        FROM public.review_ratings
                        WHERE (
                            (ghr_id = $1)
                            AND
                            (year::integer = $2)
                        );
                ";

    $resultAll = pg_prepare($db, "get_data", $getNameSql);
    $resultAll = pg_execute($db, "get_data", array($empGHR, $curQuarter, $curYear))
        or die (pg_last_error($db));
    $resultAllArr = pg_fetch_all($resultAll)
        or die (pg_last_error($db));

    $resultAvgRating = pg_prepare($db, "get_ratings", $getAllRatingsSql);
    $resultAvgRating = pg_execute($db, "get_ratings", array($empGHR, $curYear))
        or die (pg_last_error($db));
    $resultRatingArr = pg_fetch_all($resultAvgRating)
        or die (pg_last_error($db));

    $nameResult = $resultAllArr[0]['full_name'];
    $ghrResult = $resultAllArr[0]['ghr_id'];
    $titleResult = $resultAllArr[0]['title'];
    $supGHR = $resultAllArr[0]['reports_to'];
    $supName = $resultAllArr[0]['reports_to_name'];
    $shiftRanking = $resultAllArr[0]['shift_ranking'];
    $overallRating = $resultAllArr[0]['overall_rating'];
    $overallRanking = $resultAllArr[0]['overall_ranking'];
    $writeup = $resultAllArr[0]['writeup'];
    $row_id = $resultAllArr[0]['id'];

    $runningAvg = 0.0;
    $i = 0;
    foreach ($resultRatingArr as $item) {
        if ($item['overall_numeric_rating'] == null) {
            unset($resultRatingArr[$i]);
        }
        $i++;
        $runningAvg += floatval($item['overall_numeric_rating']);
    }

    // In the event none exist for the year, the math will result in false or null, respectively.
    if (!is_numeric($runningAvg)) {
        $runningAvg = 0;
    }
    if (!is_numeric($overallRanking)) {
        $overallRanking = 0;
    }

    if (count($resultRatingArr) == 0) {
        $runningAvg = 0;
    }
    else {
        $runningAvg = $runningAvg / (count($resultRatingArr));
    }

    $resultJSON = [
    'full_name' => '<input id="empName" name="empName" type="text" value="' . $nameResult . '" readonly class="form-control">',
    'ghr_id' => '<input id="empGHR" name="empGHR" type="text" value="' . $ghrResult . '" readonly class="form-control">',
    'title' => '<input id="empTitle" name="empTitle" type="text" value="' . $titleResult . '" readonly class="form-control">',
    'reports_to_name' => '<input id="supName" name="supName" type="text" value="' . $supName . '" readonly class="form-control">',
    'reports_to' => $supGHR,
    'shift_ranking' => $shiftRanking,
    'overall_rating' => $overallRating,
    'overall_ranking' => $overallRanking,
    'writeup' => $writeup,
    'running_avg' => $runningAvg,
    'row_id' => $row_id
    ];


    header('Content-Type: application/json');
    echo json_encode($resultJSON);
    pg_close($db);
?>
