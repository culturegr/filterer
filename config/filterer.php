<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Pagination Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the default behavior of the Filterer pagination
    | methods. You can override these values by passing parameters directly
    | to the methods.
    |
    */

    'pagination' => [
        /*
        |--------------------------------------------------------------------------
        | Default Items Per Page
        |--------------------------------------------------------------------------
        |
        | This value determines the default number of items returned per page
        | when using filterPaginate() or filterSimplePaginate() methods without
        | specifying a limit parameter.
        |
        | Environment variable: FILTERER_DEFAULT_LIMIT
        |
        */
        'default_limit' => env('FILTERER_DEFAULT_LIMIT', 10),

        /*
        |--------------------------------------------------------------------------
        | Maximum Items Per Page
        |--------------------------------------------------------------------------
        |
        | This value sets the maximum number of items that can be requested
        | per page. This helps prevent performance issues from overly large
        | page sizes. Set to null to disable the limit.
        |
        | Environment variable: FILTERER_MAX_LIMIT
        |
        */
        'max_limit' => env('FILTERER_MAX_LIMIT'),

        /*
        |--------------------------------------------------------------------------
        | Page Parameter Name
        |--------------------------------------------------------------------------
        |
        | The name of the query parameter used for pagination. This should
        | match Laravel's default pagination parameter name unless you have
        | a specific reason to change it.
        |
        | Environment variable: FILTERER_PAGE_NAME
        |
        */
        'page_name' => env('FILTERER_PAGE_NAME', 'page'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how the Filterer validates incoming parameters.
    |
    */

    'validation' => [
        /*
        |--------------------------------------------------------------------------
        | Custom Error Messages
        |--------------------------------------------------------------------------
        |
        | You can customize the validation error messages returned when
        | parameters fail validation. Leave empty to use your application's
        | validation translations.
        |
        */
        'error_messages' => [
            // 'limit.max' => 'The limit may not be greater than :max.',
            // 'filters.*.column.in' => 'The selected filter column is not allowed.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | These settings help protect your application from potential security
    | issues related to filtering and sorting operations.
    |
    */

    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Allowed Operators
        |--------------------------------------------------------------------------
        |
        | You can restrict which operators are available globally. This provides
        | an additional layer of security by preventing potentially dangerous
        | operations. Leave empty to allow all default operators. Operators
        | not supported by the package are ignored.
        |
        */
        'allowed_operators' => [
            // 'equal_to',
            // 'not_equal_to',
            // 'less_than',
            // 'greater_than',
            // 'between',
            // 'not_between',
            // 'contains',
            // 'starts_with',
            // 'between_date',
            // 'in',
        ],
    ],
];
