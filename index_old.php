<?php
session_start();
eval(base64_decode('JHNhbHRfY2hlY2sgPSAnKOC4hyDNoMKwIM2fypYgzaHCsCnguIcnOw=='));
if (!isset($_COOKIE['rank_rate'])) {
    header("Location: http://ccdev/cc/rank_rate/rankRateLogin.php");
    exit();
}

//else {
    //if (strpos($COOKIE['rank_rate'], strval($salt_check))) !== false {
    //    exit();
    //}
//}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8">

    <script src="https://unpkg.com/jquery@3.3.1/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/jquery-ui-css@1.11.5/jquery-ui.min.js"></script>
    <script src="https://unpkg.com/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="https://unpkg.com/tabulator-tables@4.0.5/dist/js/tabulator.min.js"></script>
    <link href="https://unpkg.com/tabulator-tables@4.0.5/dist/css/bootstrap/tabulator_bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css" rel="stylesheet">

    <title>CLNS Rank/Rate</title>
</head>

<body>
    <div class="container">
        <div class="row">
            <h3>Employees</h3>
        </div>
            <div class="row">
            <p>
              <div class="dropdown">
                <div id="selectSupButton" class="dropdown-content">
                    <form>
                        <select name="supChoice" id="supChoiceButton" value="" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                          <option>Select Supervisor</option>
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
                                        /*
                                          RIGHT JOIN public.review_ratings
                                          ON
                                          	public.review_employees.full_name = public.review_ratings.reports_to_name
                                          WHERE (
                                              (public.review_employees.dept_name LIKE $1)
                                              AND
                                              (
                                                (public.review_employees.title LIKE $2)
                                                OR
                                                (public.review_employees.title LIKE $3)
                                              )
                                              AND
                                              (
                                                  public.review_ratings.hidden=false
                                              )
                                          )
                                          ORDER BY public.review_employees.full_name ASC;
                                          ";
                                          */


                              $resultSups = pg_prepare($db, "get_sups", $getSupSql);
                              //$resultSups = pg_execute($db, "get_sups", array('Clean%', '%Supervisor', '%TR'));
                              $resultSups = pg_execute($db, "get_sups", array('BIG_BOSS', '%Supervisor', '%TR'));
                              while ($row = pg_fetch_assoc($resultSups)) {
                                  echo "<option value='" . $row['employee'] . "'>" . $row['employee'] . "</option>";
                              }

                          if (isset($_GET['supChoice'])) {
                            $selectedSup = $_GET['supChoice'];

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
                      	  		          )
                                          ORDER BY subgroup, full_name, year desc, quarter desc;
                                          ";

                                $resultEmps = pg_prepare($db, "get_reports", $getReportsSql);
                                $resultEmps = pg_execute($db, "get_reports", array('Clean%', $selectedSup, '%Associate%'));
                                $resultEmpsArr = pg_fetch_all($resultEmps);
                            }
                          pg_close($db);

                          ?>

                    </select>
                </div>
            </div>
                <br>
                <input type="submit" id="submitForm" onclick="this.form.submit()" class="btn btn-primary">
                <span id="getShiftSummary"></span>
                <span id="getAllSummary"></span>
                <span id="blankSpace" style="display:inline-block; width: 50px;"></span>
                <select name="selectYear" id="selectYear" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                    <option>Select Year</option>
                        <?php
                            $yearToSelect=2018;
                            for ($yearToSelect = 2018; $yearToSelect <= 2038; $yearToSelect++) {
                                echo "<option value='" . $yearToSelect . "'>" . $yearToSelect . "</option>";
                            }
                        ?>
                  </select>
                <select name="selectQtr" id="selectQtr" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                    <option>Select Quarter</option>
                    <option value="1">Q1</option>
                    <option value="2">Q2</option>
                    <option value="3">Q3</option>
                    <option value="4">Q4</option>
                </select>
                <span id="blankSpace" style="display:inline-block; width: 15px;"></span>
                <span class="btn-group btn-toggle">
                  <button id="group_2Button" class="btn btn-default active">EQP</button>
                  <button id="group_1Button" class="btn btn-primary">PRC</button>
                </span>
                <span id="blankSpace" style="display:inline-block; width: 15px;"></span>
                <span id="addRecord">
                        <button class="btn btn-warning" type="button" data-toggle="modal" data-target=".add-record-modal">Bulk Add Record</button>
                        <div class="modal fade add-record-modal" tabindex="-1" role="dialog" aria-labelledby="Add Record" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                  <b>Add Entire Shift Template</b>
                                  <br>
                              </div>
                              <div class="modal-body" id="addRecordBody">
                                <div class="container-fluid">
                                    <div class="row">
                                        <select name="bulkSelectYear" id="bulkSelectYear" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                            <option id="bulkSelectYear">Select Year</option>
                                                <?php
                                                    $yearToSelect = 2018;
                                                    for ($yearToSelect=2018; $yearToSelect<=2038; $yearToSelect++) {
                                                        echo "<option value='" . $yearToSelect . "'>" . $yearToSelect . "</option>";
                                                    }
                                                ?>
                                          </select>

                                        <select name="bulkSelectQtr" id="bulkSelectQtr" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                            <option id="bulkSelectQtr">Select Quarter</option>
                                            <option value="1">Q1</option>
                                            <option value="2">Q2</option>
                                            <option value="3">Q3</option>
                                            <option value="4">Q4</option>
                                        </select>
                                        <p>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                      <button type="button" class="btn btn-primary" id="addRecordModalSave">Save changes</button>
                                    </div>
                                </div>
                              </div>
                            </div>
                          </div>
                      </div>
                  </span>
                  <span id="shiftRanking">
                      <form id="shiftRankingForm">
                          <button class="btn btn-info" type="button" data-toggle="modal" data-target=".shift-ranking-modal">Shift Ranking</button>
                          <span class="pull-right">
                              <input type="button" class="btn btn-success mb-2" onclick="location.href='about.php';" value="About" />
                          </span>
                          <div class="modal fade shift-ranking-modal" tabindex="-1" role="dialog" aria-labelledby="Shift Ranking" aria-hidden="true">
                              <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                      <div class="modal-header">
                                          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                          <span aria-hidden="true">&times;</span>
                                          </button>
                                          <br>
                                      </div>
                                      <div class="modal-body" id="shiftRankingBody">
                                          <div class="container-fluid">
                                              <div class="row">
                                                  <ul class="list-group" id="shiftRankName"></ul>
                                              </div>
                                          </div>
                                          <div class="modal-footer">
                                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                              <button type="button" class="btn btn-primary" id="shiftRankModalSave">Save changes</button>
                                          </div>
                                      </div>
                                  </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </span>
            </form>
          <div id="shiftTable"></div>
          <script type="application/javascript">
              $("#getShiftSummary").append('<input id="summarizeShift" type="button" onclick="getShiftSummary()" class="btn btn-primary" value="Summarize Shift" />');
              $("#getAllSummary").append('<input id="summarizeAll" type="button" onclick="getAllSummary()" class="btn btn-primary" value="Summarize All" />');
              var selYear = 'Select Year';
              var selQtr = 'Select Quarter';

              $('#selectYear').on('click', function() {
                  selYear = this.value;
                  getEmpEntries();
                  storeQtrYear();
                  summaryTable.replaceData(tableResult);
              });

              $('#selectQtr').on('click', function() {
                  selQtr = this.value;
                  storeQtrYear();
                  wipeShiftRankingTable();
                  getEmpEntries();
                  summaryTable.replaceData(tableResult);
              });

              $('#bulkSelectQtr').on('click', function() {
                  bulkSelectQtr = this.value;
              });

              $('#bulkSelectYear').on('click', function() {
                  bulkSelectYear = this.value;
              });

              $('#supChoiceButton').on('click', function() {
                  sessionStorage.supChoiceVar = $('#supChoiceButton').val();
              });


              $(document).ready(function() {
                  if (sessionStorage.supChoiceVar) {
                      $('#supChoiceButton').val(sessionStorage.supChoiceVar);
                      $('#selectQtr').val(selQtr);
                      $('#selectYear').val(selYear);
                  }
              });


              function getShiftSummary(){
                  let selectedSup = sessionStorage.supChoiceVar;
                  selectedSup = JSON.stringify(selectedSup);
                  window.open("getSummary.php?selectedSup=" + selectedSup);
              }

              function getAllSummary(){
                  let selectedSup = "%";
                  selectedSup = JSON.stringify(selectedSup);
                  window.open("getSummary.php?selectedSup=" + selectedSup);
              }

              function success() {
                  bootbox.alert({
                      message: "Record updated!",
                      className: 'animated bounce faster ',
                      backdrop: true
                  });
              }


              function errorMessage(jqXHR, textStatus, errorThrown) {
                console.log("jqXHR");
                console.log(jqXHR);
                console.log("textStatus");
                console.log(textStatus);
                console.log("errorThrown");
                console.log(errorThrown);
              }

              $("#shiftRankModalSave").click(function() {
                  let shiftRankArray = [];
                  let finalShiftRankArray = [];
                  let form = $('#shiftRankItem')[0].parentElement.children;
                  for (let i = 0; i < form.length; i++) {
                      let name = $(form[i].firstElementChild.firstElementChild).val();
                      let rank = $(form[i].lastElementChild.firstElementChild).val();
                      shiftRankArray.push(name, rank);
                  }

              // finalShiftRankArray winds up being an object of arrays: [[name:rank]...{quarter: selQtr},{year: selYear}]
              // saveShiftRank.php was expecting this format, I originally wrote this in a truly horrific fashion.
              // Could refactor eventually but this isn't as awful anymore.
              for (let j = 0; j < shiftRankArray.length; j+=2) {
                  finalShiftRankArray[j] = shiftRankArray.slice(j, j+2);
              }

              finalShiftRankArray.push({'quarter':selQtr}, {'year':selYear});
              // Removes nulls
              finalShiftRankArray = finalShiftRankArray.filter(function(e){return e});
              var shiftRankData = JSON.stringify(finalShiftRankArray);

                $.ajax({
                    type: 'POST',
                    url: 'saveShiftRank.php',
                    data: { shiftRankData: shiftRankData },
                    error: errorMessage(),
                    success: success()
                });
              });


              var selected_ghr = '';
              // Thank you to Madara Uchiha on SO for this.
              document.addEventListener('mouseover', function(event) {
                var hoveredEl = event.target;
                if (hoveredEl.tagName !== 'A') {
                    return;
                } // Ignore non links
                  selected_ghr = hoveredEl.href.split('&')[0].split('=')[1];
              });

              $("#addRecordModalSave").click(function() {
                $.ajax({
                    type: 'POST',
                    url: 'saveNewRecord.php',
                    data: {
                        shift_ghrs: shift_ghrs,
                        qtr: bulkSelectQtr,
                        year: bulkSelectYear
                    },
                    dataType: "application/json",
                    error: errorMessage(),
                    success: success()
                })
              });


              // Note that this is the name from the URL, and it's being sent to getEmpEntries.php
              // getEmpEntries overloads reports_to in its query by casting the input to a varchar so GHRs don't fail
              // This could be fixed by solely using GHRs or names as a record
              //var reports_to_name = document.URL.split('/')[5].split('=')[1].replace(/\+/g, ' ').split('&')[0];


              function getEmpEntries() {
                let reports_to_name = sessionStorage.supChoiceVar;
                $.ajax({
                      type: 'GET',
                      url: 'getEmpEntries.php',
                      cache: false,
                      data: {
                          reports_to: reports_to_name,
                          curQuarter: selQtr,
                          curYear: selYear,
                          subGroup: activeSubgroup

                      }
                    }).done(function(empEntries) {
                        // Sort by shift_ranking so they're appended in order
                        console.log(empEntries);
                        empEntries = empEntries.sort(function(a,b){return a.shift_ranking - b.shift_ranking});
                        for (var i in empEntries) {

                          $('#shiftRankName').append('<li class="list-group-item row" id="shiftRankItem"> <div class="col-md-10">' +
                              '<input id="' + empEntries[i].ghr_id + '_name" type="text" value="' + empEntries[i].full_name + '" readonly class="form-control">' +
                              '</div>' + '<div class="col-md-2">' + '<input id="' + empEntries[i].ghr_id + '_shiftRank" name="shiftRank" type="text" value="' +
                              empEntries[i].shift_ranking + '"class="form-control">' + '</div>' + '</li>');
                      }

                      const draggableShiftRanks = Array.prototype.slice.apply(
                          document.querySelectorAll("#shiftRankItem"));

                          $('#shiftRankName').sortable({
                              change: function(event, ui) {
                                  $(window).mouseup(function(e) {
                                      let shiftRankNode = $('#shiftRankItem')[0].parentElement.children
                                      for (let i = 0; i < shiftRankNode.length; i++) {
                                          let draggedItem = $(shiftRankNode[i].lastElementChild.firstElementChild);
                                          $(draggedItem).val(i + 1);
                                      }
                                  });
                                }
                            });
                      });
              }



              /*  This is hacked together, logic is as follows:

              When the page is first loaded, set the Read/Update to getQtrYear().selQtr/selYear, which
              default to "Select Quarter" and "Select Year" respectively; while the <option> value would be set,
              the functions below would just clear it to '' on first run, so setting the vars to those defaults fixes it.
              Next, run replQtrYear(), which checks to see if selQtr and selYear are set in sessionStorage. They are
              only set once a Read/Update button is clicked, so nothing happens if this is run. Once a Read/Update is clicked,
              storeQtrYear() is called, and the sessionStorage objects are set to getQtrYear().selQtr/selYear.
              Since it's sessionStorage, and not localStorage, the Year/Quarter resets when a new supervisor is loaded.
              */


              function storeQtrYear() {
                  sessionStorage.selQtr = selQtr;
                  sessionStorage.selYear = selYear;
              }


              function replQtrYear() {
                  if (sessionStorage.selQtr && sessionStorage.selYear && document.referrer.indexOf("create") !== -1) {
                      $("#selectQtr")[0].value = sessionStorage.selQtr;
                      $("#selectYear")[0].value = sessionStorage.selYear;
                      selQtr = sessionStorage.selQtr;
                      selYear = sessionStorage.selYear;
                  }
              }

              function wipeShiftRankingTable() {
                  // Every time the quarter is changed, wipe all <li> entries from the modal, then rebuild them with getEmpEntries and replaceData
                  var list = document.getElementById("shiftRankName");
                  while (list.hasChildNodes()) {
                      list.removeChild(list.firstChild);
                  }
              }
              // Call to set quarter and year back to session saved
              replQtrYear();
              // Call so if the user goes back from a Read/Update, the shift ranking modal is re-populated
              getEmpEntries();

              var readUpdateIcon = function(cell, formatterParams){
                  return "<a id='read_update_btn' class='btn btn-success' href=create.html?id="
                          + cell.getRow().getData().ghr_id + "&year=" + selYear + "&qtr=" + selQtr
                          +  ">Read/Update</a>";
              };

              var tableResult = '<?php echo json_encode($resultEmpsArr); ?>';
              tableResult = JSON.parse(tableResult);

              // Due to how SQL query is written, filtering out any non-null term_date there
              // would have the effect of just pulling in the first quarter where the now-terminated
              // employee was NOT terminated. This way allows them to pass but then deletes their entry
              // from the object before it's sent to Tabulator.
              for (const key of Object.keys(tableResult)) {
                if (tableResult[key].term_date) {
                    delete tableResult[key];
                  }
              }

              // Used for doing a bulk record add in saveNewRecord.
              shift_ghrs = [];
              tableResult.forEach(function(e) {
                  shift_ghrs.push(e.ghr_id);
              });

              var summaryTable = new Tabulator("#shiftTable", {
                  layout:"fitColumns",
                  data:tableResult,
                  columns:[
                      {title:"GHR", field:"ghr_id"},
                      {title:"Subgroup", field:"subgroup"},
                      {title:"Name", field:"full_name"},
                      {title:"Title", field:"title"},
                      {title:"Action", formatter:readUpdateIcon, align:"center"}
                  ],
              });

              // Forces a quarter/year selection
              var Anchors = document.getElementsByTagName("a");
              for (var i = 0; i < Anchors.length ; i++) {
                  Anchors[i].addEventListener("click",
                      function (event) {
                          event.preventDefault();
                          // For some reason, this is allowing it to pass if only one is selected.
                          if (isNaN(parseInt(selQtr)) || isNaN(parseInt(selYear))) {
                              bootbox.alert({
                                  message: "You must select a Quarter and Year!",
                                  className: 'animated jello',
                                  backdrop: true
                              });
                          }
                          else {
                              window.location = this.href;
                          }
                      },
                      false),
                  Anchors[i].addEventListener("click",
                      function (event) {
                          storeQtrYear();
                      });
              }

              var activeSubgroup = '';
              $('.btn-toggle').click(function() {
                  // Prevents toggling the button from clearing the selected supervisor
                  event.preventDefault();
                  activeSubgroup = $('.active').prop('id');
                  $(this).find('.btn').toggleClass('btn-primary');
                  $(this).find('.btn').toggleClass('btn-default');
                  $(this).find('.btn').toggleClass('active');

                  if (activeSubgroup == 'group_1Button') {
                      summaryTable.setFilter("subgroup", "=", "Process");
                  }
                  else if (activeSubgroup == 'group_2Button') {
                      summaryTable.setFilter("subgroup", "=", "Equipment");
                  }
                  wipeShiftRankingTable();
                  getEmpEntries();
              });
          </script>
      </div>
  </body>
</html>
