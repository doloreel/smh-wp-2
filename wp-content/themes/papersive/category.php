<?php get_header(); ?>
<div class="category-header-wrapper" style="background-image: url('<?php if (function_exists('z_taxonomy_image_url')) echo z_taxonomy_image_url(); ?>'); background-size: cover;">
	
	
	<div class="logo-wrap">
		<div class="logo">
			<div class="logo-main"><?php if ( ! is_singular() ) { echo '<h1>'; } ?><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr_e( get_bloginfo( 'name' ), 'blankslate' ); ?>" rel="home"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a><?php if ( ! is_singular() ) { echo '</h1>'; } ?></div>
			<div class="logo-line"></div>
			<div class="logo-tag"><?php bloginfo( 'description' ); ?></div>  
		</div>
	</div>

	<div class="category-header header">
		<div class="category-page-txt">
			<div class="category-page-title entry-title"><?php _e( '', 'blankslate' ); ?><?php single_cat_title(); ?></div>
			<div class="category-page-description"><?php if ( '' != category_description() ) echo apply_filters( 'archive_meta', '<div class="archive-meta">' . category_description() . '</div>' ); ?></div>
		</div>
	</div>

</div>


<section id="content" role="main" class="category-content">  
	<div class="content">
		<div class="content-posts">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'related-entry' ); ?>
		<?php endwhile; endif; ?>
	</div>

		<?php get_template_part( 'nav', 'below' ); ?> 
	</div>

	<?php get_sidebar(); ?>
</section>
<?php get_sidebar(); ?>
<?php get_footer(); ?>  

