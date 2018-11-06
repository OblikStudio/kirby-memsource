<?php
namespace Memsource;

class Exporter {
	public static $ignored_fields = array('line', 'tabs', 'image', 'date', 'toggle', 'headline');

	private static function isFieldTranslatable ($field) {
		if (isset($field['translate']) && $field['translate'] == false) {
			return false;
		}

		if (isset($field['type']) && in_array($field['type'], static::$ignored_fields)) {
			return false;
		}

		return true;
	}

	private static function blueprintTranslatableFields ($data, &$result = array()) {
		foreach ($data['fields'] as $key => $value) {
			$fieldId = str_replace(array('-', ' '), '_', strtolower(trim($key))); // from Kirby core
			$isTranslatable = static::isFieldTranslatable($value);

			if ($isTranslatable && !empty($value['fields'])) {
				if (!empty($value['type']) && $value['type'] === 'structure') {
					// Store `structure` fields in a new array.
					$result[$fieldId] = static::blueprintTranslatableFields($value);
				} else {
					// Store primitive fields in the flattened array.
					static::blueprintTranslatableFields($value, $result);
				}
			} else {
				$result[$fieldId] = $isTranslatable;
			}
		}

		return $result;
	}

	private static function exportObject ($fields, $translatable) {
		$data = array();

		foreach ($translatable as $key => $value) {
			if (isset($fields[$key])) {
				if (is_array($value)) {
					// The translatable map is an array, so this field should
					// be a structure that can be parsed to an array.

					$data[$key] = array();
					$fieldData = $fields[$key]->yaml();

					foreach ($fieldData as $entry) {
						$exportedChild = static::exportObject($entry, $value);

						if (!empty($exportedChild)) {
							array_push($data[$key], $exportedChild);
						}
					}
					
					// If the field has no children or there are no
					// translatable fields, remove the empty array.
					if (empty($data[$key])) {
						unset($data[$key]);
					}					
				} else if ($value === true) {
					if (method_exists($fields[$key], 'value')) {
						$fieldValue = $fields[$key]->value(); // if the value is a Kirby Field object
					} else {
						$fieldValue = $fields[$key]; // if the value is primitive
					}

					if (!empty($fieldValue)) {
						$data[$key] = $fieldValue;
					}
				}
			}
		}

		return $data;
	}

	public static function exportPage ($page, &$data = array()) {
		$reader = new BlueprintReader;
		$id = $page->isSite() ? '$site' : $page->id();

		if (!empty($id)) {
			$blueprint = $reader->get($page->template());
			$translatable = static::blueprintTranslatableFields($blueprint);
			$data[$id] = static::exportObject($page->content()->data(), $translatable);
		}

		$children = $page->children();
		if (count($children) !== 0) {
			foreach ($children as $key => $child) {
				static::exportPage($child, $data);
			}
		}
		
		return $data;
	}

	public static function export () {
		return static::exportPage(site());
	}
}
