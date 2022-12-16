<?php

return [
	'mode'                  => 'utf-8',
	'format'                => 'A4',
	'author'                => '',
	'subject'               => '',
	'keywords'              => '',
	'creator'               => 'Laravel Pdf',
	'display_mode'          => 'fullpage',
	'tempDir'               => storage_path('app/temp'), // base_path(storage_path('app/public')),
	'pdf_a'                 => false,
	'pdf_a_auto'            => false,
	'icc_profile_path'      => '',
	'font_path'             => base_path('resources/fonts/'),
	'font_data' => [
			'bangla' => [
				'R'  => 'SolaimanLipi.ttf',    // regular font
				'B'  => 'SolaimanLipi.ttf',       // optional: bold font
				'I'  => 'SolaimanLipi.ttf',     // optional: italic font
				'BI' => 'SolaimanLipi.ttf', // optional: bold-italic font
				'useOTL' => 0xFF,
				'useKashida' => 75,
			]
	],		
];
