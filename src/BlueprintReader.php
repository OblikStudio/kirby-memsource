<?php
namespace Memsource;

use Yaml;

class BlueprintReader {
	private static $cache = [];

	private static function parseField ($field) {
		$data = $field;

		if (is_string($field)) {
			$data = static::getGlobal($field);
		} else if (!empty($field['extends'])) {
			$blueprint = static::getGlobal($field['extends']);

			if (!empty($blueprint)) {
				$data = array_replace_recursive($blueprint, $field);
				unset($data['extends']);
			}
		}

		return $data;
	}

	private static function parseFields ($fields) {
		foreach ($fields as $key => $value) {
			$fields[$key] = static::parseField($value);
			
			if (!empty($fields[$key]['fields'])) {
				$fields[$key]['fields'] = static::parseFields($fields[$key]['fields']);
			}
		}

		return $fields;
	}

	public static function get ($template) {
		if (empty(static::$cache[$template])) {
			$blueprint = kirby()->get('blueprint', $template);

			if ($blueprint) {
				$blueprint = Yaml::read($blueprint);

				if (!empty($blueprint) && !empty($blueprint['fields'])) {
					$blueprint['fields'] = static::parseFields($blueprint['fields']);
				}

				static::$cache[$template] = $blueprint;
			}
		}

		if (!empty(static::$cache[$template])) {
			return static::$cache[$template];
		} else {
			return null;
		}
	}

	public static function getGlobal ($template) {
		return static::get('fields' . DS . $template);
	}
}
