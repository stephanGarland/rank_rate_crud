<?php
    require '../rank_rate_inc/db.php';
    $supGHR = $_GET['selectedSup'];
    $supGHR = json_decode($supGHR, false);
    $getSummarySql = "SELECT
              full_name,
              ghr_id,
              title,
              subgroup,
              shift,
              quarter,
              year,
              writeup,
              overall_rating,
              overall_numeric_rating,
              shift_ranking,
              overall_ranking,
              ean_items,
              pos_watch,
              neg_watch,
              succession,
              promo
              FROM public.review_ratings_test
              WHERE
              (
                  (reports_to_name LIKE $1)
                  AND
                  (public.review_ratings_test.hidden=false)
              )
              ORDER by subgroup DESC, array_position(
                  array[
                      'Technician I',
                      'Technician II',
                      'Senior Technician',
                      'Master Technician',
                      'Engineer I',
                      'Engineer II',
                      'Senior Engineer'
                  ]::varchar[], title),
                  full_name;
              ";


    $result = pg_prepare($db, "get_summary", $getSummarySql)
        or die (pg_last_error($db));
    $result = pg_execute($db, "get_summary", array($supGHR))
        or die (pg_last_error($db));
    $resultSummary = pg_fetch_all($result);

    header('Content-Type: application/json');
    echo json_encode($resultSummary, JSON_HEX_APOS | JSON_NUMERIC_CHECK);
    pg_close($db);
?>
