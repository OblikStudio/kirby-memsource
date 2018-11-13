<?php
	$url_panel = panel()->urls->index;
	$url_main = $url_panel . '/memsource';
	$url_auth = $url_main . '/auth';
?>

<div class="memsource-widget"></div>

<script>
<?php include_once __DIR__ . DS . 'assets' . DS . 'build' . DS . 'main.js'; ?>
</script>