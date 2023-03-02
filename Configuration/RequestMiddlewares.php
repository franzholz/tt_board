<?php

return [
    'frontend' => [
        'jambagecom/tt-board/preprocessing' => [
            'target' => \JambageCom\TtBoard\Middleware\FrontendHooks::class,
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

