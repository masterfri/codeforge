$.fn.openImagePickerDialog = function(title) {
	var context = this.get(0);
	title = title || 'Select image...';
	var dlg = $('<div class="modal fade image-picker-dialog">' +
					'<div class="modal-dialog">' +
						'<div class="modal-content">' +
							'<div class="modal-header">' + 
								'<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
								'<h4 class="modal-title">' + title + '</h4>' +
							'</div>' +
							'<div class="modal-body">' +
							'</div>' +
						'</div>' +
					'</div>' +
				'</div>');
	var body = dlg.find('.modal-body');
	$(document.body).append(dlg);
	dlg.modal({show: true});
	dlg.on('hidden.bs.modal', function() {
		dlg.remove();
	});
	body.load(context.href, function() {
		$(body).on('click', 'a.thumbnail', function() {
			var i = $(this).children('img').detach();
			$(context).children('img').remove();
			$(context).append(i);
			$(context).parent().removeClass('empty').addClass('non-empty');
			$(context).parent().find('input').val($(this).attr('rel'));
			dlg.modal('hide');
		});
	});
}
$.fn.imagePickerClear = function() {
	this.parent().removeClass('non-empty').addClass('empty');
	this.parent().find('input').val('');
	this.parent().find('img').remove();
}
