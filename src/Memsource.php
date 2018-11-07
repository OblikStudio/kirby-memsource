<?php
namespace Memsource;

use GuzzleHttp\Client;

class App {
	private static $client = null;
	private $token = null;

	public function __construct () {
		$this->token = 'zpSY5b8Jj150wfKdihyPQV04BcvdwJBbij4bHwBJsqkJJfC7RLJvOQItq0tfbh8D4';
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

	public function createJob ($filename, $data) {
		// {
		// "asyncRequest": {
		// "action": "IMPORT_JOB",
		// "dateCreated": "2018-11-07T06:57:18+0000",
		// "id": "250209538"
		// },
		// "jobs": [
		// {
		// "jobAssignedEmailTemplate": null,
		// "workflowLevel": 1,
		// "workflowStep": null,
		// "uid": "uftfAD3Tk3qj1i7f5xqBl5",
		// "filename": "nexo.json",
		// "status": "NEW",
		// "imported": false,
		// "dateCreated": "2018-11-07T06:57:18+0000",
		// "notificationIntervalInMinutes": -1,
		// "providers": [],
		// "dateDue": null,
		// "continuous": false,
		// "targetLang": "bg"
		// },
		// {
		// "jobAssignedEmailTemplate": null,
		// "workflowLevel": 1,
		// "workflowStep": null,
		// "uid": "AK05lYCHH0AgS4qAOhr8Ie",
		// "filename": "nexo.json",
		// "status": "NEW",
		// "imported": false,
		// "dateCreated": "2018-11-07T06:57:18+0000",
		// "notificationIntervalInMinutes": -1,
		// "providers": [],
		// "dateDue": null,
		// "continuous": false,
		// "targetLang": "ru"
		// }
		// ],
		// "unsupportedFiles": []
		// }

		$metadata = array(
			'targetLangs' => ['bg', 'ru']
		);

		try {
			$res = static::$client->post('projects/ZIj01S6l1x1KYiu7ggDNkf/jobs', [
			    'headers' => [
			        'Content-Type' => 'application/octet-stream',
			        'Content-Disposition' => 'filename*=UTF-8\'\'' . $filename,
			        'Memsource' => json_encode($metadata)
			    ],
			    'query' => [
			    	'token' => $this->token
			    ],
				'body' => json_encode($data),
			]);

			return $res->getBody();
		} catch (Exception $e) {
			return ($e->getResponse()->getBody(true));
		}
	}

	public function readJob () {
		// uftfAD3Tk3qj1i7f5xqBl5
		$res = static::$client->request('GET', 'projects/12593321/jobs/uftfAD3Tk3qj1i7f5xqBl5/preview', [
			'query' => [
				'token' => $this->token
			]
		]);

		return $res->getBody();
	}	
}