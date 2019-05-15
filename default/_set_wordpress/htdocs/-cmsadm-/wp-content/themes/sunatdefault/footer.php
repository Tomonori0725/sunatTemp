        </div>
    </div>

    <footer class="footer">
        <div class="contents">
            <div class="pageTop">▲ページトップへ</div>
            <nav class="fnav">
                <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer1',
                        'container'      => false,
                        'items_wrap'     => '<ul>%3$s</ul>'
                    ));
                ?>
                <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer2',
                        'container'      => false,
                        'items_wrap'     => '<ul>%3$s</ul>'
                    ));
                ?>
            </nav>
            <small class="copyright">&copy; 2016 Sunat Default Theme</small>
        </div>
    </footer>
</div>

<?php wp_footer(); ?>
</body>

</html>
