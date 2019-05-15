<form action="options.php" method="post">
    <?php
    settings_fields($slug . '-section-group');
    do_settings_sections($slug);
    submit_button();
    ?>
</form>
