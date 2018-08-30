(function($, window, undefined){

	// from underscore.js
	function throttle(func, wait, options) {
		var timeout, context, args, result;
		var previous = 0;
		if (!options) options = {};

		var later = function() {
			previous = options.leading === false ? 0 : Date.now();
			timeout = null;
			result = func.apply(context, args);
			if (!timeout) context = args = null;
		};

		var throttled = function() {
			var now = Date.now();
			if (!previous && options.leading === false) previous = now;
			var remaining = wait - (now - previous);
			context = this;
			args = arguments;
			if (remaining <= 0 || remaining > wait) {
				if (timeout) {
					clearTimeout(timeout);
					timeout = null;
				}
				previous = now;
				result = func.apply(context, args);
				if (!timeout) context = args = null;
			} else if (!timeout && options.trailing !== false) {
				timeout = setTimeout(later, remaining);
			}
			return result;
		};

		throttled.cancel = function() {
			clearTimeout(timeout);
			previous = 0;
			timeout = context = args = null;
		};

		return throttled;
	};

	/**
	 * Handle autosaving custom metafield data
	 */
	$(function(){

		var wpn_metas = $([
			'.wpn_meta_autosave input',
			'.wpn_meta_autosave select',
			'.wpn_meta_autosave textarea'
		].join(', '));

		function cmf_autosave() {
			if ( ! ajaxurl) {
				return;
			}
			var post_id = $('#post_ID').val(),
				post_data = {
					post_ID: post_id,
					action: 'autosave_wwopn_podcast_meta',
				};
				wpn_metas.each(function(){
					var $this = $(this);
					post_data[$this.attr('name')] = $this.val();
				});
			$.ajax({
				data: post_data,
				type: 'POST',
				url: ajaxurl
			});
		}

		// any time the meta fields change, register an autosave
		wpn_metas.on('change keyup',
			throttle(cmf_autosave, 60000, {leading: false, trailing: true})
		);

		// prompt before leaving
		var shouldConfirmLeave = false;
		$(window).on('beforeunload', function() {
			if (shouldConfirmLeave) {
				return 'You have unsaved changes, are you sure you want to leave?';
			}
			return;
		});
		$('##submitpost input, #submitpost .submitdelete')
			.on('click', function() {
				shouldConfirmLeave = false;
			});
		wpn_metas.on('change keyup', 
			throttle(function() {
				shouldConfirmLeave = true;
			}, 1000)
		);

	});

	function meta_extraimage_picker() {
		var $this = $(this);
			frame = $this.data('frame'),
			$field = $('.image_id', $this),
			$img = $('img', $this),
			buttons = {
				remove: $('.meta_extraimage-remove', $this),
				add: $('.meta_extraimage-add', this)
			};

		// Reopen an existing frame
		if (frame) {
			frame.open();
			return;
		}

		// Create media frame
		frame = wp.media.frames.file_frame = wp.media({
			title: $this.data('uploader-title'),
			button: {
				text: $this.data('uploader-button-text'),
			},
			multiple: false
		});
		$this.data('frame', frame);

		// Auto-select already chosen image
		frame.on('open', function() {
			var selection = frame.state().get('selection'),
				attachment = wp.media.attachment($field.val());
			attachment.fetch();
			selection.add( attachment ? [ attachment ] : [] );
		});

		// Save a selection to our hidden input and display
		frame.on('select', function() {
			var att = frame.state().get('selection').first().toJSON();
			$field.val(att.id);
			$img.attr('src', att.url);
			$this.addClass('has_image');
		});

		frame.open();
	}

	$(function(){
		$('.meta_extraimage').on('click', '.meta_extraimage-add, img', function(e) {
			e.preventDefault();
			meta_extraimage_picker.apply(e.delegateTarget);
		});

		$('.meta_extraimage').on('click', '.meta_extraimage-remove', function(e) {
			// Remove image and unset val
			e.preventDefault();
			var $this = $(e.delegateTarget),
				$field = $('.image_id', $this),
				$img = $('img', $this);
			$field.val('');
			$img.attr('src','data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
			$this.removeClass('has_image');
		});
	});

}(jQuery, window.self));