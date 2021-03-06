<?php
	namespace WWOPN_Podcast;
?>
<div class="wrap">

	<h1><?=$title?></h1>

	<div class="tablenav top">

		<div class="alignleft">
			<form method="GET" id="genre_chooser">
				<input type="hidden" name="post_type" value="<?=PREFIX?>">
				<input type="hidden" name="page" value="<?=self::$screen?>">
				<label for="genre-selector">Select Genre:</label>
				<?php 
					\wp_dropdown_categories([
						'taxonomy' => Genre::$prefix,
						'name' => 'genre_id',
						'value_field' => 'id',
						'hide_if_empty' => false,
						'show_option_none' => '--',
						'selected' => isset($genre) ? $genre->term_id : -1
					]);
				?>
				<input type="submit" id="doaction" class="button action" value="Choose">
			</form>
		</div>
	</div>

	<?php if ( ! isset($genre)): ?>
		
		<div class="howto">
			Select a genre to begin.
		</div>

	<?php else: ?>

		<h2><?=$genre->name?> Features</h2>

		<form method="POST" id="feature_posts_form">
			<input type="hidden" name="<?=$key?>" value="genre">
			<input type="hidden" id="find-posts-category-limit" name="genre_id" value="<?=$genre->term_id?>">

			<?=\wp_nonce_field($key, $key . '-nonce');?>

			<p>
				<input type="submit" value="Save Configuration" class="button button-primary button-large">
			</p>
	
			<?php require_once BASEPATH . '/assets/features/template.features.genre.php'; ?>

			<p>
				<input type="submit" value="Save Configuration" class="button button-primary button-large">
			</p>

			<p class="howto">Empty boxes will be collapsed on front-end display.</p>

		</form>

		<p>
			Shortcode Options <strong>(Use one, not both!)</strong>:
		</p>
		<ul class="wpn-genre-shortcode_options">
			<li>
				[podcasts-feature-genre id=<?php echo $genre->term_id ?>]
			</li>
			<li>
				[podcasts-feature-genre slug=<?php echo $genre->slug ?>]
			</li>
		</ul>
		<p><strong>Note:</strong> using slugs may break if the genre's slug changes!</p>

	<?php endif; ?>
</div>
