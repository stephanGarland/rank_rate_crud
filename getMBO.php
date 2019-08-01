<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    require '../rank_rate_inc/db.php';
    $empGHR = $_GET['id'];

    $getRatingSql = "SELECT title, subgroup
                    FROM public.review_ratings_test
                    WHERE
                    ghr_id = $1
                    ORDER BY
                        year ASC,
                        quarter DESC
                    LIMIT 1;
                    ";

    // Insert your desired subgroup and MBO column names here
    $getRatedMBOSql = "SELECT
                rating_group_2_mbo_1,
                rating_group_2_mbo_2,
                rating_group_2_mbo_3,
                rating_group_2_mbo_4,
                rating_group_2_mbo_5,
                rating_group_1_mbo_1,
                rating_group_1_mbo_2,
                rating_group_1_mbo_3,
                rating_group_1_mbo_4,
                rating_group_1_mbo_5
                FROM public.review_ratings_test
                WHERE
                    ghr_id = $1
                AND
                    quarter = $2
                AND
                    year = $3;
                ";

    $resultTitle = pg_prepare($db, "get_rating", $getRatingSql);
    $resultTitle = pg_execute($db, "get_rating", array($empGHR));
    $resultTitleArr = pg_fetch_all($resultTitle);

    $subgroup = $resultTitleArr[0]['subgroup'];
    $title = $resultTitleArr[0]['title'];

    $getTemplateMBOSql = "SELECT
                    * FROM public.review_mbo_test
                    WHERE
                    worker_title = $1
                        AND
                    subgroup = $2
                    ";

    $resultTemplateMBO = pg_prepare($db, "get_mbos", $getTemplateMBOSql);
    $resultTemplateMBO = pg_execute($db, "get_mbos", array($title, $subgroup))
        or die (pg_last_error($db));
    $resultTemplateMBOArr = pg_fetch_all($resultTemplateMBO)
        or die (pg_last_error($db));

    header('Content-Type: application/json');
    echo json_encode($resultTemplateMBOArr);
    pg_close($db);
?>
