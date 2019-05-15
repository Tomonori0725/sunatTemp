<?php

function get_ffc_post_options($post_id, $name)
{
    global $ffCollection;
    return $ffCollection->posttype->getPostOptions($post_id, $name);
}

function get_ffc_term_options($term_id, $name)
{
    global $ffCollection;
    return $ffCollection->posttype->getTermOptions($term_id, $name);
}
