<h2>お知らせ</h2>

<?php the_post(); ?>
<article>
    <div><?php the_title(); ?></div>
    <div><?php the_time('Y.m.d'); ?></div>
    <div><?php the_content(); ?></div>
</article>
