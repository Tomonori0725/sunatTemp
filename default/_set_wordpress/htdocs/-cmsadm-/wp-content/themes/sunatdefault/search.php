<?php get_header(); ?>

<section class="titleBox">
    <h2>検索結果</h2>
</section>

<section class="wallBelt">
    <section class="contents">
        <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article>
                <?php the_time('Y.m.d'); ?>
                <a href="<?php the_permalink(); ?>">
                    <?php the_title(); ?>
                </a>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>
        <?php else : ?>
            <p>見つかりませんでした。</p>
        <?php endif; ?>
    </section>
</section>
<?php get_footer(); ?>
