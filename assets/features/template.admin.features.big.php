<?php
	namespace WWOPN_Podcast;
?>
<div class="wrap">

	<h1><?=$title?></h1>

	<ul style="display: inline-flex; flex-direction: column; padding: 1em; background: #FFE; border: 1px solid #FAA;">
		<li>Click on a box to choose the <strong><em>published</em></strong> Podcast.</li>
		<li><span class="dashicons dashicons-move"></span> Click and drag to reorder boxes.</li>
		<li><span class="dashicons dashicons-dismiss"></span> Click to clear a box.</li>
		<li><em>Be sure to save your configuration!</em></li>
	</ul>

	<form method="POST" id="feature_posts_form">
		<input type="hidden" name="<?=$key?>" value="big">
		<?=\wp_nonce_field($key, $key . '-nonce');?>

		<p>
			<input type="submit" value="Save Configuration" class="button button-primary button-large">
		</p>

		<?php require_once BASEPATH . '/assets/features/template.features.big.php'; ?>

		<p>
			<input type="submit" value="Save Configuration" class="button button-primary button-large">
		</p>
	</form>

	<p>Shortcode: <strong>[podcasts-feature-big]</strong></p>

</div>