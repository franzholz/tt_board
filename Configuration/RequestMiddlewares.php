<?php

use JambageCom\TtBoard\Middleware\FrontendHooks;
return [
    'frontend' => [
        'jambagecom/tt-board/preprocessing' => [
            'target' => FrontendHooks::class,
            'description' => 'Initialisation of global variables for hooks',
            'after' => [
                'typo3/cms-frontend/tsfe'
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering'
            ]
        ]
    ]
];
