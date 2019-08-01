<?php
    require '../rank_rate_inc/db.php';
    $getSupSql = "SELECT DISTINCT
                public.review_employees_test.full_name AS employee
                FROM public.review_employees_test
                WHERE
                  public.review_employees_test.reports_to_name like $1
                  AND
                  (
                    (public.review_employees_test.title LIKE $2)
                    OR
                    (public.review_employees_test.title LIKE $3)
                  );
              ";


    $resultSups = pg_prepare($db, "get_sups", $getSupSql);
    // Modify this ($1) to meet your needs; I have it set up to find anyone who reports to my boss
    $resultSups = pg_execute($db, "get_sups", array('Bran Stark', '%Supervisor', '%TR'))
        or die (pg_last_error($db));
    $resultSupsArr = pg_fetch_all($resultSups)
        or die (pg_last_error($db));

    header('Content-Type: application/json');
    echo json_encode($resultSupsArr);
    pg_close($db);

?>
