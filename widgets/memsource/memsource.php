<?php

return array(
    'title' => 'Memsource',
    // 'options' => array(
    //     array(
    //         'text' => 'Logout',
    //         'icon' => 'sign-out',
    //         'link' => 'link/to/option'
    //     )
    // ),
    'html' => function() {
        return tpl::load(__DIR__ . DS . 'memsource.html.php');
    }
);