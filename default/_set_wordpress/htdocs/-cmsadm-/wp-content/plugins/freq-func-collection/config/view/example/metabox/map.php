<?php
$latlng = get_post_meta(get_the_ID(), '{{KEY}}', true);
get_the_ffc_map($latlng, 500);
?>
