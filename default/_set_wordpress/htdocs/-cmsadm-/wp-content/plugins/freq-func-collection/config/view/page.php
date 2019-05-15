<div class="wrap">
    <h1>Freq Func COLLECTION</h1>
    <form action="options.php" method="post">
        <?php
        settings_fields('ffc-config-' . $pageName . '-group');
        do_settings_sections('ffc-config-' . $pageName);
        include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/' . $pageName . '.php';
        submit_button();
        ?>
    </form>
</div>
