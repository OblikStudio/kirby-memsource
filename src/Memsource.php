<?php
namespace Memsource;

use GuzzleHttp\Client;

class App {
	private static $client = null;
	private $token = 'nTYiSFJbarg9PkwR1TUiW2xo4hzipJ7shDvOaskGB68zUx1M09USgRq3y0XE2w2O4';

	public function __construct () {
		static::$client = new Client(['base_uri' => 'https://cloud.memsource.com/web/api2/v1/']);
	}

	public function login ($user, $pass) {
		return static::$client->request('POST', 'auth/login', [
			'json' => [
				'userName' => $user,
				'password' => $pass
			]
		]);
	}

	public function createJob () {
		$metadata = array(
			'targetLangs' => ['bg', 'ru']
		);

		$data = array(
			'test' => 'foo',
			'bar' => 'baz'
		);

		try {
			$res = $client->post('projects/ZIj01S6l1x1KYiu7ggDNkf/jobs', [
			    'headers' => [
			        'Content-Type' => 'application/octet-stream',
			        'Content-Disposition' => "filename*=UTF-8''test.json",
			        'Memsource' => json_encode($metadata)
			    ],
			    'query' => [
			    	'token' => $token
			    ],
				'body' => json_encode($data),
			]);

			echo $res->getBody();
		} catch (Exception $e) {
			echo ($e->getResponse()->getBody(true));
		}
	}

	public function readJob () {
		$res = static::$client->request('GET', 'projects/12593321/jobs/0dDfr7b48WTGdWZmrIwjc6/preview', [
			'query' => [
				'token' => $this->token
			]
		]);

		return $res->getBody();
	}	
}