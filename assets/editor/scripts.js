(function($, window, undefined){

	if (! window._) {
		throw "Underscore.js must be included before using this script!";
		return;
	}

	/**
	 * Handle autosaving custom metafield data
	 */
	$(function(){

		var $wpn_metas = $([
			'.wpn_meta_autosave input[type!=hidden]',
			'.wpn_meta_autosave input.image_id',
			'.wpn_meta_autosave select',
			'.wpn_meta_autosave textarea'
		].join(', '));

		// Store original loaded values
		$wpn_metas.each(function(){
			var $this = $(this);
			$this.data('original', $this.val());
		});

		// any time the meta fields change, register an autosave
		$wpn_metas.on('change keyup', _.throttle(function() {
			var $this = $(this),
				original = $this.data('original');

			if ($this.val() !== original) {
				shouldConfirmLeave = true;
				$this.data('do_autosave', true);
				return;
			}
			$this.data('do_autosave', false);
		}, 1000, {leading: true, trailing: true}));

		// Hook into WP's autosave heartbeat
		$(window.document).on('heartbeat-tick.autosave', function() {
			if ( ! wpn_ajax_object) {
				return;
			}
			var changed = [],
				post_id = $('#post_ID').val(),
				nonce = wpn_ajax_object.nonce,
				post_data = {
					nonce: nonce,
					post_ID: post_id,
					action: 'autosave_wpn_podcast_meta',
				};

			// Determine which meta fields we need to save
			$wpn_metas.each(function() {
				var $meta = $(this),
					name = $meta.attr('name');

				if ($meta.data('do_autosave')) {
					if ($meta.attr('name').indexOf('[') > -1) {
						// meta is a multi
						var parentName = name.replace(/\[[^\]]+\]/,''),
							key = name.replace(/[^\[]*/,'').replace(/\].*/,'').replace(/[\[\]]/,'');
						if ( ! post_data.hasOwnProperty(parentName)) {
							post_data[parentName] = {};
						}
						post_data[parentName][key] = $meta.val();
						changed.push($meta);
						return;
					}
					post_data[name] = $meta.val();
					changed.push($meta);
				}
			});

			if (changed.length) {
				// Values have changed, save them
				$.ajax({
					data: post_data,
					type: 'POST',
					url: wpn_ajax_object.url,
					success: function(data) {
						$(changed).each(function() {
							$(this).data('do_autosave', false);
						});
						changed = [];
						if (data.new_nonce) {
							wpn_ajax_object.nonce = data.new_nonce;
						}
						shouldConfirmLeave = false;
					},
					error: function(error) {
						if (error.responseJSON && error.responseJSON.msg) {
							$('<div class="notice notice-error"><p>' + error.responseJSON.msg + '</p></div>').insertAfter('div.wrap h2:first');

							if (error.responseJSON.new_nonce) {
								wpn_ajax_object.nonce = error.responseJSON.new_nonce;
							}

						} else {
							$('<div class="notice notice-error"><p>' + error.responseText.msg + '</p></div>').insertAfter('div.wrap h2:first');
						}
					}
				});
			}
		});

		// prompt before leaving
		var shouldConfirmLeave = false;
		$(window).on('beforeunload', function() {
			if (shouldConfirmLeave) {
				return 'You have unsaved changes, are you sure you want to leave?';
			}
			return;
		});
		$('#submitpost input, #submitpost .submitdelete')
			.on('click', function() {
				shouldConfirmLeave = false;
			});

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
			$field.val(att.id).trigger('change');
			$img
				.attr('src', att.sizes.medium.url)
				.attr('srcset', '');
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