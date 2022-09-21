function drawChart(incomes, expenses)
{
    google.charts.load('current', {'packages':['corechart']});
    
    if(Array.isArray(incomes) && incomes.length) {
       
        google.charts.setOnLoadCallback(drawIncomesChart);
    }
                
   if(Array.isArray(expenses) && expenses.length) {
        
        google.charts.setOnLoadCallback(drawExpensesChart);
    }
    
    function drawIncomesChart() {
        
        var incomesData = new google.visualization.DataTable();
        incomesData.addColumn('string', 'Kategoria');
        incomesData.addColumn('number', 'Kwota');
        
		for (var i = 0; i < incomes.length; i++) {
            
            incomesData.addRow([incomes[i].name, parseFloat(incomes[i].amount)]);
        }
        
        var incomesOptions = {
            title: 'Przychody',
            colors: ['#cda3bc', '#cdb4cc', '#cdb5bc', '#cdc3bc', '#cdc5bc'],
            backgroundColor: { fill:'transparent' },
            chartArea:{top:30,bottom:10,width:'100%',height:'100%'},
            fontSize: 17,
            fontName: 'Nunito',
            legend: {position: 'right', textStyle: {color: '#404040', fontSize: 17}},
            titleTextStyle: {color: '#404040', fontSize: 17},
            pieSliceTextStyle: {color: '#404040', fontSize: 17},
            tooltip: {textStyle: {color: '#404040', fontSize: 15}}
        };
        
        var incomesChart = new google.visualization.PieChart(document.getElementById('piechart1'));
        incomesChart.draw(incomesData, incomesOptions);
    }
    
    function drawExpensesChart() {
        
        var expensesData = new google.visualization.DataTable();
        expensesData.addColumn('string', 'Kategoria');
        expensesData.addColumn('number', 'AKwota');
        
        var expensesOptions = {
            title: 'Wydatki',
            colors: ['#87CEFA', '#00BFFF', '#6495ED', '#4169E1', '#1E90FF'],
            backgroundColor: { fill:'transparent' },
            chartArea:{top:30,bottom:10,width:'100%',height:'100%'},
            fontSize: 17,
            fontName: 'Nunito',
            legend: {position: 'right', textStyle: {color: '#404040', fontSize: 17}},
            titleTextStyle: {color: '#404040', fontSize: 17},
            pieSliceTextStyle: {color: '#404040', fontSize: 17},
            tooltip: {textStyle: {color: '#404040', fontSize: 15}}
        };
        
        for (var i = 0; i < expenses.length; i++) {
            
            expensesData.addRow([expenses[i].name, parseFloat(expenses[i].amount)]);
        }
        
        var expensesChart = new google.visualization.PieChart(document.getElementById('piechart2'));
        expensesChart.draw(expensesData, expensesOptions);
    }
}
function expandTableRows()
{
    $('tr.header').click(function(){
        
        $(this).nextUntil('tr.header').css('display', function(i,v) {
            
            return this.style.display === 'table-row' ? 'none' : 'table-row';
        });
    });
}