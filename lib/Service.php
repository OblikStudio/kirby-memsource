<?php

namespace Oblik\Memsource;

use Error;
use Kirby\Http\Cookie;
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

	public $token = null;

	public function __construct()
	{
		$this->token = Cookie::get('memsource_session');
	}

	public function login()
	{
		$remote = Remote::request(self::API_URL . '/auth/login', [
			'method' => 'POST',
			'data' => json_encode(kirby()->request()->data()),
			'headers' => [
				'Content-Type' => 'application/json'
			]
		]);

		if ($remote->code() === 200) {
			$data = json_decode($remote->content(), true);
			$token = $data['token'] ?? null;
			$expires = $data['expires'] ?? null;

			if ($token) {
				Cookie::set('memsource_session', $token, [
					'lifetime' => strtotime($expires)
				]);
			}
		}

		return new Response($remote->content(), 'application/json', $remote->code());
	}

	public function get(string $resource)
	{
		$remote = Remote::request(self::API_URL . '/' . $resource . '?token=' . $this->token, [
			'method' => 'GET'
		]);

		return new Response($remote->content(), 'application/json', $remote->code());
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
