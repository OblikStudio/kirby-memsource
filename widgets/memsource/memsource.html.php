<?php if (!site()->multilang()): ?>
    <h4>This site is not multilingual.</h4>
    <?php exit; ?>
<?php endif; ?>

<?php
    $langs = [];
    $activeLang = site()->language();

    foreach (site()->languages() as $lang) {
        array_push($langs, [
            'name' => $lang->name(),
            'code' => $lang->code(),
            'locale' => strtolower($lang->locale()),
            'isDefault' => $lang->isDefault(),
            'isActive' => $lang->code() === $activeLang->code()
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
