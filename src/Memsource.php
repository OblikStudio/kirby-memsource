<?php
namespace Memsource;

use GuzzleHttp\Client;

class App {
	private static $file = __DIR__ . DS . '../memsource.json';
	private $client = null;
	private $token = null;

	public function __construct () {
		$store = static::store();

		$this->client = new Client(['base_uri' => 'https://cloud.memsource.com/web/api2/v1/']);
		$this->token = !empty($store['token']) ? $store['token'] : null;
	}

	private static function store ($mutator = null) {
		$store = null;

		try {
			$store = file_get_contents(static::$file);
			$store = json_decode($store, true);
		} catch (\Exception $e) {
			error_log('Could not open/parse store file.');
		}

		if (empty($store)) {
			$store = array();
		}
		
		if (is_callable($mutator)) {
			try {
				$mutator($store);
			} catch (\Exception $e) {
				error_log('Error while mutating store data.');
			}

			$json = json_encode($store, JSON_PRETTY_PRINT);
			file_put_contents(static::$file, $json);
		}
		
		return $store;
	}

	public function login ($user, $pass) {
		$data = null;
		$request = $this->client->request('POST', 'auth/login', [
			'json' => [
				'userName' => $user,
				'password' => $pass
			]
		]);

		try {
			$data = json_decode($request->getBody(), true);
		} catch (\Exception $e) {
			$data = array('error' => 'Could not parse authentication response');
		}

		if ($data && empty($data['error'])) {
			return static::store(function (&$store) use ($data) {
				$this->token = $data['token'];
				$_SESSION['memsource_token'] = $data['token'];

				$store['user'] = $data['user'];
				$store['token'] = $data['token'];
				$store['token_expires'] = $data['expires'];
			});
		}
	}

	public static function getTargetLanguages () {
		$targetLanguages = array();
        $siteLanguages = site()->languages();
        $defaultLanguage = site()->defaultLanguage();

        foreach ($siteLanguages as $lang) {
            if ($lang->code() !== $defaultLanguage->code()) {
                array_push($targetLanguages, strtolower($lang->locale()));
            }
        }

        return $targetLanguages;
	}

	public function listProjects () {
		$res = $this->client->request('GET', 'projects', [
			'query' => [
				'token' => $this->token
			]
		]);

		return $res;
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
			'targetLangs' => static::getTargetLanguages()
		);

		try {
			$res = $this->client->post('projects/ZIj01S6l1x1KYiu7ggDNkf/jobs', [
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
		$res = $this->client->request('GET', 'projects/12593321/jobs/uftfAD3Tk3qj1i7f5xqBl5/preview', [
			'query' => [
				'token' => $this->token
			]
		]);

		return $res->getBody();
	}	
}