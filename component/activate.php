<?php

function iddahe_com_editor_component_has($name)
{
    $dir = __DIR__ . "/{$name}";

    return is_dir($dir);
}
