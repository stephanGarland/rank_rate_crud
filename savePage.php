<?php
    header("Content-Type: application/json");
    require 'db.php';

    $insertRatingsSql = "INSERT INTO public.review_ratings(
                        ghr_id, overall_rating, pos_watch,
                        neg_watch, ean_pa, ean_pip,
                        ean_verbal, ean_written, ean_final,
                        succession, promo, quarter, year
                    )
                  VALUES(
                      $1, $2, $3, $4, $5, $6, $7,
                      $8, $9, $10, $11, $12, $13
                  )
                  ON CONFLICT (ghr_id, quarter, year)
                  DO
                      UPDATE
                         SET pos_watch=EXCLUDED.pos_watch,
                             neg_watch=EXCLUDED.neg_watch,
                             ean_pa=EXCLUDED.ean_pa,
                             ean_pip=EXCLUDED.ean_pip,
                             ean_verbal=EXCLUDED.ean_verbal,
                             ean_written=EXCLUDED.ean_written,
                             ean_final=EXCLUDED.ean_final,
                             succession=EXCLUDED.succession,
                             promo=EXCLUDED.promo,
                             overall_rating=EXCLUDED.overall_rating;
                  ";

    if(isset($_POST['pageData'])) {
        $pageData = $_POST['pageData'];
        $pageData = json_decode($pageData, true);
    }

    $ghr_id = $pageData[0];
    $overall_rating = $pageData[1];
    $pos_watch = $pageData[2];
    $neg_watch = $pageData[3];
    $ean_pa = $pageData[4];
    $ean_pip = $pageData[5];
    $ean_verbal = $pageData[6];
    $ean_written = $pageData[7];
    $ean_final = $pageData[8];
    $succession = $pageData[9];
    $promo = $pageData[10];
    $quarter = $pageData[11];
    $year = $pageData[12];

    $result = pg_prepare($db, "insert_ratings", $insertRatingsSql)
        or die (pg_last_error($db));
    $result = pg_execute($db, "insert_ratings", array
        (
            $ghr_id,
            $overall_rating,
            $pos_watch,
            $neg_watch,
            $ean_pa,
            $ean_pip,
            $ean_verbal,
            $ean_written,
            $ean_final,
            $succession,
            $promo,
            $quarter, 
            $year
            )
        )
        or die (pg_last_error($db));

    pg_close($db);

?>
