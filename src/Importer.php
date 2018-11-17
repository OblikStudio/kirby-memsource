<?php
namespace Memsource;

use Yaml;

class Importer {
	public static function clean ($data) {
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$data[$key] = static::clean($value);
			} else if (empty($value)) {
				unset($data[$key]);
			}
		}

		return $data;
	}

	public static function updatePage ($page, $data, $lang) {
		// Clean the input data so that empty strings won't overwrite the non-
		// empty default language values later.
		$data = static::clean($data);

		$currentData = $page->content($lang)->data();
		$normalizedData = array();

		// Make up an array with the current data and normalize it by parsing
		// YAML and extracting field values.
		foreach ($currentData as $key => $field) {
			$normalizedData[$key] = $field->value();

			if (!empty($data[$key]) && is_array($data[$key])) {
				// If the translated value is an array, that means this
				// field was a structure when it was exported, so it must
				// still be a structure and should be parsed.

				try {
					$normalizedData[$key] = Yaml::read($normalizedData[$key]);
				} catch (\Exception $e) {}
			}
		}

		$mergedData = array_replace_recursive($normalizedData, $data);

		// Encode all arrays back to YAML because that's how Kirby stores
		// them. If they are not pased, an empty value will be saved.
		foreach ($mergedData as $key => $value) {
			if (is_array($value)) {
				$mergedData[$key] = Yaml::encode($mergedData[$key]);
			}
		}

		$page->update($mergedData, $lang);
	}

	public static function import($data, $lang) {
		foreach ($data as $pageId => $value) {
			$page = null;

			if ($pageId === '$site') {
				$page = site();
			} else {
				$page = site()->children()->find($pageId);
			}

			if ($page) {
				static::updatePage($page, $value, $lang);
			}
		}
	}
}
