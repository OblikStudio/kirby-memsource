<pre>

<?php
$token = 'nTYiSFJbarg9PkwR1TUiW2xo4hzipJ7shDvOaskGB68zUx1M09USgRq3y0XE2w2O4';

require_once(__DIR__ . '/vendor/autoload.php');
$client = new GuzzleHttp\Client(['base_uri' => 'https://cloud.memsource.com/web/api2/v1/']);

$res = $client->request('GET', 'projects/12593321/jobs/0dDfr7b48WTGdWZmrIwjc6/preview', [
		'query' => [
			'token' => $token
		]
	]);

	echo $res->getBody();
?>



</pre>