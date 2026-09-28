jQuery(document).ready(function(){

jQuery(".choiceCategory").find('input[type="button"]').on('click', function (event) {

    
    if( jQuery(this).parent("li").attr("class") == "checkCategory" ){
        
        
            if( jQuery(this).parent("li").find("ul").css("display") === "none" ){
                jQuery(this).parent("li").children("ul").css('display', "block");
            }else{
                jQuery(this).parent("li").find("ul").css('display', "none");
            }
        
            
    }    
    
     
});

jQuery(".choiceCategory").find('input[type="checkbox"]').on('change', function (event) {

    
    if( jQuery(this).parent("li").attr("class") == "checkCategory" ){
        
            jQuery(this).parent("li").find("input:checkbox").prop('checked',jQuery(this).prop("checked"));
        
    }else if( jQuery(this).parent("li").attr("class") == "checkAllCategory" ){
        
        jQuery(".choiceCategory").find("li").find("input:checkbox").prop('checked',jQuery(this).prop("checked"));
        
    }
    
     
});


jQuery("#taxonomy-catalog").find('input[type="checkbox"]').on('change', function (event) {
    
    //Получаем выбранные категории
    var idCategory = [];
    
    jQuery("#taxonomy-catalog").find('input[type="checkbox"]:checked').each(function(index, element){
        
        var string = jQuery(this).attr("id").split("-");
        
        if(string.length == 3){
            string.splice(0,1);
        }else if(string.length == 4){
            string.splice(0,2);
        }        
        
        if( jQuery.inArray(string[1], idCategory) ){
            
            idCategory.push(string[1]);
            
        }
        
    })
    
    if(idCategory.length < 1){
        idCategory = "no categories"
    }
    
    //В случае если добавили новую категорию
        
        //Получаем значения полей и чекбоксов на данный момент
        var characteristicsTextField = [];
        var characteristicsCheckboxField = [];
        
        
        //Ищем все input элементы чей name начинается с characteristics_text_field 
        jQuery(".advanced-field").find("input[name^='characteristics_text_field']").each(function(index, element){
            
            var elementName = jQuery(element).attr("name");
            
            //Получаем id, обрезая в строке первый и последний символ ([1] = 1)
            var elementId = elementName.split("[")[1].slice(0,elementName.split("[")[1].length-1);
            
            characteristicsTextField[elementId] = jQuery(element).attr("value");
        });
        
        
        //Ищем все input элементы чей name начинается с characteristics_checkbox_field, тип checbox, состояние выбран 
        jQuery(".advanced-field").find("input[name^='characteristics_checkbox_field'][type='checkbox']:checked").each(function(index, element){
            
            
            var elementName = jQuery(element).attr("name");
            var elementId = elementName.split("[")[1].slice(0,elementName.split("[")[1].length-1);
            var elementParentId = jQuery(element).parent().attr("data-parentid");
            
            
            if(!characteristicsCheckboxField[elementParentId]){
                characteristicsCheckboxField[elementParentId] = [];
            } 
             
            characteristicsCheckboxField[elementParentId][elementId] = jQuery(element).attr("value");
            
        });
        
    //}
    
    
        
//Вызываем ajax функцию и передаем её выбранные категории и текущие значения полей    
updateCharacteristics(idCategory,characteristicsTextField,characteristicsCheckboxField);

        
});

jQuery("#settingPageSave").click(function(){
    
    var cartPageID = jQuery("#shoppingCartPage").val();
    var cabinetPageID = jQuery("#cabinetPage").val();
    
    saveSettingPage(cartPageID, cabinetPageID)
})

});