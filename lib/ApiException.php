<?php

namespace Oblik\Memsource;

use Kirby\Exception\Exception;
use Kirby\Http\Remote;

class ApiException extends Exception
{
	public function __construct(Remote $remote)
	{
		$data = json_decode($remote->content(), true);

		parent::__construct([
			'key' => 'memsource.api',
			'fallback' => $data['errorDescription'] ?? null,
			'httpCode' => $remote->code()
		]);
	}
}
