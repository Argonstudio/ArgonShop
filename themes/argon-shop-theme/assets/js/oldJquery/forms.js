/* Archived copy of the old 2018 jQuery code.

The files in the oldJquery folder are not needed when installing the plugin and can be deleted; they are kept only for GitHub.

Later I rewrote everything in plain modern JS, which now lives in the plugin. Only the CSS and PHP templates remained in the theme files. */

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
        
        // Get the checkbox state (true/false)
        const isChecked = document.getElementById('agree_checkbox').checked;
        
        // Find all buttons with the .submitCart class on the page
        const submitButtons = document.querySelectorAll('.submitCart');
        
        // Loop over every button found
        submitButtons.forEach(button => {
            // If the box is checked, disabled is turned off (false), otherwise on (true)
            button.disabled = !isChecked;
        });
        
    }else if(type === 'account'){
        
        // Get the checkbox state (true/false)
        const isChecked = document.getElementById('agree_checkbox_account').checked;
        
        // Find the button
        const submitButton = document.getElementById('accountSaveButton');
        
        submitButton.disabled = !isChecked;
        
    }else if(type === 'setting'){
        
        // Get the checkbox state (true/false)
        const isChecked = document.getElementById('agree_checkbox_setting').checked;
        
        // Find the button
        const submitButton = document.getElementById('editEmailPasswordButton');
        
        submitButton.disabled = !isChecked;
        
    }
    
    
}