<?php

if (site()->multilang()) {
    $options = [
        [
            'text' => 'User',
            'icon' => 'user',
            'link' => '#'
        ]
    ];
} else {
    // Don't render plugin buttons when site is not multilang because JS won't
    // be echoed and the buttons will be useless.
    $options = [];
}

return array(
    'title' => 'Memsource',
    'options' => $options,
    'html' => function() {
        return tpl::load(__DIR__ . DS . 'memsource.html.php');
    }
);