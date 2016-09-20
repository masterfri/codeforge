$(function() {
	
	$.fn.tooltip = $.fn.tooltip.noConflict();
	$(document.body).tooltip({
		selector: "[data-toggle=tooltip]",
		placement: 'bottom'
	});
	
	$(document.body).on('click', '[data-toggle=search-form]', function() {
		$('.search-form').toggle();
		return false;
	});
	
	$(document.body).on('submit', '[role=search-form]', function() {
		var target = $(this).attr('data-target');
		$.fn.yiiGridView.update(target, {
			data: $(this).serialize()
		});
		return false;
	});
	
});
