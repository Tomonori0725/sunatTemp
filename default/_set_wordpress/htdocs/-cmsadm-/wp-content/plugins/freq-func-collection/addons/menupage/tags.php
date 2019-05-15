<?php

function get_ffc_menupage_options($page_slug, $name)
{
    global $ffCollection;
    return $ffCollection->menupage->getMenupageOptions($page_slug, $name);
}
