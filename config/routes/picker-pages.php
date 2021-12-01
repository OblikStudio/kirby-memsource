<?php

namespace Oblik\Memsource;

use Kirby\Cms\PagePicker;

return [
	'pattern' => 'memsource/picker/pages',
	'method' => 'GET',
	'action' => function () {
		return (new PagePicker([
			'page' => $this->requestQuery('page'),
			'parent' => $this->requestQuery('parent'),
			'search' => $this->requestQuery('search')
		]))->toArray();
	}
];
