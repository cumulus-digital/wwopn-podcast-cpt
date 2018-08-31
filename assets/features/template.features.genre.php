<?php
namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-genre">
	<ul class="wpn-f-container">
		<?php foreach($features as $j=>$feature): ?>
			<li>
				<a class="wpn-feature" href="<?=$feature->permalink?>">
					<?php if (\is_admin()): ?>
						<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$feature->id?>">
						<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
					<?php endif ?>
					<img
						src="<?=$feature->icon?>"
						<?php if (\is_admin()): ?>
							data-empty="<?=$feature->empty?>"
						<?php endif ?>
					>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>