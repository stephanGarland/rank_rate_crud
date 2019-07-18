$("#getShiftSummary").append('<input id="summarizeShift" type="button" onclick="getShiftSummary()" class="btn btn-primary" value="Summarize Shift" />');
$("#getAllSummary").append('<input id="summarizeAll" type="button" onclick="getAllSummary()" class="btn btn-primary" value="Summarize All" />');

var selYear;
var selQtr;
var selSup;

for (let i=2018; i<=2038; i++){
    $('#selectYear').append("<option value=" + i + ">" + i + "</option>");
    $('#bulkSelectYear').append("<option value=" + i + ">" + i + "</option>");
}

function setSelYear(input){
    selYear = input;
}

function getSelYear(){
    return selYear;
}

function setSelQtr(input){
    selQtr = input;
}

function getSelQtr(){
    return selQtr;
}

function setSelSup(input){
    selSup = input;
    sessionStorage.selSup = selSup;
}

function getSelSup(){
    return selSup;
}

function getSups(){
    $.ajax({
        type: 'GET',
        url: 'getSups.php',
        dataType: 'json',
        error: errorMessage()
    }).done(function(response) {
            var supArray = response;
            for (let i=0; i<supArray.length; i++){
                $('#supChoiceButton').append('<option value="' + supArray[i].employee + '">' + supArray[i].employee + "</option>");
                if (typeof sessionStorage.selSup !== 'undefined') {
                    $('#supChoiceButton').val(sessionStorage.selSup);
                }

            }
        });
    }

function getReports(sup){
    $.ajax({
        type: 'POST',
        url: 'getReports.php',
        dataType: 'json',
        data: {
            supChoice : sup,
            year : getSelYear(),
            qtr : getSelQtr()
        },
        error: errorMessage()
    }).done(function(response) {
            var empArray = response;
            fillEmpTable(empArray);
    });
}

function updateReadIcon(input){
        let readUpdateIcon = function(cell, formatterParams){
            return "<a class='btn btn-success read_update_btn' href=create.html?id="
                    + cell.getRow().getData().ghr_id + "&year=" + getSelYear() + "&qtr=" + getSelQtr()
                    +  ">Read/Update</a>";
        };
        return readUpdateIcon;
}

// Need to make this more elegant
var summaryTable;
var shift_ghrs = [];
function createSummaryTable(tableResult){
    summaryTable = new Tabulator("#shiftTable", {
        layout:"fitColumns",
        data:tableResult,
        columns:[
            {title:"GHR", field:"ghr_id"},
            {title:"Subgroup", field:"subgroup"},
            {title:"Name", field:"full_name"},
            {title:"Title", field:"title"},
            {title:"Action", formatter:updateReadIcon(), align:"center"}
        ],
    });
}

// Need to make this more elegant
var tableResult;
function fillEmpTable(empArray){
    tableResult = empArray;

    /*
        Due to how SQL query is written, filtering out any non-null term_date there
        would have the effect of just pulling in the first quarter where the now-terminated
        employee was NOT terminated. This way allows them to pass but then deletes their entry
        from the object before it's sent to Tabulator.
    */
    for (const key of Object.keys(tableResult)) {
      if (tableResult[key].term_date) {
          delete tableResult[key];
        }
    }

    // Used for doing a bulk record add in saveNewRecord
    $.ajax({
        type: 'POST',
        url: 'getGHRs.php',
        dataType: 'json',
        data: {
                sup_name: getSelSup(),
                qtr: getSelQtr(),
                year: getSelYear()
        },
        error: errorMessage(),
        success: function(response){
            response.forEach(function(e){
                shift_ghrs.push(e.ghr_id);
            });
        }
    });
    createSummaryTable(tableResult);
    return shift_ghrs;
}


$(document).ready(function() {
    getSups();
    if (document.referrer){
        referringURL = document.referrer;
        // If create.html is anywhere in the string, it will be > -1
        if (referringURL.indexOf('create.html') > -1){
            setSelSup(sessionStorage.selSup);
            getReports(getSelSup()); // This may not be required, investigate
            replQtrYear();
        }
    }

    $('#selectYear').on('click', function() {
        setSelYear(this.value); // This allows the right value to pass to create
        storeQtrYear(); // This allows the right value to be present upon return
        updateReadIcon();
        getEmpEntries();
        getReports(getSelSup());
        if (typeof tableResult !== 'undefined') {
            summaryTable.replaceData(tableResult);
        }
    });

    $('#selectQtr').on('click', function() {
        setSelQtr(this.value);
        storeQtrYear();
        updateReadIcon();
        wipeShiftRankingTable();
        getEmpEntries();
        getReports(getSelSup());
        if (typeof tableResult !== 'undefined') {
            summaryTable.replaceData(tableResult);
        }
    });

    $('#bulkSelectQtr').on('click', function() {
        var bulkSelectQtr = this.value;
    });

    $('#bulkSelectYear').on('click', function() {
        var bulkSelectYear = this.value;
    });

    $('#supChoiceButton').on('click', function() {
        setSelSup(this.value);
        getReports(this.value);
    });

});


function getShiftSummary(){
    window.open("getSummary.php?selectedSup=" + JSON.stringify(getSelSup()));
}

function getAllSummary(){
    window.open("getSummary.php?selectedSup=" + JSON.stringify("%"));
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


$('#addRecordModalSave').click(function() {
  $.ajax({
      type: 'POST',
      url: 'saveNewRecord.php',
      data: {
          shift_ghrs: JSON.stringify(shift_ghrs),
          qtr: bulkSelectQtr[0].value,
          year: bulkSelectYear[0].value
      },
      dataType: 'json',
      error: errorMessage(),
      success: success()
  })
});


function getEmpEntries() {
  let reports_to_name = getSelSup();
  $.ajax({
        type: 'POST',
        url: 'getEmpEntries.php',
        cache: false,
        data: {
            reports_to: reports_to_name,
            curQuarter: getSelQtr(),
            curYear: getSelYear(),
            subGroup: activeSubgroup
        }
      }).done(function(empEntries) {
          // Sort by shift_ranking so they're appended in order
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
    sessionStorage.selQtr = $("#selectQtr").val();
    sessionStorage.selYear = $("#selectYear").val();
}


function replQtrYear() {
    if (sessionStorage.selQtr && sessionStorage.selYear && document.referrer.indexOf("create") !== -1){
        setSelQtr(sessionStorage.selQtr);
        setSelYear(sessionStorage.selYear);
        $("#selectQtr").val(getSelQtr());
        $("#selectYear").val(getSelYear());
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
