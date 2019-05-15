<?php get_header(); ?>

<section class="titleBox">
    <h1>INFORMATION</h1>
</section>

<section class="wallBelt">
    <section class="contents information">
        <h1><?php single_term_title(); ?>カテゴリーの記事一覧</h1>
        <div class="row">
            <?php get_template_part('parts/taxonomy-' . getPostType()); ?>
        </div>

        <section class="pagination">
            <?php pagination(array(
                'range'   => 3,
                'prev'    => '&lt;',
                'next'    => '&gt;',
                'first'   => '&laquo;最初',
                'last'    => '最後&raquo;',
                'current' => '%page% of %total%'
            )); ?>
        </section>
    </section>
</section>
<?php get_footer(); ?>
