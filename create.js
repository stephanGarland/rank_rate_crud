const parsedUrl = new URL(window.location.href);
const url_ghr = parsedUrl.searchParams.get("id");
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

function getSups(){
    $.ajax({
        type: 'GET',
        url: 'getSups.php',
        dataType: 'json',
        error: errorMessage()
    }).done(function(response) {
            var supArray = response;
            for (let i=0; i<supArray.length; i++){
                $('#supChoiceReassignButton').append('<option value="' + supArray[i].employee + '">' + supArray[i].employee + "</option>");
            }
        });
    }


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
/*console.log("jqXHR");
console.log(jqXHR);
console.log("textStatus");
console.log(textStatus);
console.log("errorThrown");
console.log(errorThrown);*/
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
        type: 'POST',
        url: 'getEmpDetails.php',
        cache: false,
        data: {
            url_ghr : url_ghr,
            curQuarter : selQtr,
            curYear : selYear
        },
        // The callback is sending the response from getEmpDetails, which is its JSON being echoed, to getEmpEntries
        error: errorMessage(),
        success: function(reports_to) {
            callback(reports_to, url_ghr);
        }
      }).done(function(empDetails) {
            row_id = empDetails.row_id;
            $('#emp_name_val').val(empDetails.full_name);
            $('#emp_name_holder_val').val(empDetails.full_name);
            $('#emp_ghr_val').val(empDetails.ghr_id);
            $('#emp_title_val').val(empDetails.title);
            $('#sup_name_val').val(empDetails.reports_to_name);
            $('#sup_name_holder_val').val(empDetails.reports_to_name);
            $('#quarter_val').val(selQtr);
            $('#year_val').val(selYear);
            $('#overall_ranking_val').val(empDetails.overall_ranking);
            $('#run_avg_val').val(empDetails.running_avg);
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

// This could all be cleaned up, probably does not need the convoluted callback
function getEmpEntries(reports_to, url_ghr) {
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
        empEntries = empEntries.filter(item => item.ghr_id == url_ghr)[0];
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
    let ghr_id = url_ghr;
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
      ghr_id : url_ghr
    },
    datatype: 'json',
    error: errorMessage(),
    success: function(response) {
        checkIfExist(response);
    }
});


$('#bulk_reassign_check').click(function() {
    if (!$('#bulk_reassign_check')[0].checked) {
        $('#emp_name_holder_val').val($("#emp_name_val").val());
    }
    else {
        $('#emp_name_holder_val').val("All");
    }
});

$('#supChoiceReassignButton').click(function() {
    var to_be_sup = $('#supChoiceReassignButton').val();
    $('#new_sup_name_holder').val(to_be_sup);
});

$('#reassignSubmitButton').click(function(event) {
    $.ajax({
        type: 'POST',
        url: 'sqlEditPush.php',
        data: {
            ghr_id: url_ghr,
            new_sup_name: $('#new_sup_name_holder').val(),
            cur_sup_name: $('#sup_name_val').val(),
            year: selYear,
            quarter: selQtr,
            bulk_bool: $('#bulk_reassign_check')[0].checked
        },
        datatype: 'json',
        success: bootbox.alert("Success!")
    });
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
                        ghr_id: url_ghr,
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
          id: url_ghr,
          quarter: sessionStorage.selQtr,
          year: sessionStorage.selYear
       },
      datatype: 'html',
      error: errorMessage()
    });
    return mboData;
}

$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
    getSups();
    var runningAvg;
    var runningAvgRating;
    returnMBO().then(function(mbos) {
        let mboTableResult = [];
        let filteredMBO = mbos.filter(function(e) {
          if (e.worker_title == $('#emp_title_val')[0].value) {
              mboTableResult.push(e);
              console.log(mboTableResult);
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
});
