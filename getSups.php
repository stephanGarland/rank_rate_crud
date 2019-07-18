<?php
    require 'db.php';
    $getSupSql = "SELECT DISTINCT
                public.review_employees.full_name AS employee
                FROM public.review_employees
                WHERE
                  public.review_employees.reports_to_name like $1
                  AND
                  (
                    (public.review_employees.title LIKE $2)
                    OR
                    (public.review_employees.title LIKE $3)
                  );
              ";


    $resultSups = pg_prepare($db, "get_sups", $getSupSql);
    // Modify this to meet your needs; I have it set up to find anyone who reports to my boss
    $resultSups = pg_execute($db, "get_sups", array('BIG_BOSS', '%Supervisor', '%TR'))
        or die (pg_last_error($db));
    $resultSupsArr = pg_fetch_all($resultSups)
        or die (pg_last_error($db));

    header('Content-Type: application/json');
    echo json_encode($resultSupsArr);
    pg_close($db);

?>
