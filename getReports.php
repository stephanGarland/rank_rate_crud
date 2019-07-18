<?php
  require 'db.php';
  if (isset($_POST['supChoice'])) {
      $selectedSup = $_POST['supChoice'];
      $qtr = $_POST['qtr'];
      $year = $_POST['year'];
      $getReportsSql =  "SELECT DISTINCT ON
                    (subgroup, full_name)
                    ghr_id,
                    full_name,
                    subgroup,
                    title,
                    term_date,
                    reports_to_name
                    FROM public.review_ratings
                    WHERE (
                        (dept_name LIKE $1)
                    AND
                        (reports_to_name LIKE $2)
                    AND
                        (title NOT LIKE $3)
                    AND
                        (quarter LIKE $4)
                    AND
                        (year LIKE $5)
                    )
                    ORDER BY subgroup, full_name;
                    ";

          $resultEmps = pg_prepare($db, "get_reports", $getReportsSql)
            or die (pg_last_error($db));
          $resultEmps = pg_execute($db, "get_reports", array('Clean%', $selectedSup, '%Associate%', $qtr, $year))
            or die (pg_last_error($db));
          $resultEmpsArr = pg_fetch_all($resultEmps);
      }
      header('Content-Type: application/json');
      echo json_encode($resultEmpsArr);
      pg_close($db);
?>
