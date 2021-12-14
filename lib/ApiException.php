<?php

namespace Oblik\Memsource;

use Kirby\Exception\Exception;

class ApiException extends Exception
{
	public function __construct($data)
	{
		parent::__construct([
			'key' => 'memsource.api',
			'httpCode' => 500,
			'details' => $data
		]);

		/**
		 * Memsource could respond with a message containing `{` and `}`, which
		 * Kirby would interpret as a template string, so we assign the error
		 * message here, instead of passing it to the constructor.
		 */
		if (!empty($data['errorDescription'])) {
			$this->message = $data['errorDescription'];
		}
	}
}
