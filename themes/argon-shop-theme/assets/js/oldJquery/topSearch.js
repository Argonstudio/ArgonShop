/* Archived copy of the old 2018 jQuery code.

The files in the oldJquery folder are not needed when installing the plugin and can be deleted; they are kept only for GitHub.

Later I rewrote everything in plain modern JS, which now lives in the plugin. Only the CSS and PHP templates remained in the theme files. */

 $(".block-topSearch").mouseleave(function(){           
                $(".topSearchBlockResult").css("display","none");
                $(".topSearchInput").blur();
        });