<?php
namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-genre">
	<ul class="wpn-f-container">
		<?php foreach($features as $j=>$feature): ?>
			<li>
				<?php if ($feature->id): ?>
					<a class="wpn-feature" href="<?=$feature->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
						<?php if (\is_admin()): ?>
							<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$feature->id?>">
							<span class="wpn-f-clear" title="Clear this box"><span class="screen-reader-text">Clear this box.</span></span>
						<?php endif ?>
						<?=\get_the_post_thumbnail($feature->id, 'full', ['alt' => \esc_attr($feature->post_title), 'data-empty' => \esc_attr($feature->empty)])?>
					</a>
				<?php elseif(\is_admin()): ?>
					<a class="wpn_feature" title="Click to set a feature">
						<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="">
						<span class="wpn-f-clear" title="Clear this box"><span class="screen-reader-text">Clear this box.</span></span>
						<img src="<?php echo \esc_attr($feature->empty); ?>" data-empty="<?php echo \esc_attr($feature->empty); ?>">
					</a>
				<?php endif ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>