function initWarn() {
    bootbox.prompt("<h2><center>STOP!</center><p /> You can destroy records from this page. " +
    "To continue editing, type the following:<p />" +
    "<font color='red'>nowiambecomedeath</font></h2>", function(result){
        if (result == null) {
            location.href='index.php';
        }
        else if (result != 'nowiambecomedeath') {
            console.log(result);
            initWarn();
        }
    });
}

//initWarn();

var selYear;
var selQtr;
var selSup;

for (let i=2018; i<=2038; i++){
    $('#selectYear').append("<option value=" + i + ">" + i + "</option>");
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
}

function getSelSup(){
    return selSup;
}

$.ajax({
    type: 'GET',
    url: 'getSups.php',
    dataType: 'json',
    }).done(function(response) {
        var supArray = response;
        for (let i=0; i<supArray.length; i++){
            $('#supChoiceButton').append('<option value="' + supArray[i].employee + '">' + supArray[i].employee + "</option>");
        }
});

function reassignButton(){
        let reassignBtn = function(cell, formatterParams){
            return "<a class='btn btn-success' href=sqlEditPush.php?ghr_id="
                    + cell.getRow().getData().ghr_id + "&sup_ghr_id=" + encodeURIComponent(getSelSup())
                    + "&year=" + getSelYear() + "&qtr=" + getSelQtr()
                    +  ">Reassign</a>";
        };
        return reassignBtn;
}

function getEmpEntries() {
  let reports_to_name = getSelSup();
  $.ajax({
        type: 'POST',
        url: 'getEmpEntries.php',
        cache: false,
        data: {
            reports_to: reports_to_name,
            curQuarter: getSelQtr(),
            curYear: getSelYear()
        }
    });
}


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
            {title:"Action", formatter:reassignButton(), align:"center"},
            {title:"Action", formatter:reassignButton(), align:"center"},
        ],
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
    }).done(function(response) {
            var empArray = response;
            fillEmpTable(empArray);
    });
}

var tableResult;
function fillEmpTable(empArray){
    tableResult = empArray;

    for (const key of Object.keys(tableResult)) {
      if (tableResult[key].term_date) {
          delete tableResult[key];
        }
    }
    createSummaryTable(tableResult);
}

$('#selectYear').on('click', function() {
    setSelYear(this.value);
    //updateReadIcon();
    getEmpEntries();
    getReports(getSelSup());
    if (typeof tableResult !== 'undefined') {
        summaryTable.replaceData(tableResult);
    }
});

$('#selectQtr').on('click', function() {
    setSelQtr(this.value);
    //updateReadIcon();
    getEmpEntries();
    getReports(getSelSup());
    if (typeof tableResult !== 'undefined') {
        summaryTable.replaceData(tableResult);
    }
});

$('#supChoiceButton').on('click', function() {
    setSelSup(this.value);
    getReports(this.value);
});
