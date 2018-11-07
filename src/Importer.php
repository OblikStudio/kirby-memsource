<?php
namespace Memsource;

use Yaml;

class Importer {
	public static function updatePage ($page, $data, $lang) {
		$currentData = $page->content($lang)->data();
		$normalizedData = array();

		// Normalize data by getting field values and parsing YAML when needed.
		foreach ($data as $key => $value) {
			if (!empty($currentData[$key])) {
				$normalizedData[$key] = $currentData[$key]->value();

				if (is_array($value)) {
					// If the translated value is an array, that means this
					// field was a structure when it was exported, so it must
					// still be a structure and should be parsed.
					$normalizedData[$key] = Yaml::read($normalizedData[$key]);
				}
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
