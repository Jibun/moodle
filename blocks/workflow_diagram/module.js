
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
            var myDataValues = [ 
                {category: days[0] + "/" + months[0] + "/" + years[0], calcul:2, fisica:0}, 
                {category: days[1] + "/" + months[1] + "/" + years[1], calcul:0, fisica:2}, 
                {category: days[2] + "/" + months[2] + "/" + years[2], calcul:1, fisica:1}, 
                {category: days[3] + "/" + months[3] + "/" + years[3], calcul:2, fisica:1}, 
                {category: days[4] + "/" + months[4] + "/" + years[4], calcul:2, fisica:0}
            ];

            var mychart = new Y.Chart({
                dataProvider:myDataValues, 
                render:"#mychart", //Indica el <div> on es mostrarà
                type:"combo", //Tipus de gràfica, es pot canviar
                width: 500,
                height: 300,
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
                },
                stacked:true
            });
        });
    }
}