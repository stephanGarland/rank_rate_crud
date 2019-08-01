<?php
    header("Content-Type: application/json");
    require '../rank_rate_inc/db.php';

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    $ghr_id = $_POST['ghr_id'];
    $new_sup_name = $_POST['new_sup_name'];
    $cur_sup_name = $_POST['cur_sup_name'];
    $quarter = $_POST['quarter'];
    $year = $_POST['year'];
    $bulk_bool = $_POST['bulk_bool'];

    switch ($bulk_bool) {
        case 'true':
            $sqlEmpBulk = "UPDATE public.review_employees_test
                            SET reports_to = (
                                SELECT ghr_id FROM public.review_employees_test
                                WHERE full_name = $2
                            ),
                            reports_to_name = $1
                            WHERE
                                reports_to_name = $2
                            ;"
            ;
            $result = pg_prepare($db, "update_emp_bulk", $sqlEmpBulk)
                or die (pg_last_error($db));
            $result = pg_execute($db, "update_emp_bulk", array($new_sup_name, $cur_sup_name))
                or die (pg_last_error($db));

            $sqlRatingBulk = "UPDATE public.review_ratings_test
                            SET reports_to = (
                                SELECT ghr_id FROM public.review_employees_test
                                WHERE full_name = $2
                            ),
                            reports_to_name = $1
                            WHERE
                            (
                                (reports_to_name = $2)
                                AND
                                (quarter = $3)
                                AND
                                (year = $4)

                            );"
            ;
            $result = pg_prepare($db, "update_rating_bulk", $sqlRatingBulk)
                or die (pg_last_error($db));
            $result = pg_execute($db, "update_rating_bulk", array($new_sup_name, $cur_sup_name, $quarter, $year))
                or die (pg_last_error($db));
        break;

    case 'false':
            $sqlEmp = "UPDATE public.review_employees_test
                        SET reports_to = (
                            SELECT ghr_id FROM public.review_employees_test
                            WHERE full_name = $2
                        ),
                        reports_to_name = $2
                        WHERE
                        ghr_id = $1;"
            ;
            $result = pg_prepare($db, "update_emp", $sqlEmp)
                or die (pg_last_error($db));
            $result = pg_execute($db, "update_emp", array($ghr_id, $new_sup_name))
                or die (pg_last_error($db));



            $sqlRating = "UPDATE public.review_ratings_test
                            SET reports_to = (
                                SELECT ghr_id FROM public.review_employees_test
                                WHERE full_name = $2
                            ),
                            reports_to_name = $2
                            WHERE
                            (
                                (ghr_id = $1)
                                AND
                                (quarter = $3)
                                AND
                                (year = $4)
                            );"
            ;
            $result = pg_prepare($db, "update_rating", $sqlRating)
                or die (pg_last_error($db));
            $result = pg_execute($db, "update_rating", array($ghr_id, $new_sup_name, $quarter, $year))
                or die (pg_last_error($db));
        break;
    }
    pg_close($db);
?>
