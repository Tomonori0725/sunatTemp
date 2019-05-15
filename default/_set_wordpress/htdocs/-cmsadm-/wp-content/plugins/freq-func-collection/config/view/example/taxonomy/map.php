<?php
$latlng = get_term_meta(get_queried_object_id(), '{{KEY}}', true);
get_the_ffc_map($latlng, 500);
?>
