<?php get_header(); ?>
<?php the_post(); ?>

<div class="mainColumn">
    <?php the_content(); ?>
</div>

<div class="subColumn">
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
