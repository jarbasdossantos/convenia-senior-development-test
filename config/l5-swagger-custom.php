<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Convenia API',
                'version' => '1.0.0',
                'description' => 'API para gerenciamento de colaboradores',
                'contact' => [
                    'email' => 'eu@jarbas.dev',
                    'name' => 'Convenia',
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://opensource.org/licenses/MIT',
                ],
            ],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'paths' => [
                'annotations' => [
                    base_path('app'),
                ],
            ],
        ],
    ],
    'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),
    'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),
    'proxy' => false,
    'additional_config_url' => null,
    'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
    'validator_url' => null,
    'ui' => [
        'api_credentials' => env('L5_SWAGGER_UI_API_CREDENTIALS', false),
        'validator_url' => env('L5_SWAGGER_UI_VALIDATOR_URL', null),
        'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),
        'layout' => 'StandaloneLayout',
        'display_operation_id' => false,
        'doc_expansion' => 'list',
        'filter' => true,
        'show_request_headers' => true,
        'supported_submit_methods' => ['get', 'post', 'put', 'delete', 'patch'],
        'oauth2_default_scopes' => null,
        'oauth2_scopes' => null,
        'try_it_out_enabled' => true,
    ],
];
