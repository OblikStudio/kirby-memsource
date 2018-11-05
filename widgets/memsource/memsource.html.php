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
	<strong><?= $_SESSION['memsource_token'] ?></strong>

	<form action="<?= $url_auth; ?>" method="post">
		<div class="field field-content">
			<input class="input" type="text" name="ms-username" autocomplete="section-memsource username" placeholder="Username">
			<div class="field-icon">
				<i class="icon fa fa-user"></i>
			</div>
		</div>
		<div class="field field-content">
			<input class="input" type="password" name="ms-password" autocomplete="section-memsource current-password" placeholder="Password">
			<div class="field-icon">
				<i class="icon fa fa-key"></i>
			</div>
		</div>

		<button class="btn btn-rounded btn-positive">Authorize</button>
	</form>
</div>

<script>
	var $widget = $('#memsource-widget');

	$widget.find('form').on('submit', function (event) {
		event.preventDefault();

		var $this = $(this);

		$.ajax({
			type: $this.attr('method'),
			url: $this.attr('action'),
			data: $this.serialize()
		});
	});
</script>