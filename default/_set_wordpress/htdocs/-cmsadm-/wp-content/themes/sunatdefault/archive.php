<?php get_header(); ?>

<section class="wallBelt">
    <section class="contents information">
        <div class="row">
            <?php get_template_part('parts/archive-' . getPostType()); ?>
        </div>

        <section class="pagination">
            <?php pagination(array(
                'range'   => 3,
                'prev'    => '<i class="fa fa-chevron-left" aria-hidden="true"></i>',
                'next'    => '<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                'first'   => '<i class="fa fa-chevron-left" aria-hidden="true"></i>&nbsp;最初',
                'last'    => '最後&nbsp;<i class="fa fa-chevron-right" aria-hidden="true"></i>',
                'current' => '%page% of %total%'
            )); ?>
        </section>
    </section>
</section>

<section class="subColumn">
    <?php get_sidebar(); ?>
</section>

<?php get_footer(); ?>
