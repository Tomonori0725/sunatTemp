<?php
    header('Content-type: text/plain; charset= UTF-8');
    $length = mt_rand(8, 16);
    $makePass = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $length)), 0, $length);
    echo $makePass;
