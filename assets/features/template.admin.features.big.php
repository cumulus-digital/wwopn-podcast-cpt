<?php
	namespace WWOPN_Podcast;
?>
<div class="wrap">

	<h1><?=$title?></h1>

	<p>Select a box to choose the <em>published</em> Podcast for each position of the Big Feature.</p>

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

</div>