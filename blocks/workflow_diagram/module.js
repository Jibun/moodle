
M.block_workflow_diagram = {
    
    Y : null,
    transaction : [],
    
    printgraph : function(Y, params){
        this.Y = Y;
    
        //First, get the date
        
        var date = new Date();
        //date.setDate(30);

        var days = new Array;
        var months = new Array;
        var years = new Array;
        var actMonth, actYear;
        days[0] = date.getDate();
        actMonth = date.getMonth()+1;
        months[0] = actMonth;
        actYear = date.getFullYear();
        years[0] = actYear;
        
        for(var i=1; i<5; i++){     // 5 days
            days[i] = days[i-1]+1;
            if (days[i] > 30){      // It depends on the month.         
                days[i]=1;
                actMonth += 1;
            }
            if(actMonth == 13){
                actMonth = 1;
                actYear += 1;
            }
            months[i] = actMonth;
            years[i] = actYear;
        }
        
        YUI().use('charts', function (Y) {
            // Data for the chart
            var datavalues = [ 
                {date: months[0] + "/" + days[0] + "/" + years[0], calcul:2, fisica:0}, 
                {date: months[1] + "/" + days[1] + "/" + years[1], calcul:0, fisica:2}, 
                {date: months[2] + "/" + days[2] + "/" + years[2], calcul:1, fisica:1}, 
                {date: months[3] + "/" + days[3] + "/" + years[3], calcul:2, fisica:1}, 
                {date: months[4] + "/" + days[4] + "/" + years[4], calcul:2, fisica:0}
            ];

            var mychart = new Y.Chart({
                dataProvider:datavalues, 
                render:"#mychart", //Indica el <div> on es mostrarà
                type:"column", //Tipus de gràfica, es pot canviar
                width: 500,
                height: 300,
                title: "Workflow Diagram",
                stacked:true,
                categoryKey:"date", 
                categoryType:"time",
                
                horizontalGridlines: {
                    styles: {
                        line: {
                            color: "#dad8c9"
                        }
                    }
                },
                verticalGridlines: {
                    styles: {
                        line: {
                            color: "#dad8c9"
                        }
                    }
                }
            });
        });
    }
}