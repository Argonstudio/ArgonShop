$(".as-sort").click(function(){
    
    var sorting = $(this);
    
    var typeSort = sorting.attr("name");
    var catId = sorting.parent(".sortParameters").attr("data-cat-id");
    var pageNum = sorting.parent(".sortParameters").attr("data-page");
    
    var howSorting;
   
    if( sorting.hasClass("sort-max") ){
        
        //От меньшего к большему
        howSorting = "ASC";
        
    }else{
        
        howSorting = "DESC";
        
    }
    //console.log(catId);
    sortingCatalog(sorting, typeSort, howSorting, catId, pageNum);
    
})

function successSortingCatalog(response, sorting, howSorting){    
    
    $(".as-sort").each(function(index,element){        
            
        $(element).removeClass("sort-min");
        $(element).removeClass("sort-max");       
        
    })
    
    if( howSorting === "ASC" ){
        
        sorting.addClass("sort-min");
        
    }else if(howSorting === "DESC"){
        
        sorting.addClass("sort-max");
        
    }
    
    var productList = $(".catalogProductList");
    productList.html(response);
    
    var blockError = $(".as-error-sort");
    blockError.css("display","none");
    
}

function errorSortingCatalog(response){
    
    console.log(response);
    
    var blockError = $(".as-error-sort");
    
    blockError.css("display","block");
    blockError.html("Ошибка на сервере");
}