<?php
function hgup()
{
    chdir('/home/jireh/public_html');
    exec('hg pull', $output1);
    exec('hg up', $output2);

    return implode(PHP_EOL, array_merge($output1, array(PHP_EOL), $output2));
}