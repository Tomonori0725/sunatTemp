<?php get_header(); ?>

<div class="mainImg">
    <!-- トップカルーセル -->
    <?php $arrImage = get_option('top-carousel-image'); ?>
    <?php if ($arrImage) : ?>
        <!-- カルーセル -->
        <ul class="bxslider">
            <?php
            theImage($arrImage, array(
                'before' => '<li>',
                'after'  => '</li>'
            ));
            ?>
        </ul>
    <?php endif; ?>
</div>

<div class="mainColumn">
    <div class="topics">
        <h2>お知らせ</h2>
        <div class="informationTopics dateBlock">
            <div class="scroll">
                <?php
                    $theQuery = new WP_Query(array(
                        'post_type'           => 'information',
                        'posts_per_page'      => getConst('NUMBER_RECENT_POST'),
                        'no_found_rows'       => true,
                        'post_status'         => 'publish',
                        'ignore_sticky_posts' => true
                    ));
                    if ($theQuery->have_posts()) :
                        while ($theQuery->have_posts()) :
                            $theQuery->the_post();
                            ?>
                            <dl>
                                <dt><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><small class="pc"><?php the_time('Y.m.d'); ?></small></dt>
                                <dd><?php the_excerpt(); ?><small class="sp">(<?php the_time('Y.m.d'); ?>)</small></dd>
                            </dl>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                ?>
            </div>
            <div><a href="<?php echo getUrl('site-top'); ?>/information/">もっと見る</a></div>
        </div>
    </div>
</div>

<div class="subColumn">
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
