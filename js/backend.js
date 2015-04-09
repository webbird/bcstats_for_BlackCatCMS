jQuery(document).ready(function($) {
    $( "div.mod_bcstats div.fc_widget_content.accordion" ).accordion({
        collapsible: true,
        active: false
    });
/*
    var max_widget_height = 0;
    $('.fc_widget_wrapper').each(function() {
        if($(this).height() > max_widget_height) {
            max_widget_height = $(this).height();
        }
    });
    $('.fc_widget_wrapper').css('height',max_widget_height+'px');
*/
});