<?php get_header(); ?>

<section class="wallBelt">
    <section class="contents">
        <section class="mainColumn">
            <?php get_template_part('parts/single-' . getPostType()); ?>

            <section class="pagination">
                <ul>
                    <?php if (get_next_post()) : ?>
                        <li class="prev">
                            <?php next_post_link('%link', 'PREV'); ?>
                        </li>
                    <?php else : ?>
                        <li class="prev disabled"><span>PREV</span></li>
                    <?php endif; ?>
                    <?php if (get_previous_post()) : ?>
                        <li class="next">
                            <?php previous_post_link('%link', 'NEXT'); ?>
                        </li>
                    <?php else : ?>
                        <li class="next disabled"><span>NEXT</span></li>
                    <?php endif; ?>
                </ul>
            </section>
        </section>
        <section class="subColumn">
            <?php get_sidebar(); ?>
        </section>
    </section>
</section>

<?php get_footer(); ?>
