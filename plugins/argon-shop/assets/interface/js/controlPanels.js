jQuery(document).ready(function(){
    
    /**
     * Панели управления(панели переключатели)
     * =========================================== */
     
     var controlPanel = false;
     var mobileControlPanel = false;
     var mobileWidth = 0;
     
     var classItemPanel = ".itemControlPanel";
     
     if( $(".itemControlPanel").length > 0 ){
         
         controlPanel = true;
         
     }
     
     if( $(".mobileItemControlPanel").length > 0 ){
         
         mobileControlPanel = true;
         mobileWidth = $(".mobileItemControlPanel").attr("data-mobileWidth");
     }
     
     if(mobileWidth !== 0 && window.innerWidth <= mobileWidth ){
         
         classItemPanel = ".mobileItemControlPanel";
         
     }
     
     function viewBlockItemPage(type, elementName){
         
         switch(type){
             case "switch":
                $(".blockItemPage").css("display","none");
                
                $(classItemPanel).filter(".itemControlPanelActive").each(function(index, element){
                    
                    $(".blockItemPage[id='block_"+ $(element).attr("name") +"' ]").css("display","block");
                    
                })
                
                break;
                
             case "list_close":
                 $(".blockItemPage[id='block_"+ elementName +"' ]").css("display","none");
                 break;
                 
             case "list_open":
                 $(".blockItemPage[id='block_"+ elementName +"' ]").css("display","block");
                 break; 
                 
         }
         
         
     }

     if( controlPanel && classItemPanel === ".itemControlPanel" ){
         
         viewBlockItemPage("switch");
         
     }
     
     if( mobileControlPanel && classItemPanel === ".mobileItemControlPanel" ){
         
         viewBlockItemPage("switch");
         
     }
     
     $(".itemControlPanel, .mobileItemControlPanel").click(function(){
         
         //Проверяем содержит ли пункт актуальный класс
         //Убираем точку для правильной работы hasClass
         if( !$(this).hasClass(classItemPanel.substr(1) ) ){
             return;
         }
         
         //Переключатель
         if( $(this).attr("data-type") === "switch" ){
             
             $(classItemPanel).removeClass("itemControlPanelActive");
             $(classItemPanel+"[name='"+ $(this).attr("name") +"' ]").addClass("itemControlPanelActive");
             
             viewBlockItemPage("switch");
         
         //Список    
         }else if($(this).attr("data-type") === "openingList"){
             
             if( $(this).hasClass("itemControlPanelActive") ){
                 
                 $(classItemPanel+"[name='"+ $(this).attr("name") +"' ]").removeClass("itemControlPanelActive");
                 viewBlockItemPage("list_close", $(this).attr("name"));
                 
             }else{
                 
                 $(classItemPanel+"[name='"+ $(this).attr("name") +"' ]").addClass("itemControlPanelActive");
                 viewBlockItemPage("list_open", $(this).attr("name"));
                 
             }
             
         }
         
         
     })
     
     //Корректировка при изменении разрешения
     $(window).resize( function() {
         
         if( controlPanel && mobileControlPanel ){
             
             if( window.innerWidth > mobileWidth && classItemPanel === ".mobileItemControlPanel" ){
                 
                 classItemPanel = ".itemControlPanel";
                 viewBlockItemPage("switch");
                 
             }else if( window.innerWidth <= mobileWidth && classItemPanel === ".itemControlPanel" ){
                 
                 classItemPanel = ".mobileItemControlPanel";
                 viewBlockItemPage("switch");
                 
             }
             
         }
         
     });
     
});