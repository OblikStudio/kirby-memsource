<?php
    $langs = [];
    foreach (site()->languages() as $lang) {
        array_push($langs, [
            'code' => $lang->code(),
            'locale' => strtolower($lang->locale()),
            'name' => $lang->name(),
            'isDefault' => $lang->isDefault()
        ]);
    }

    $pluginData = [
        'endpoint' => panel()->urls->index . '/memsource',
        'languages' => $langs
    ];
?>

<div class="memsource-widget"></div>

<script>
    window.Memsource = <?= json_encode($pluginData) ?>;
</script>

<script>
<?php include_once __DIR__ . DS . 'assets' . DS . 'build' . DS . 'main.js'; ?>
</script>