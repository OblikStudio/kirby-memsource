<?php

namespace Oblik\Memsource;

use Kirby\Http\Cookie;
use Kirby\Http\Remote;
use Kirby\Http\Response;

class Service
{
	public const API_URL = 'https://cloud.memsource.com/web/api2/v1';

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
		$remote = Remote::request(self::API_URL . '/projects/' . $projectId . '/jobs?token=' . $this->token, [
			'method' => 'POST',
			'data' => json_encode(kirby()->request()->data()),
			'headers' => [
				'Memsource' => kirby()->request()->headers()['Memsource'],
				'Content-Type' => 'application/octet-stream',
				'Content-Disposition' => "filename*=UTF-8''{$filename}"
			]
		]);

		return new Response($remote->content(), 'application/json', $remote->code());
	}
}
