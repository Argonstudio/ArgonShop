$(".asInputSearchForm").on('input', function(){
    
    var searchStr = $(this).val();
    var dataAjax = $(this).attr("data-ajax");
    
    var classBlockResult = $(this).attr('data-blockresult');
    var blockResult = $("."+classBlockResult);
    
    if( isJson(dataAjax)){
        
        if( searchStr.length > 1 ){
            console.log(blockResult.html());
            if( blockResult.html() === '' ){
                blockResult.html('Ищем результаты...')
            }
            
            getAjaxSearchResult($(this), dataAjax, searchStr, blockResult);
            blockResult.css("display","block")
            
        }else if(blockResult.css("display") == "block"){
            blockResult.css("display","none");
            
        }        
        
    }
    
});

$(".asInputSearchForm").on('focus', function(){
    
    $(this).trigger('input');
    
});

$(document).mouseup(function (e){ // событие клика по веб-документу

		var blockResult = $(".searchAjaxResult"); // тут указываем набор элементов
		
		var classElement = e.target.className;
		
		//console.log(e.target.className);
		if(blockResult && classElement.lastIndexOf("asInputSearchForm") === -1){
		    
		    blockResult.each(function(index,element){
		    
    		    if( $(element).css("display") === "block" ){
    		        
    		        //Проверяем что клик не на самом блоке с результатами и не на его дочерних
    		        if (!$(element).is(e.target) && $(element).has(e.target).length === 0) {
    		            //console.log(1);
            			$(element).css("display","none")
            		}
    		        
    		    }
    		    
    		})
		    
		}
		
	});


/**
* Вспомогательные функции(получение данных, проверка итп) 
*/

//Проверка на json
function isJson(item) {
    item = typeof item !== "string"
        ? JSON.stringify(item)
        : item;

    try {
        item = JSON.parse(item);
    } catch (e) {
        return false;
    }

    if (typeof item === "object" && item !== null) {
        return true;
    }

    return false;
}

