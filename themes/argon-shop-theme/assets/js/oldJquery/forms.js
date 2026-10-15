/* Архивная копия старого jQuery-кода 2018 года.

Файлы в папке oldJquery не нужны при установке плагина и могут быть удалены; они сохранены только для GitHub.

Позже я переписал всё на чистом современном JS, который теперь живёт в плагине. В файлах темы остались только CSS и PHP-шаблоны. */

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
        
        // Находим на странице все кнопки с классом .submitCart
        const submitButtons = document.querySelectorAll('.submitCart');
        
        // Перебираем каждую найденную кнопку
        submitButtons.forEach(button => {
            // Если чекбокс отмечен, disabled выключается (false), иначе включается (true)
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