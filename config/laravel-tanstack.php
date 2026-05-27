<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default page size when client does not specify per_page.
    |--------------------------------------------------------------------------
    */
    'default_per_page' => 25,

    /*
    |--------------------------------------------------------------------------
    | Maximum page size. Requests exceeding this are clamped.
    | Prevents clients from pulling huge result sets at once.
    |--------------------------------------------------------------------------
    */
    'max_per_page' => 100,

    /*
    |--------------------------------------------------------------------------
    | Case-insensitive search. On MySQL this depends on collation; on
    | Postgres you may want to swap LIKE with ILIKE in a custom search.
    |--------------------------------------------------------------------------
    */
    'case_insensitive' => true,

    /*
    |--------------------------------------------------------------------------
    | When true, exceptions during the datatable lifecycle are reported via
    | Laravel's report() helper. Set to false in tests if noisy.
    |--------------------------------------------------------------------------
    */
    'report_exceptions' => true,

];
