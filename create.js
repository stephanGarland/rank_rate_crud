const parsedUrl = new URL(window.location.href);
const urlGHR = parsedUrl.searchParams.get("id");
const selQtr = parsedUrl.searchParams.get("qtr");
const selYear = parsedUrl.searchParams.get("year");
var reports_to = '';
var finalFormArray = [];

function success() {
    bootbox.alert({
        message: "Record updated!",
        className: 'animated bounce faster',
        backdrop: true
    });
}

// Add ability to save each metric's score to database
$('#mboCalcModal').on('hidden.bs.modal', function(){
  $('#runningAvgModalContent').modal('hide')
   getEmpDetails(getEmpEntries);
});

function returnAfterDelete() {
  bootbox.confirm({
    message: "Record deleted - would you like to return to the main page?",
    className: 'animated zoomIn faster',
    buttons: {
            confirm: {
            label: 'Yes',
            className: 'btn-success'
        },
        cancel: {
            label: 'No',
            className: 'btn-warning'
        }
    },
    callback: function (result) {
        if (result == true) {
            window.history.back();
            }
        }
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

$('#writeupModalSave').click(function() {
  let writeupData = [];
  let writeupEmpName = empName[1].value;
  let writeupEmpGHR = empGHR[1].value;
  let writeupFormBody = $('#writeupFormBody').val();
  writeupData.push(writeupEmpGHR, writeupEmpName, selQtr, selYear, writeupFormBody);
  writeupData = JSON.stringify(writeupData);
  $.ajax({
      type: 'POST',
      url: 'saveWriteup.php',
      cache: false,
      data: { 'writeupData': writeupData },
      error: errorMessage(),
      success: success()
  });
});

$('#mboCalcModalSave').click(function() {
  let mboScore = [];
  let mboEmpName = empName[1].value;
  let mboEmpGHR = empGHR[1].value;
  mboScore.push(
      mboEmpGHR,
      selQtr.toString(),
      selYear.toString(),
      runningAvgRating,
      runningAvg.toString()
  );
  mboScore = JSON.stringify(mboScore);
  $.ajax({
      type: 'POST',
      url: 'saveMBO.php',
      cache: false,
      data: { 'mboScore': mboScore },
      error: errorMessage(),
      success: success()
  });
});

getEmpDetails(getEmpEntries);

// Used for getting the id column of the db to delete records.
var row_id = '';

function getEmpDetails(callback) {
  $.ajax({
        type: 'GET',
        url: 'getEmpDetails.php',
        cache: false,
        data: {
            urlGHR : urlGHR,
            curQuarter : selQtr,
            curYear : selYear
        },
        // The callback is sending the response from getEmpDetails, which is its JSON being echoed,
        // along with reports_to and urlGHR, to getEmpEntries.

        success: function(reports_to) {
            callback(reports_to, urlGHR);
        }
      }).done(function(empDetails) {
            row_id = empDetails.row_id;
            $('#empName').replaceWith('<div id="empName" name="empName">' + empDetails.full_name + '</div>');
            $('#empGHR').replaceWith('<div id="empGHR" name="empGHR">' + empDetails.ghr_id + '</div>');
            $('#empTitleDiv').replaceWith('<div id="empTitleDiv" name="empTitleDiv">' + empDetails.title + '</div>');
            $('#supName').replaceWith('<div id="supName" name="supName">' + empDetails.reports_to_name + '</div>');
            $('#quarter').replaceWith('<input id="quarter" name="quarter" type="text" value=' + selQtr + ' readonly class="form-control">');
            $('#year').replaceWith('<div id="quarter" name="quarter"><input id="year" name="year" type="text" value=' + selYear +
                ' readonly class="form-control"></div>');
            $('#overall_ranking').replaceWith('<div id="overall_ranking" name="overall_ranking">\
                <input id="quarter" name="quarter" type="text" value=' + empDetails.overall_ranking + ' readonly class="form-control"></div>');
            $('#run_avg').replaceWith('<div id="run_avg" name="run_avg">\
                <input id="running_avg" name="running_avg" type="text" value=' + empDetails.running_avg + ' readonly class="form-control"></div>');
            $('#writeupFormBody').val(empDetails.writeup);
            $('#backBtn').replaceWith('<span id="backBtn"><a class="btn btn-warning" href="index.php">Back</a></span>');
            if (empDetails.overall_rating) { // If it's null, leave 'Overall Rating' in place, otherwise pull in current rating
                $('#curRating')[0].innerText = empDetails.overall_rating;
            }
            $('.dropdown-menu li a').click(function(){
                $(this).parents('.dropdown').find('.btn').html($(this).text() + ' <span class="caret"></span>');
                $(this).parents('.dropdown').find('.btn').val($(this).data('value'));
            });
        });
    }

function getEmpEntries(reports_to, urlGHR) {
    // This is assigning the value of a callback result JSON (reports_to) value (reports_to.reports_to) to var reports_to_ghr
    var reports_to_ghr = reports_to.reports_to;
  $.ajax({
        type: 'POST',
        url: 'getEmpEntries.php',
        cache: false,
        data: {
            reports_to : reports_to_ghr,
            curQuarter : selQtr,
            curYear : selYear
        }
      }).done(function(empEntries) {
        var allCheckBoxes = $(':checkbox');
        let checkMap = new Map();
        empEntries = empEntries.filter(item => item.ghr_id == urlGHR)[0];
        // Gets all the bools that became strings so they can check the right boxes
        Object.keys(empEntries).forEach(function(key) {
            // Older records may have nulls, don't pull those in.
            // Using a map vs an object as before since maps preserve order.
            if ((empEntries[key]) && (empEntries[key] == 't') || (empEntries[key] == 'f')) {
                checkMap.set(key, empEntries[key]);
            }
        });
        /*

            Above, an AJAX call gets every employee, storing it in empEntries.
            Every checkbox on the page (9 of them) is put into allCheckBoxes.
            empEntries then gets filtered based on the GHR of the currently employee.
            Then, a map (checkMap) is created that contains only the fake booleans from empEntries,
            e.g. ean_pa => "f".

            Below, checkMap's key/value pairs are iterated over, and a nested for loop then iterates
            over the values of the allCheckBoxes object. If the checkbox being inspected is equal to
            the key of checkMap, and if the value of the checkMap entry (the if will short-circuit
            if the id doesn't match) is true, the respective checkbox is set to true, checking the box.

        */

        for (let e of checkMap) {
            for (let value of Object.values(allCheckBoxes)) {
                if ((value.id == e[0]) && (e[1] == 't')) {
                    value.checked = true;
                }
            }
        }
    });
}


$('#submitPageSave').click(function() {
    // If you don't cast the bools to a string, false and null both show up as null in the JSON object
    let pageData = [];
    let ghr_id = urlGHR;
    let pos_watch_check = pos_watch[1].checked.toString();
    let neg_watch_check = neg_watch[1].checked.toString();
    let ean_pa_check = ean_pa[1].checked.toString();
    let ean_pip_check = ean_pip[1].checked.toString();
    let ean_verbal_check = ean_verbal[1].checked.toString();
    let ean_written_check = ean_written[1].checked.toString();
    let ean_final_check = ean_final[1].checked.toString();
    let succession_check = succession[1].checked.toString();
    let promo_check = promo[1].checked.toString();
    // overallRating[1] is empty unless the drop-down is modified; this leads to the rating being nulled if the page is submitted without a change
    if (overallRating[1].value == '') {
      overallRating[1].value = $('#curRating')[0].innerText;
      var overall_rating_to_push = overallRating[1].value;
    }
    else {
        var overall_rating_to_push = overallRating[1].value;
    }
        pageData.push(
        ghr_id,
        overall_rating_to_push,
        pos_watch_check,
        neg_watch_check,
        ean_pa_check,
        ean_pip_check,
        ean_verbal_check,
        ean_written_check,
        ean_final_check,
        succession_check,
        promo_check,
        selQtr,
        selYear
    );
    pageData = JSON.stringify(pageData);
    $.ajax({
      type: 'POST',
      url: 'savePage.php',
      data: { pageData },
      error: errorMessage(),
      success: success()
    });
});


$('#deleteRecord').click(function() {
    bootbox.confirm({
    message: "Are you sure you want to delete this record?",
    className: 'animated zoomIn faster',
    buttons: {
            confirm: {
            label: 'Yes',
            className: 'btn-danger'
        },
        cancel: {
            label: 'No',
            className: 'btn-success'
        }
    },
    callback: function (result) {
        if (result) {
            $.ajax({
                type: 'POST',
                url: 'deleteRecord.php',
                data: { row_id : row_id },
                error: errorMessage(),
                success: returnAfterDelete()
            });
        }
    }
    });
});

$('#mboCalcButton').click(function() {
$('#runningAvgModalContent').modal({
  'backdrop': 'static',
  'show': true
})
});

$.ajax({
    type: 'GET',
    url: 'getChecks.php',
    data: {
      qtr : selQtr,
      year : selYear,
      ghr_id : urlGHR
    },
    datatype: 'json',
    error: errorMessage(),
    success: function(response) {
        checkIfExist(response);
    }
});

function checkIfExist(recordExist) {
if (recordExist) {
  //pass;
} /*
    This function is probably not required now that I'm only pulling
    employees who are assigned to a supervisor for a given quarter/year
    */
else if (!(recordExist)) {
  bootbox.confirm({
        message: 'No record was found, would you like to create one?',
        buttons: {
                confirm: {
                label: 'Yes',
                className: 'btn-info'
            },
            cancel: {
                label: 'No',
                className: 'btn-default'
            }
        },
        onEscape: false,
        callback: function(result) {
            if (result == false) {
                bootbox.alert("Returning to main page.");
                setTimeout(function(){ window.history.back(); }, 2000);
            }
            else {
                $.ajax({
                    type: 'POST',
                    url: 'saveNewRecord.php',
                    data: {
                        ghr_id: urlGHR,
                        qtr: sessionStorage.selQtr,
                        year: sessionStorage.selYear
                    },
                    dataType: "html",
                    success: function() {
                        window.location.reload(true);
                    }
                })
            }
        }
  });
}
}


function fixedRound(num) {
    // Hacky fix for floating point math
    if ((Math.ceil(num) - Math.floor(num * 100) / 100) < 0.02) {
        return Math.ceil(num);
    }
    else {
        return Math.floor(num * 100) / 100;
    }
}

function returnMBO() {
var mboData = $.ajax({
  type: 'GET',
  url: 'getMBO.php',
  data: {
      id: urlGHR,
      quarter: sessionStorage.selQtr,
      year: sessionStorage.selYear
   },
  datatype: 'html',
  error: errorMessage()
});

return mboData;
}

var runningAvg;
var runningAvgRating;
returnMBO().then(function(mbos) {
    let mboTableResult = [];
    let filteredMBO = mbos.filter(function(e) {
      if (e.worker_title == $('#empTitle')[0].value) {
          mboTableResult.push(e);
          mboTableResult = mboTableResult.filter(e => e.weight > 0);
          var mboCalcModal = new Tabulator("#mboCalcForm", {
              layout:"fitColumns",
              groupBy:"item",
              groupStartOpen:false,
              selectable:true,
              data:mboTableResult,
              columns:[
                  {title:"Item", field:"item", formatter:"textarea", width:150},
                  {title:"Rating", field:"rating", formatter:"textarea", width:150},
                  {title:"Weight", field:"weight", formatter:"textarea", width:100},
                  {title:"Metric", field:"metric", formatter:"textarea", width:750},
              ],
              initialSort:[
                  {column:"rating", dir:"asc"},
                  {column:"item", dir:"asc"}
              ],
              rowClick:function(e, row){
                let selRows = mboCalcModal.getSelectedData();
                runningAvg = 0.0;
                runningAvgRating = '';
                selRows.forEach(function(e) {
                    // Fixes the bootbox modal deleting the scrollbar when other modal is open
                    $('.bootbox.modal').on('hidden.bs.modal', function () {
                      if($('.modal').hasClass('in')){
                                 $('body').addClass('modal-open');
                             }
                    });
                    var valueToAdd = e.weight/100 * e.num_rating;
                    runningAvg += valueToAdd;
                    let checkGroup = row.getGroup().getRows();
                    let countGroup = 0;
                    for (let i=0; i<checkGroup.length; i++) {
                        if (checkGroup[i]._row.modules.select.selected == true) {
                            countGroup++;
                        }
                        while (countGroup > 1) {
                            row.deselect();
                            countGroup = 0;
                            runningAvg -= valueToAdd; // Added so that the displayed value doesn't temporarily show an erroneous value
                            bootbox.alert({
                                message: "You may only select one rating per MBO. Select an item again to deselect it.",
                                className: 'animated jello',
                                backdrop: true
                            });
                        }
                    }
                    if (Math.round(runningAvg) > 3) {
                        runningAvgRating = "Unsuccessful";
                    }
                    else if (Math.round(runningAvg) > 2) {
                        runningAvgRating = "Successful";
                    }
                    else if (Math.round(runningAvg) > 1) {
                        runningAvgRating = "Exceeds Some";
                    }
                    else if (Math.round(runningAvg) <= 1) {
                        runningAvgRating = "Exceeds Most";
                    }
                });
                $('#runningAvg').replaceWith('<h4 align="right" id="runningAvg">Numeric: </h4>');
                $('#runningAvg').append(fixedRound(runningAvg));
                $('#runningAvgRating').replaceWith('<h4 align="right" id="runningAvgRating">Alpha: </h4>');
                $('#runningAvgRating').append(runningAvgRating);
            },
          });
        }
        // Table wasn't loading until sorted, this forces it.
        mboCalcModal.redraw();
    })
});
