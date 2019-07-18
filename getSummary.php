<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8">

    <script src="https://unpkg.com/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-enterprise@19.0.0/dist/ag-grid-enterprise.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/plotly.js/1.38.3/plotly.min.js"></script>
    <link href="https://unpkg.com/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/ag-grid/19.0.1/styles/ag-theme-balham.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css" rel="stylesheet">

    <title>CLNS Rank/Rate Summary</title>
</head>

<body>
    <div class="alert alert-info fade in" id="bluebookInst">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <strong>Click on a name to see their review.</strong>
    </div>
    <?php
        require 'db.php';
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
                  FROM public.review_ratings
                  WHERE
                  (
                      (reports_to_name LIKE $1)
                      AND
                      (public.review_ratings.hidden=false)
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


        $result = pg_prepare($db, "get_summary", $getSummarySql);
        $result = pg_execute($db, "get_summary", array($supGHR));
        $resultSummary = pg_fetch_all($result);

        pg_close($db);
    ?>

    <div id="summaryTable" style="width: 100%; height: 850px;" class="ag-theme-balham"></div>

        <script type="application/javascript">
            agGrid.LicenseManager.setLicenseKey(YOUR_LICENSE_KEY);

            function jsonEscape(str)  {
                return str.replace(/\n/g, "\\\\n")
                .replace(/\r/g, "\\\\r")
                .replace(/\t/g, "\\\\t")
                .replace(/[\u0000-\u0019]+/g,"");
            }

            // HEX_APOS replaces single quotes, NUMERIC_CHECK treats numerics as numerics rather than strings, so sorting works
            var tableResult = '<?php echo json_encode($resultSummary, JSON_HEX_APOS | JSON_NUMERIC_CHECK) ?>';
            tableResult = JSON.parse(jsonEscape(tableResult));

            var summaryTableCols = [
                {headerName:"Quarter", field:"quarter", filter:"agTextColumnFilter", enableRowGroup: true,},
                {headerName:"Year", field:"year", filter:"agTextColumnFilter", enableRowGroup: true},
                {headerName:"Name", filter:"agTextColumnFilter", field:"full_name"},
                {headerName:"Dept", filter:"agTextColumnFilter", field:"subgroup", enableRowGroup: true},
                {headerName:"Title", filter:"agTextColumnFilter", field:"title", enableRowGroup: true},
                {headerName:"Shift", filter:"agTextColumnFilter", field:"shift"},
                {headerName:"Shift Ranking", filter:"agTextColumnFilter", field:"shift_ranking"},
                {headerName:"Overall Rating", filter:"agTextColumnFilter", field:"overall_rating"},
                {headerName:"Numeric Rating", filter:"agTextColumnFilter", field:"overall_numeric_rating"},
                {headerName:"Overall Ranking", filter:"agTextColumnFilter", field:"overall_ranking"},
                {
                    headerName:"Positive Watch",
                    filter:"agTextColumnFilter",
                    field:"pos_watch",
                    cellRenderer:function (params) {
                        if (params.value === 't')
                            return "X";
                        else if (params.value === 'f')
                            return null;
                    }
                },
                {
                    headerName:"Negative Watch",
                    filter:"agTextColumnFilter",
                    field:"neg_watch",
                    cellRenderer:function(params) {
                        if (params.value === 't')
                            return "X";
                        else if (params.value === 'f')
                            return null;
                    }
                },
                {
                    headerName:"Succession",
                    filter:"agTextColumnFilter",
                    field:"succession",
                    cellRenderer:function(params) {
                        if (params.value === 't')
                            return "X";
                        else if (params.value === 'f')
                            return null;
                    }
                },
                {
                    headerName:"Promotion",
                    filter:"agTextColumnFilter",
                    field:"promo",
                    cellRenderer:function(params) {
                        if (params.value === 't')
                            return "X";
                        else if (params.value === 'f')
                            return null;
                    }
                }
            ];

            var summaryTableOptions = {
                columnDefs: summaryTableCols,
                animateRows: true,
                enableRangeSelection: true,
                paginationPageSize: 150,
                floatingFilter: true,
                onFirstDataRendered: onFirstDataRendered,
                onRowGroupOpened: onFirstDataRendered,
                onCellDoubleClicked: onCellDoubleClicked,
                onFilterChanged: onFilterChanged,
                rowData: tableResult,
                enableSorting: true,  // v18
                enableFilter: true,  // v18
                defaultColDef: {
                    /*enableSorting: true, v21
                    enableFilter: true, v21 */
                    editable: false
                },
                sideBar: 'columns'
            };


            function onFirstDataRendered(params){
                params.api.sizeColumnsToFit();
            }


            function onRowGroupOpened(params){
                params.api.sizeColumnsToFit();
            }


            function onFilterChanged(params){
                // Any time the filter on ag-grid is changed, rebuild the table
                var layout = {
                    barmode: 'group',
                    autosize: false,
                    width: 1800,
                    height: 600
                };

                Plotly.react('distPlot', getTableFromAgGrid(), layout);
            }


            function getTableFromAgGrid(options){

                if (options === undefined) {
                    var newDataArray = [];
                    summaryTableOptions.api.forEachNodeAfterFilter(function(rowNode, index) {
                        newDataArray.push(rowNode.data);
                    });
                }

                let unSuccCount = newDataArray.filter(function(newDataObj) {
                    if (newDataObj.overall_rating !== null) {
                        return newDataObj.overall_rating.indexOf('Unsuccessful') > -1;
                    }
                });
                let succCount = newDataArray.filter(function(newDataObj) {
                    if (newDataObj.overall_rating !== null) {
                        return newDataObj.overall_rating.indexOf('Successful') > -1;
                    }
                });
                let excSomeCount = newDataArray.filter(function(newDataObj) {
                    if (newDataObj.overall_rating !== null) {
                        return newDataObj.overall_rating.indexOf('Exceeds Some') > -1;
                    }
                });
                let excMostCount = newDataArray.filter(function(newDataObj) {
                    if (newDataObj.overall_rating !== null) {
                        return newDataObj.overall_rating.indexOf('Exceeds Most') > -1;
                    }
                });

                let unSuccNames = [];
                let excMostNames = [];

                unSuccCount.forEach(function(element){
                    unSuccNames.push(element.full_name)
                });

                excMostCount.forEach(function(element){
                    excMostNames.push(element.full_name)
                });

                /*
                    Desired distribution is
                    Unsuccessful: 10%
                    Successful: 55%
                    Exceeeds Some: 25%
                    Exceeds Most: 10%
                    Past experiments with rounding found that subtracting 1 from Succesful is the best way to fix the totals
                */

                let totalCounts = unSuccCount.length + succCount.length + excSomeCount.length + excMostCount.length;
                let targetCounts = Array.from([Math.ceil(totalCounts*0.1), Math.ceil(totalCounts*0.55) - 1, Math.ceil(totalCounts*0.25), Math.ceil(totalCounts*0.1)]);

                let trace1 = {
                        x: ['Unsuccessful', 'Successful', 'Exceeds Some', 'Exceeds Most'],
                        y: [unSuccCount.length, succCount.length, excSomeCount.length, excMostCount.length],
                        name: 'Actual',
                        text: [JSON.stringify(unSuccNames).toString(), , , JSON.stringify(excMostNames).toString()],
                        type: 'bar'
                };

                let trace2 = {
                        x: ['Unsuccessful', 'Successful', 'Exceeds Some', 'Exceeds Most'],
                        y: [targetCounts[0], targetCounts[1], targetCounts[2], targetCounts[3]],
                        name: 'Target',
                        type: 'bar'
                };

                let newData = [trace1, trace2];
        		return newData;
        	}


            function onCellDoubleClicked(params){
                // Regardless of where on the row you click, get the name, quarter, and year, then pass it to a modal with some sanitizing
                var getCellData = [params.data['full_name'], params.data['quarter'], params.data['year']];
                var found = tableResult.find( (e => e.full_name === getCellData[0] && e.quarter === getCellData[1] && e.year === getCellData[2]));
                var writeupText = found.writeup.replace(/\\n/g, "\n").replace(/\\r/g, "\r").replace(/\\t/g, "\t").replace(/\n/g, "<p>");
                $('#empWriteup .modal-body').html("<p>" + writeupText);
                $('#empWriteup').modal('show');
            }



            var summaryTableGrid = document.querySelector('#summaryTable');
            new agGrid.Grid(summaryTableGrid, summaryTableOptions);

            </script>

            <div class="modal" tabindex="-1" role="dialog" id="empWriteup">
               <div class="modal-dialog animated bounceInDown" role="dialog">
                 <div class="modal-content">
                   <div class="modal-header">
                     <h3 class="modal-title">Blue Book</h3>
                   </div>
                   <div class="modal-body">
                     <p></p>
                   </div>
                   <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                   </div>
                 </div>
               </div>
             </div>
        <div id="distPlot"></div>
    </div>
        <script type="application/javascript">

        // Initial build of table, pulls currently displayed data in ag-grid (which will be all of it)
            var layout = {
                barmode: 'group',
                autosize: false,
                width: 1800,
                height: 600
            };

            Plotly.newPlot('distPlot', getTableFromAgGrid(), layout);
        </script>
    </body>
</html>
