<?php

return array(
    'title' => 'Memsource',
    'options' => array(
        array(
            'text' => 'User',
            'icon' => 'user',
            'link' => '#'
        )
    ),
    'html' => function() {
        return tpl::load(__DIR__ . DS . 'memsource.html.php');
    }
);