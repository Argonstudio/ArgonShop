/* Архивная копия старого jQuery-кода 2018 года.

Файлы в папке oldJquery не нужны при установке плагина и могут быть удалены; они сохранены только для GitHub.

Позже я переписал всё на чистом современном JS, который теперь живёт в плагине. В файлах темы остались только CSS и PHP-шаблоны. */

 $(".block-topSearch").mouseleave(function(){           
                $(".topSearchBlockResult").css("display","none");
                $(".topSearchInput").blur();
        });