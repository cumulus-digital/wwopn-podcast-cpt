<?php
	namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-bfg">
	<?php for ($i = 0, $j = 0; $i < 3; $i++): ?>
	<ul class="wpn-f-container">
		<li class="wpn-f-big">
			<a class="wpn-feature" href="<?=$features[$j]->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
				<?php if (\is_admin()): ?>
					<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
					<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
				<?php endif ?>
				<?php
					echo \get_the_post_thumbnail(
						$features[$j]->id,
						'large',
						[
							'data-empty' => \is_admin() ? $features[$j]->empty : '',
							'alt' => $features[$j]->post_title,
							'sizes' => '(min-width: 1000px) 33vw, 50vw'
						]
					);
				?>
				<?php $j++ ?>
			</a>
		</li>
		<li class="wpn-f-cluster">
			<ul>
				<li class="wpn-f-small">
					<a class="wpn-feature" href="<?=$features[$j]->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
						<?php if (\is_admin()): ?>
							<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
							<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
						<?php endif ?>
						<?php
							echo \get_the_post_thumbnail(
								$features[$j]->id,
								'medium',
								[
									'data-empty' => \is_admin() ? $features[$j]->empty : '',
									'alt' => $features[$j]->post_title,
									'sizes' => '(min-width: 1000px) 33vw, 50vw'
								]
							);
						?>
						<?php $j++ ?>
					</a>
				</li>
				<li class="wpn-f-small">
					<a class="wpn-feature" href="<?=$features[$j]->permalink?>" title="<?=\esc_attr($features[$j]->post_title) ?>">
						<?php if (\is_admin()): ?>
							<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
							<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
						<?php endif ?>
						<?php
							echo \get_the_post_thumbnail(
								$features[$j]->id,
								'medium',
								[
									'data-empty' => \is_admin() ? $features[$j]->empty : '',
									'alt' => $features[$j]->post_title,
									'sizes' => '(min-width: 1000px) 33vw, 50vw'
								]
							);
						?>
						<?php $j++ ?>
					</a>
				</li>
			</ul>
		</li>
	</ul>
	<?php endfor; ?>
</section>