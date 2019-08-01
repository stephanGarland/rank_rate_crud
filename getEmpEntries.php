<?php

    require '../rank_rate_inc/db.php';
    $supGHR = $_POST['reports_to'];
    $curQuarter = $_POST['curQuarter'];
    $curYear = $_POST['curYear'];
    $subSel = '%';

    $getDetailsSql = "SELECT
                    full_name,
                    ghr_id,
                    subgroup,
                    reports_to,
                    quarter,
                    year,
                    overall_rating,
                    shift_ranking,
                    ean_pa,
                    ean_pip,
                    ean_verbal,
                    ean_written,
                    ean_final,
                    pos_watch,
                    neg_watch,
                    succession,
                    promo
                    FROM public.review_ratings_test
                    WHERE
                        (reports_to_name = $1
                    OR
                        reports_to::varchar = $1)
                    AND
                        quarter = $2
                    AND
                        year = $3
                    AND
                        subgroup LIKE $4
                    ORDER by subgroup desc, full_name;
                    ";

    $result = pg_prepare($db, "get_review", $getDetailsSql)
        or die (pg_last_error($db));
    $result = pg_execute($db, "get_review", array($supGHR, $curQuarter, $curYear, $subSel))
        or die (pg_last_error($db));
    $resultArr = pg_fetch_all($result);
    header('Content-Type: application/json');
    echo json_encode($resultArr);
    pg_close($db);

?>
