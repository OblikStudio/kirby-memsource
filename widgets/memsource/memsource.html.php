<?php
	$url_panel = panel()->urls->index;
	$url_main = $url_panel . '/memsource';
	$url_auth = $url_main . '/auth';
?>

<style>
	#memsource-widget form {
		max-width: 16em;
		text-align: center;
		margin: 1.5em auto 0.5em auto;
	}
</style>

<div class="login">
	<?php if (isset($_SESSION['memsource_token'])): ?>
		<strong><?= $_SESSION['memsource_token'] ?></strong>
	<?php endif; ?>

	<form action="<?= $url_auth; ?>" method="post">
		<div class="field field-content">
			<input class="input" type="text" name="userName" autocomplete="section-memsource username" placeholder="Username">
			<div class="field-icon">
				<i class="icon fa fa-user"></i>
			</div>
		</div>
		<div class="field field-content">
			<input class="input" type="password" name="password" autocomplete="section-memsource current-password" placeholder="Password">
			<div class="field-icon">
				<i class="icon fa fa-key"></i>
			</div>
		</div>

		<button type="button" class="memsource-req">req</button>

		<button class="btn btn-rounded btn-positive">Authorize</button>
	</form>
</div>

<script>
<?php include_once __DIR__ . DS . 'assets' . DS . 'build' . DS . 'main.js'; ?>
</script>