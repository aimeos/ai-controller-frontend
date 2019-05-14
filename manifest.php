<?php

return array(
	'name' => 'ai-controller-frontend',
	'depends' => array(
		'aimeos-core',
	),
	'config' => array(
		'config',
	),
	'include' => array(
		'controller/frontend/src',
	),
	'i18n' => array(
		'controller/frontend' => 'controller/frontend/i18n',
	),
);
