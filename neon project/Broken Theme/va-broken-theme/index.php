<?php get_header(); ?>
<main id="primary" class="site-main" style="min-height:50vh;padding:2rem 1rem;">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article <?php post_class(); ?>>
        <h1><?php the_title(); ?></h1>
        <div class="entry-content"><?php the_content(); ?></div>
    </article>
<?php endwhile; else : ?>
    <p>No content found.</p>
<?php endif; ?>
</main>
<?php get_footer(); ?>
