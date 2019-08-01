<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    header("Content-Type: application/json");
    require '../rank_rate_inc/db.php';

    define("SHIFT_RANKING_DEFAULT", 99);
    $qtr = $_POST['qtr'];
    $year = $_POST['year'];

    if (isset($_POST['ghr_id'])) {
        $ghr_id = $_POST['ghr_id'];
    }

    $bulk_add = false;
    if (isset($_POST['shift_ghrs'])) {
        $shift_ghrs = $_POST['shift_ghrs'];
        $shift_ghrs = json_decode($shift_ghrs);
        $bulk_add = true;
    }

    // Modify as desired
    $group_1 = "'MBO #1:\x20\nMBO #2:\x20\nMBO #3:\x20\nMBO #4:\x20\nMBO#5\x20'";

    $group_2 = "'MBO #1:\x20\nMBO #2:\x20\nMBO #3:\x20\nMBO #4:\x20\nMBO#5\x20'";

    // Added term_date so terminated employees aren't added to new quarters when doing a bulk add
    $insertRecordSql = "INSERT INTO public.review_ratings_test
                                	(
                                		ghr_id,
                                		full_name,
                                		title,
                                		dept_name,
                                		reports_to,
                                		reports_to_name,
                                		shift,
                                        shift_ranking,
                                		quarter,
                                		year,
                                        pos_watch,
                                        neg_watch,
                                        succession,
                                        promo,
                                		subgroup,
                                        hidden,
                                        ean_verbal,
                                        ean_written,
                                        ean_final,
                                        ean_pa,
                                        ean_pip,
                                        ean_items,
                                		writeup,
                                        term_date
                                	)
                                SELECT
                                		ghr_id,
                                		full_name,
                                		title,
                                		dept_name,
                                		reports_to,
                                		reports_to_name,
                                		shift,
                                        $1,         --SHIFT_RANKING_DEFAULT
                                		$2,         --quarter
                                		$3,         --year
                                    	false,      --pos_watch
                                    	false,      --neg_watch
                                    	false,      --succession
                                    	false,      --promo
                                		subgroup,
                                		false,      --hidden
                                		false,      --ean_vebal
                                		false,      --ean_written
                                		false,      --ean_final
                                		false,      --ean_pa
                                		false,      --ean_pip
                                		'None',     --ean_items
                                		CASE
                                        	WHEN subgroup = 'Group_1' THEN $group_1
                                    		WHEN subgroup = 'Group_2' THEN $group_2
                                    	END,
                                        term_date
                                FROM public.review_ratings_test
                                WHERE
                                    ghr_id = $4
                                AND
                                    term_date IS NULL
                                ORDER BY year DESC, quarter DESC
                                LIMIT 1;
                                ";

    $insertResult = pg_prepare($db, "insertNew", $insertRecordSql);
    if ($bulk_add == false) {
        $insertResult = pg_execute($db, "insertNew", array(SHIFT_RANKING_DEFAULT, $qtr, $year, $ghr_id));
    }
    else {
        $last_id_arr = array();
        foreach ($shift_ghrs as $item) {
            $insertResult = pg_execute($db, "insertNew", array(SHIFT_RANKING_DEFAULT, $qtr, $year, $item));
            $insertResultArr = pg_fetch_all($insertResult);
            $last_id = array_map(function ($v) {return (int)$v;},$insertResultArr[0]);
            $last_id = $last_id["id"];
            array_push($last_id_arr, $last_id);
        }
    }

    $insertResultArr = pg_fetch_all($insertResult);
    // Get the last committed rowid for this session, which will be the record just inserted.
    // It's in a string array, so this one-liner pulls out the 0th and casts to an int, then the next line retrieves the value.
    // ************* THIS IS CURRENTLY RETURNING A WARNING ******************
    $last_id = array_map(function ($v) {return (int)$v;},$insertResultArr[0]);
    $last_id = $last_id["id"];

    header('Content-Type: application/json');
    pg_close($db);
?>
