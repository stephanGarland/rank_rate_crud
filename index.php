<!--< ?php
session_start();
if (!isset($_COOKIE['rank_rate'])) {
    header("Location: http://ccdev/cc/rank_rate/rankRateLogin.php");
    exit();
}
?>
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta charset="utf-8">
    <script src="/library/jquery/jquery-3.3.1.min.js"></script>
    <script src="/library/jquery-ui/jquery-ui.min.js"></script>
    <script src="/library/bootstrap-3.3.7/js/bootstrap.min.js"></script>
    <script src="/library/bootbox-5.0-min.js"></script>
    <script src="/library/tabulator-4.0.4/dist/js/tabulator.min.js"></script>
    <link href="/library/tabulator-4.0.4/dist/css/bootstrap/tabulator_bootstrap.min.css" rel="stylesheet">
    <link href="/library/bootstrap-3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="/library/animate.min.css" rel="stylesheet">

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
                        <select name="supChoice" id="supChoiceButton" value="" \
                            class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                          <option>Select Supervisor</option>
                        </select>
                </div>
            </div>
                <br>
                <span id="getShiftSummary"></span>
                <span id="getAllSummary"></span>
                <span id="blankSpace" style="display:inline-block; width: 50px;"></span>
                <select name="selectYear" id="selectYear" class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                    <option>Select Year</option>
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
                  <button id="eqpButton" class="btn btn-default active">EQP</button>
                  <button id="prcButton" class="btn btn-primary">PRC</button>
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

                          <span id="blankSpace" style="display:inline-block; width: 65px;"></span>
                          <input type="button" class="btn btn-success mb-2" onclick="location.href='about.php';" value="About" />
                          <input type="button" class="btn btn-danger mb-2" onclick="location.href='help.html';" value="Help!" />

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
         <script src="main.js" type="application/javascript"></script>
      </div>
  </body>
</html>
