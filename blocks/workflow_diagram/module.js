
M.block_workflow_diagram = {
    
    Y : null,
    transaction : [],
    
    printgraph : function(Y, params){
        this.Y = Y;
        
        YUI().use('charts', function (Y) {
            // Data for the chart
            var myDataValues = [ 
                {category:"5/1/2010", calcul:2, fisica:0}, 
                {category:"5/2/2010", calcul:0, fisica:2}, 
                {category:"5/3/2010", calcul:1, fisica:1}, 
                {category:"5/4/2010", calcul:2, fisica:1}, 
                {category:"5/5/2010", calcul:2, fisica:0}
            ];

            var mychart = new Y.Chart({
                dataProvider:myDataValues, 
                render:"#mychart", 
                type:"combo", 
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