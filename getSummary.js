/*  agGrid.LicenseManager.setLicenseKey("YOUR_LICENSE_KEY");
    Note that nothing this app is using requires* the Enterprise version
    Community version is MIT-licensed, so it's fine for use anywhere

    *Fine, you can't Pivot, and you can't Export to Excel, so maybe it's kinda necessary
    If you're interested, make friends with Jesse Ortega and he'll share the key - you just can't develop with it.
*/
const parsedUrl = new URL(window.location.href);
const urlSup = parsedUrl.searchParams.get("selectedSup");
var tableResult = '';

function getRowData() {
    $.ajax({
        type: 'GET',
        url: 'getSummary.php',
        dataType: 'json',
        data: {selectedSup : urlSup},
        success: function(response){
            tableResult = response;
            summaryTableOptions.api.setRowData(tableResult);
            buildPlot();
        }
    });
}

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
    onCellValueChanged: onFilterChanged,
    enableSorting: true,  // v18
    enableFilter: true,  // v18
    defaultColDef: {
        /*enableSorting: true, v21
        enableFilter: true, v21 */
        editable: true
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
    // Get the name, quarter, and year, then pass it to a modal with some sanitizing
    // but only if the name is clicked on - allows editing of other cells to take place
    let testForName = summaryTableOptions.api.getFocusedCell();
    if (testForName.column.colId == 'full_name') {
        let getCellData = [params.data['full_name'], params.data['quarter'], params.data['year']];
        let found = tableResult.find( (e => e.full_name === getCellData[0] && e.quarter === getCellData[1] && e.year === getCellData[2]));
        let writeupText = found.writeup.replace(/\\n/g, "\n").replace(/\\r/g, "\r").replace(/\\t/g, "\t").replace(/\n/g, "<p>");
        $('#empWriteup .modal-body').html("<p>" + writeupText);
        $('#empWriteup').modal('show');
    }
}

var summaryTableGrid = document.querySelector('#summaryTable');
new agGrid.Grid(summaryTableGrid, summaryTableOptions);
getRowData();

function buildPlot() {
    // Initial build of table, pulls currently displayed data in ag-grid (which will be all of it)
    var layout = {
        barmode: 'group',
        autosize: false,
        width: 1800,
        height: 600
    };
    var plotData = getTableFromAgGrid();
    Plotly.newPlot('distPlot', plotData, layout);
}
