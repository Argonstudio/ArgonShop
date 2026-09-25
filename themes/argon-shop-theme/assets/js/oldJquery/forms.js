/* Архивная копия старого Jquery 2018 года

При установке плагина файлы из папки oldJquery не нужны и их можно удалить, это только для Гитхаба

Позднее я переписал все на чистый современный JS, сейчас он в плагине.В файлах же темы остался css и php шаблоны. */

$(".block-regQuestion1").click(function(){
  
    if($('.cartReg').is(':checked')){
        
        $('.cartReg').prop('checked', false);
        $(".cartLogin").css("display","none");
        
    } else {
        
        $('.cartReg').prop('checked', true);
        $(".cartLogin").css("display","block");
        
    } 
    
})

function toggleSubmitButton(type = 'basket') {
    
    if(type === 'basket'){
        
        // Получаем состояние чекбокса (true/false)
        const isChecked = document.getElementById('agree_checkbox').checked;
        
        // Находим все кнопки с классом .submitCart на странице
        const submitButtons = document.querySelectorAll('.submitCart');
        
        // Перебираем каждую найденную кнопку в цикле
        submitButtons.forEach(button => {
            // Если галочка стоит — disabled выключается (false), если нет — включается (true)
            button.disabled = !isChecked;
        });
        
    }else if(type === 'account'){
        
        // Получаем состояние чекбокса (true/false)
        const isChecked = document.getElementById('agree_checkbox_account').checked;
        
        // Находим кнопку
        const submitButton = document.getElementById('accountSaveButton');
        
        submitButton.disabled = !isChecked;
        
    }else if(type === 'setting'){
        
        // Получаем состояние чекбокса (true/false)
        const isChecked = document.getElementById('agree_checkbox_setting').checked;
        
        // Находим кнопку
        const submitButton = document.getElementById('editEmailPasswordButton');
        
        submitButton.disabled = !isChecked;
        
    }
    
    
}