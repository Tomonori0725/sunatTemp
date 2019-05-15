<h2>お知らせ一覧</h2>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <article>
            <div><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></div>
            <div><?php the_time('Y.m.d'); ?></div>
            <div><?php the_excerpt(); ?></div>
        </article>
    <?php endwhile; ?>
<?php endif; ?>
