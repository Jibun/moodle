
M.block_workflow_diagram = {
    
    Y : null,
    transaction : [],
    
    printgraph : function(Y, params){
        this.Y = Y;
        
        YUI().use('charts', function (Y) {
            
            //Get data from JSON string parameter
            var data = eval('(' + params + ')');

            var mychart = new Y.Chart({
                dataProvider:data,
                render:"#mychart", //Indica el <div> on es mostrarà
                type:"column", //Tipus de gràfica, es pot canviar
                title: "Workflow Diagram",
                stacked:true,
                categoryKey:"date", 
                //categoryType:"time",
                
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