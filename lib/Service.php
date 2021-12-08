<?php

namespace Oblik\Memsource;

use Kirby\Http\Remote;

class Service
{
	public const API_URL = 'https://cloud.memsource.com/web/api2/v1';
	public const IMPORT_SETTINGS_NAME = 'kirby';

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

	public function getWorkflowSteps(string $projectId)
	{
		return $this->request("projects/$projectId/workflowSteps");
	}

	public function getImportSettings()
	{
		$name = static::IMPORT_SETTINGS_NAME;
		return $this->request("importSettings?name={$name}")['content'][0];
	}

	public function createImportSettings()
	{
		return $this->request('importSettings', [
			'method' => 'POST',
			'headers' => [
				'Content-Type' => 'application/json'
			],
			'data' => json_encode([
				'name' => static::IMPORT_SETTINGS_NAME,
				'fileImportSettings' => option('oblik.memsource.importSettings')
			])
		]);
	}

	public function updateImportSettings(string $settingsId)
	{
		return $this->request('importSettings', [
			'method' => 'PUT',
			'headers' => [
				'Content-Type' => 'application/json'
			],
			'data' => json_encode([
				'uid' => $settingsId,
				'name' => static::IMPORT_SETTINGS_NAME,
				'fileImportSettings' => option('oblik.memsource.importSettings')
			])
		]);
	}

	public function postJobs(array $input)
	{
		$memsourceHeader = [
			'targetLangs' => $input['targetLangs'],
			'importSettings' => [
				'uid' => $input['settingsId']
			]
		];

		$projectId = $input['projectId'];
		$jobName = $input['jobName'];

		return $this->request("projects/$projectId/jobs", [
			'method' => 'POST',
			'data' => json_encode($input['jobData'], JSON_UNESCAPED_UNICODE),
			'headers' => [
				'Memsource' => json_encode($memsourceHeader),
				'Content-Type' => 'application/octet-stream',
				'Content-Disposition' => "filename*=UTF-8''{$jobName}.json"
			]
		]);
	}
}
