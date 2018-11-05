<?php

$site_content = array();

function extractFields ($object) {
	$data = array();

	foreach ($object->content()->fields() as $key => $value) {
		$field = $object->$value();

		if (!$field->isEmpty()) {
			$data[$field->key()] = $field->value();
		}
	}

	return $data;
}

foreach (site()->children() as $key => $value) {
	$site_content[$value->uri()] = extractFields($value);
}

// var_dump($site_content);