<?php

return [
	'name' => 'ai-controller-frontend',
	'depends' => [
		'aimeos-core',
	],
	'config' => [
		'config',
	],
	'include' => [
		'controller/frontend/src',
	],
	'i18n' => [
		'controller/frontend' => 'controller/frontend/i18n',
	],
];
