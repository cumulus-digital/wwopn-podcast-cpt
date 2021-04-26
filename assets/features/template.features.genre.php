<?php
namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-genre">
	<ul class="wpn-f-container">
		<?php foreach($features as $j=>$feature): ?>
			<li>
				<?php if ($feature->id || \is_admin()): ?>
				<a class="wpn-feature" href="<?=$feature->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
					<?php if (\is_admin()): ?>
						<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$feature->id?>">
						<span class="wpn-f-clear" title="Clear this box"><span class="screen-reader-text">Clear this box.</span></span>
					<?php endif ?>
					<?=\get_the_post_thumbnail($feature->id, 'full', ['alt' => \esc_attr($feature->post_title)])?>
				</a>
				<?php endif ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>