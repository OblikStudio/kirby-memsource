<?php

Kirby::plugin('oblik/memsource', [
  'api' => [
    'routes' => [
      [
        'pattern' => 'memsource/langs',
        'action' => function () {
          $langs = [];
          $activeLang = kirby()->language();

          foreach (kirby()->languages() as $lang) {
            array_push($langs, [
              'name' => $lang->name(),
              'code' => $lang->code(),
              'locale' => $lang->locale(),
              'isDefault' => $lang->isDefault(),
              'isActive' => $lang->code() === $activeLang->code()
            ]);
          }

          return $langs;
        }
      ]
    ]
  ]
]);
