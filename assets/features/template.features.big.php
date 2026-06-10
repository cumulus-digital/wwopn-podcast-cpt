<?php
	namespace WWOPN_Podcast;

	function outputBigFeatureFeature($feature, $key) {
		?>
		<a class="wpn-feature" href="<?=$feature->permalink?>" title="<?=\esc_attr($feature->post_title) ?>">
			<?php if (\is_admin()): ?>
				<span class="wpn-f-boxoption wpn-f-drag" title="Drag to reorder"><span class="screen-reader-text">Drag to reorder.</span></span>
				<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$feature->id?>">
				<span class="wpn-f-boxoption wpn-f-clear" title="Clear this box"><span class="screen-reader-text">Clear this box.</span></span>
			<?php endif ?>
			<?php
				echo \get_the_post_thumbnail(
					$feature->id,
					'large',
					[
						'data-empty' => \is_admin() ? $feature->empty : '',
						'alt' => $feature->post_title,
						'sizes' => '(min-width: 1000px) 33vw, 50vw'
					]
				);
			?>
		</a>
		<?php
	}
?>
<section class="wpn-features wpn-f-bfg">
	<?php for ($i = 0, $j = 0; $i < 3; $i++): ?>
	<ul class="wpn-f-container">
		<li class="wpn-f-big">
			<?php outputBigFeatureFeature($features[$j], $key) ?>
			<?php $j++ ?>
		</li>
		<li class="wpn-f-cluster">
			<ul>
				<li class="wpn-f-small">
					<?php outputBigFeatureFeature($features[$j], $key) ?>
					<?php $j++ ?>
				</li>
				<li class="wpn-f-small">
					<?php outputBigFeatureFeature($features[$j], $key) ?>
					<?php $j++ ?>
				</li>
			</ul>
		</li>
	</ul>
	<?php endfor; ?>
</section>