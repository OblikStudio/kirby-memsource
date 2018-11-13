<pre>
	<?php
	$url_panel = panel()->urls->index;
	$url_main = $url_panel . '/memsource';
	$url_auth = $url_main . '/auth';

    $siteLanguages = panel()->site();

    var_dump($siteLanguages);
?>
</pre>

<div class="memsource-widget"></div>

<script>
<?php include_once __DIR__ . DS . 'assets' . DS . 'build' . DS . 'main.js'; ?>
</script>