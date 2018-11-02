<?php

return [
    /**
     * Add the Fully Qualified Names of the Providers to be added to this SnapWP theme.
     *
     * @var array
     */
	'providers' => [
		Snap\Debug\Debug_Service_Provider::class,
		//Snap\Blade\Blade_Service_Provider::class,
	],


    /**
     * Alias a given class to another class.
     *
     * Useful for making namespaced classes available in templates without typing the full namespace.
     *
     * @var array
     */
    'aliases' => [
    ],
];