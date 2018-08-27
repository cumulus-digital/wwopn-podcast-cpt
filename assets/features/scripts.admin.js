(function($, window, undefined) {

	$(function(){

		var $find_post_form = $('#wpn-findpost'),
			$feature_post_form = $('#feature_posts_form'),
			$feature_items = $('.wpn-features a'),
			$clear_box_buttons = $('.wpn-f-clear');

		// Make the find post dialog have an appropriate headline
		// and a close button
		$('#find-posts-head', $find_post_form).html(
			'Select a Podcast' +
			'<button type="button" class="media-modal-close"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></button>'
		).addClass('upload-php');

		/**
		 * Override the existing findPosts.send function to only deal
		 * with Podcast custom post types and include thumbnails.
		 */
		window.findPosts.originalSend = window.findPosts.send;
		window.findPosts.send = function() {
			var $spinner = $('.find-box-search .spinner'),
				$output = $('#find-posts-response'),
				query = $('#find-posts-input').val(),
				genre_id = $('#genre_id').val();

			$spinner.addClass('is-active');

			var queryData = {
				'_embed': true,
				'context': 'embed',
				'per_page': 50,
		        '_ajax_nonce': $("#_ajax_nonce").val(),
		        'search': query
			};

			if (genre_id && genre_id.length) {
				queryData.wpn_podcast_genre = genre_id;
			}

			jQuery.ajax({
		        url : '/wp-json/wp/v2/podcasts/',
		        method: "GET",
		        dataType: "json",
		        data: queryData
		    })
		    	.always(function() {
		    		$spinner.removeClass('is-active');
		    	})
		    	.done(function(data) {
		    		var $posts = [];
		    		data.forEach(function(post, i) {
		    			$posts.push($(
		    				'<tr class="found-posts ' + ((i%2) ? 'alternate' : '') + '">' +
		    					'<td class="found-radio"><input type="radio" name="found_post_id" id="found-' + post.id + '" value="' + post.id + '"></td>' +
		    					'<td><label for="found-' + post.id + '"><img src="' + 
		    						(post._embedded['wp:featuredmedia'][0].media_details.sizes.thumbnail.source_url || "") +
		    					'" width="75px"></label></td>' + 
		    					'<td width="100%"><label for="found-' + post.id + '">' + post.title.rendered + '</label></td>' +
		    				'</tr>'
		    			));
		    		});
		    		var $table = $('<table class="widefat" />');
		    		$table.append($posts);
		    		$output.html($table);
		    	})
		    	.fail(function() {
		    		$output.text(attachMediaBoxL10n.error);
		    	});
		};

		/**
		 * Open the find posts dialog when clicking on a feature item
		 */
		$feature_items.on('click', function(e) {
			e.preventDefault();
			var $this = $(this);

			// Handle box clearing clicks
			if (e.target.className === 'wpn-f-clear') {
				var $img = $('img', $this);
				$img.attr('src', $img.data('empty'));
				$('input', $this).val('').attr('value', '');
				return;
			}

			// Store the feature we're editing
			$find_post_form.data('feature_item', this);
			
			window.findPosts.close();
			window.findPosts.open(false);
		});

		/**
		 * Take over submission of the find post dialog
		 */
		$find_post_form.on('submit', function(e) {
			e.preventDefault();
			var $this = $(this),
				selected = $('[name="found_post_id"]:checked', $this);
			var post_id = selected.val();
			window.findPosts.close();

			// Retrieve post info
			var request = jQuery.ajax({
			        url : '/wp-json/wp/v2/podcasts/'+post_id,
			        method: "GET",
			        dataType: "json",
			        data: { 'featured_media': true }
			    })
				.done(function(data){
					// Update feature with post data
					$position = $($find_post_form.data('feature_item'));
					$('img', $position).attr(
						'src',
						data.featured_media_url
					);
					$('input', $position).attr('value', data.id).val(data.id);
					$feature_post_form.data('changed', true);
				});
		});

		/**
		 * Handle close event on find posts dialog
		 */
		$('.media-modal-close', $find_post_form).on('click', function() {
			window.findPosts.close();
		});

		/**
		 * Auto-switch genre from selector
		 */
		$('#genre_id').on('change', function(e) {
			$('#genre_chooser').submit();
		});

		/**
		 * If features have changed, confirm before leaving page
		 */
		$(window).on('beforeunload', function(e) {
			if ($feature_post_form.data('changed')) {
				var txt = "You have unsaved changes, are you sure you want to leave this page?";
				e.returnValue = txt;
				return txt;
			}
		});
		$('input[type=submit]', $feature_post_form).on('click', function() {
			$feature_post_form.data('changed', false);
		});

		/**
		 * Initialize swappable
		 */
		if (window.Swappable) {
			var swapcontainer = document.querySelector('.wpn-features');
			if (swapcontainer) {
				var swapper = new Swappable.default(swapcontainer, {
					draggable: '.wpn-features a.wpn-feature',
					mirror: {
						constrainDimensions: true
					},
				});
				swapper.on('mirror:destroy', function(e){
					// Trigger change event on inputs
					$feature_post_form.data('changed', true);
				});
			}
		}

	});

}(jQuery, window.self));