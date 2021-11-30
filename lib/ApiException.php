<?php

namespace Oblik\Memsource;

use Kirby\Exception\Exception;

class ApiException extends Exception
{
	public function __construct($data)
	{
		parent::__construct([
			'key' => 'memsource.api',
			'fallback' => $data['errorDescription'] ?? null,
			'httpCode' => 500,
			'details' => $data
		]);
	}
}
