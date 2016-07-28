AgereStatusButton = {
	body: $('body'),

	attachEvents: function () {
		this.attachChangeStatus();
	},

	// Show Print dialog
	attachChangeStatus: function () {
		// Remove handler from existing elements
		this.body.off('click', '.btn-changeStatus', this.changeStatus);

		// Re-add event handler for all matching elements
		this.body.on('click', '.btn-changeStatus', this.changeStatus);
	},

	changeStatus: function(event) {
		/*var elm = $(arguments[0].target);
		var src = elm.children('img').data('barcode');
		var link = "about:blank";
		var pw = window.open(link, "_new");
		pw.document.open();
		pw.document.write(imagePageMaker.print(src));
		pw.document.close();*/

		event.preventDefault();
		var self = arguments[0].target;
		var elm = $(self);
		var form = elm.closest('form');

		if (!form[0].checkValidity()) {
			// If the form is invalid, submit it. The form won't actually submit;
			// this will just cause the browser to display the native HTML5 error messages.
			//form.find(':submit').click();
			$('<input type="submit">').hide().appendTo(form).click().remove();
			return;
		}

		$.each(elm.data('status'), function(key, val) {
			var input = form.find('input[name="' + key +'"]');
			if (input.length > 0) {
				input.attr('value', val);
			} else {
				form.append('<input name="' + key +'" type="hidden" value="' + val + '">');
			}
		});


		//form.append('<input name="status" type="hidden" value="' + elm.data('status') + '">');
		var sendData = form.serialize();
		var sendRoute = elm.data('action');
		//shop.addItemToOpenCardOnLoad(self);

		$.ajax({
			url: sendRoute,
			type: 'POST',
			data: sendData,
		}).done(function(data) {
			if ($.trim(data.message).length > 0) {
				alert(data.message);
			} else {
				// @todo: Реалізувати підтягування контенту через ajax, щоб уникнути зайве перезавантаження сторінки
				//window.location.reload();

				elm.trigger('status.change', data);
			}

			return false;
			//shop.getOpenCard();
			//shop.addItemToOpenCardOnDone(self);
		}).fail(function (jqXHR, textStatus) {
			//xxx;
		});

		return false;
	}

};

jQuery(document).ready(function ($) {
	AgereStatusButton.attachEvents();

	// @todo Повішати на подію яка виникає після оновлення контенту через ajax - "refresh-content" element
	$('.ui-jqgrid-btable').bind('jqGrid.loadComplete', function() {
		AgereStatusButton.attachEvents(); // reattach print barcode button
	});
});