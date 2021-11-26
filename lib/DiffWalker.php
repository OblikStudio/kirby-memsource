<?php

namespace Oblik\Memsource;

use Kirby\Cms\Field;
use Kirby\Cms\ModelWithContent;
use Oblik\Walker\Walker\Walker;

class DiffWalker extends Walker
{
	public static function walk(ModelWithContent $model, array $context = [])
	{
		$context['translation'] = Walker::walk($model, [
			'lang' => $context['lang']
		]);

		// Set the main walked data to always be the default language.
		$context['lang'] = kirby()->defaultLanguage()->code();

		return parent::walk($model, $context);
	}

	protected static function subcontext($key, $context)
	{
		$context = parent::subcontext($key, $context);
		$translation = $context['translation'] ?? null;

		if (is_array($translation)) {
			$context['translation'] = static::findMatchingEntry($key, $translation, $context);
		}

		return $context;
	}

	protected static function walkField(Field $field, $context)
	{
		$value = parent::walkField($field, $context);
		$type = $context['blueprint']['type'] ?? null;

		if (in_array($type, ['structure', 'blocks', 'editor'])) {
			return $value;
		}

		$oldValue = $context['translation'] ?? $value;
		$newValue = $context['input'] ?? $oldValue;

		if ($newValue !== $oldValue) {
			return [
				'$new' => $newValue,
				'$old' => $oldValue
			];
		}
	}
}
