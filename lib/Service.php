<?php

namespace Oblik\Memsource;

use Error;
use Kirby\Http\Remote;
use Kirby\Http\Response;

class Service
{
	public const API_URL = 'https://cloud.memsource.com/web/api2/v1';
	public const IMPORT_SETTINGS = [
		'name' => 'kirby-1',
		'fileImportSettings' => [
			'inputCharset' => 'UTF-8',
			'outputCharset' => 'UTF-8',
			'json' => [
				'tagRegexp' => '\\{\\{[^\\}]+\\}\\}',
				'htmlSubFilter' => true,
				'excludeKeyRegexp' => '.*/id$'
			]
		]
	];

	public static function page($input)
	{
		// <k-pages-field> may request page=0 *or* page=1, depending on
		// pagination, but both should return the first page.
		$page = (int)$input;
		$page = $page > 0 ? $page : 1;

		// Memsource pagination starts at 0.
		return $page - 1;
	}

	public $token;

	public function __construct()
	{
		$cache = kirby()->cache('oblik.memsource');
		$session = $cache->get('session');

		if (!$session) {
			$session = $this->login();

			if (!empty($session['expires'])) {
				$timestamp = strtotime($session['expires']);
				$cache->set('session', $session, $timestamp);
			}
		}

		$this->token = $session['token'] ?? null;
	}

	public function login()
	{
		return $this->request('auth/login', [
			'method' => 'POST',
			'headers' => [
				'Content-Type' => 'application/json'
			],
			'data' => json_encode([
				'userName' => option('oblik.memsource.login.username'),
				'password' => option('oblik.memsource.login.password')
			])
		]);
	}

	public function request(string $path, array $params = [])
	{
		$params = array_replace_recursive([
			'headers' => [
				'Authorization' => 'ApiToken ' . $this->token
			]
		], $params);

		$remote = Remote::request(self::API_URL . '/' . $path, $params);
		$body = json_decode($remote->content(), true);

		if ($remote->code() < 200 || $remote->code() >= 300) {
			throw new ApiException($body);
		}

		return $body;
	}

	public function getJobs(string $projectId, int $workflowLevel, array $options = [])
	{
		$query = http_build_query([
			'workflowLevel' => $workflowLevel,
			'pageNumber' => static::page($options['page']),
			'pageSize' => $options['limit'],
			'filename' => $options['search']
		]);

		return $this->request("projects/$projectId/jobs?$query");
	}

	public function getProjects(array $options = [])
	{
		$query = http_build_query([
			'pageNumber' => static::page($options['page']),
			'pageSize' => $options['limit'],
			'name' => $options['search']
		]);

		return $this->request("projects?$query");
	}

	public function upload(string $projectId, string $filename)
	{
		$response = Remote::request(self::API_URL . '/importSettings?token=' . $this->token, [
			'method' => 'GET'
		]);

		$data = json_decode($response->content(), true);
		$entries = $data['content'] ?? [];
		$settings = null;

		foreach ($entries as $entry) {
			if ($entry['name'] === self::IMPORT_SETTINGS['name']) {
				$settings = $entry;
				break;
			}
		}

		if (!$settings) {
			$response = Remote::request(self::API_URL . '/importSettings?token=' . $this->token, [
				'method' => 'POST',
				'data' => json_encode(self::IMPORT_SETTINGS),
				'headers' => [
					'Content-Type' => 'application/json'
				]
			]);

			$settings = json_decode($response->content(), true);
		}

		if (empty($settings['uid'])) {
			throw new Error('Could not get/create import settings');
		}

		$langs = json_decode(kirby()->request()->header('Memsource-Langs'), true);
		$memsourceHeader = [
			'targetLangs' => $langs,
			'importSettings' => [
				'uid' => $settings['uid']
			]
		];

		$remote = Remote::request(self::API_URL . '/projects/' . $projectId . '/jobs?token=' . $this->token, [
			'method' => 'POST',
			'data' => json_encode(kirby()->request()->data()),
			'headers' => [
				'Memsource' => json_encode($memsourceHeader),
				'Content-Type' => 'application/octet-stream',
				'Content-Disposition' => "filename*=UTF-8''{$filename}"
			]
		]);

		return new Response($remote->content(), 'application/json', $remote->code());
	}
}
