<?php get_header(); ?>

<section class="wallBelt">
    <section class="contents">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; ?>
        <?php endif; ?>
    </section>
</section>

<?php get_footer(); ?>
