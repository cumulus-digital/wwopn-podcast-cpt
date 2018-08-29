<?php
namespace WWOPN_Podcast;
?>
<section class="wpn-features wpn-f-genre">
	<ul class="wpn-f-container">
		<?php foreach($features as $j=>$feature): ?>
			<li>
				<a class="wpn-feature" href="<?=$features[$j]->permalink?>">
					<?php if (\is_admin()): ?>
						<input class="wpn-f-id" type="hidden" name="<?=$key?>-id[]" value="<?=$features[$j]->id?>">
						<span class="wpn-f-clear"><span class="screen-reader-text">Clear this box.</span></span>
					<?php endif ?>
					<img
						src="<?=$features[$j]->icon?>"
						<?php if (\is_admin()): ?>
							data-empty="<?=$featurs[$j]->empty?>"
						<?php endif ?>
					>
					<?php $j++ ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</section>