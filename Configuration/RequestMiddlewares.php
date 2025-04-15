<?php

use JambageCom\TtBoard\Middleware\FrontendHooks;
use JambageCom\TtBoard\Middleware\SessionStart;

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
        ],
        'jambagecom/tt-board/session-start' => [
            'target' => SessionStart::class,
            'description' => 'Initialisation of the session',
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering'
            ],
            'before' => [
                'typo3/cms-frontend/content-length-headers',
            ],
        ]
    ]
];
