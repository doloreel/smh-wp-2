<div class="related-post-wrapper post">
	<div class="index-entry-b-post">
		<div class="related-img-cat">
			<div class="related-link-wrap">
				<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"></a>
			</div> 
			<div class="related-cat-wrap">
				<div class="related-cat"><?php the_category( ', ' ); ?></div> 
			</div>
			<div class="related-img"><?php the_post_thumbnail();?></div> 
		</div>
		<div class="related-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></div> 
	</div>
</div> 