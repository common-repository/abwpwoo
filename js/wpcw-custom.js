jQuery(document).ready(function($){

setTimeout(function() {
	var unitTable = jQuery('.wpcw_fe_table');
	if (unitTable.length) {
		unitTable.each(function(el, index) {
			var units = $(this).find('.wpcw_fe_unit_title');
			units.each(function(index) {
				$(this).text(index + 1 + ".");
            }); 
        });
    }
}, 500);



$( ".wpcw_progress_percent" ).prepend( course_complete.course_complete_text  ); 
$( ".wpcw_course-template-template-single-course .wpcw_widget_progress" ).find(".wpcw_fe_module").addClass("wpcw_fe_module_toggle_hide"); 

	  })
	

