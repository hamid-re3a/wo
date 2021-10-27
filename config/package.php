<?php

return [
    'categories' => [
        'beginner' => [
            "id" => 1,
            "name" => 'Beginner',
            "short_name" => 'B',

            "roi_percentage" => 1,
            "direct_percentage" => 8,
            "binary_percentage" => 7,

            "package_validity_in_days" => 200,
        ],
        'intermediate' => [
            "id" => 2,
            "name" => 'Intermediate',
            "short_name" => 'I',

            "roi_percentage" => 1,
            "direct_percentage" => 8,
            "binary_percentage" => 8,

            "package_validity_in_days" => 200,
        ],
        'advance' => [
            "id" => 3,
            "name" => 'Advance',
            "short_name" => 'A',

            "roi_percentage" => 1,
            "direct_percentage" => 8,
            "binary_percentage" => 9,

            "package_validity_in_days" => 200,
        ],
        'professional' => [
            "id" => 2,
            "name" => 'Professional',
            "short_name" => 'P',

            "roi_percentage" => 1,
            "direct_percentage" => 8,
            "binary_percentage" => 10,

            "package_validity_in_days" => 200,
        ],
    ],
    'packages' => [
        /**
         * beginner
         */
        [
            "name" => 'Beginner 1',
            "short_name" => 'B1',
            'price' => 99,
            'category_id' => 1,
        ], [
            "name" => 'Beginner 2',
            "short_name" => 'B2',
            'price' => 249,
            'category_id' => 1,
        ], [
            "name" => 'Beginner 3',
            "short_name" => 'B3',
            'price' => 499,
            'category_id' => 1,
        ], [
            "name" => 'Beginner 4',
            "short_name" => 'B4',
            'price' => 749,
            'category_id' => 1,
        ],


        /**
         * Intermediate
         */
        [
            "name" => 'Intermediate 1',
            "short_name" => 'I1',
            'price' => 999,
            'category_id' => 2,
        ], [
            "name" => 'Intermediate 2',
            "short_name" => 'I2',
            'price' => 2499,
            'category_id' => 2,
        ], [
            "name" => 'Intermediate 3',
            "short_name" => 'I3',
            'price' => 4999,
            'category_id' => 2,
        ], [
            "name" => 'Intermediate 4',
            "short_name" => 'I4',
            'price' => 7499,
            'category_id' => 2,
        ],

        /**
         * Advance
         */
        [
            "name" => 'Advance 1',
            "short_name" => 'A1',
            'price' => 9999,
            'category_id' => 3,
        ], [
            "name" => 'Advance 2',
            "short_name" => 'A2',
            'price' => 19999,
            'category_id' => 3,
        ], [
            "name" => 'Advance 3',
            "short_name" => 'A3',
            'price' => 34999,
            'category_id' => 3,
        ], [
            "name" => 'Advance 4',
            "short_name" => 'A4',
            'price' => 49999,
            'category_id' => 3,
        ],
        /**
         * Professional
         */
        [
            "name" => 'Pro 1',
            "short_name" => 'P1',
            'price' => 99999,
            'category_id' => 4,
        ], [
            "name" => 'Pro 2',
            "short_name" => 'P2',
            'price' => 299999,
            'category_id' => 4,
        ], [
            "name" => 'Pro 3',
            "short_name" => 'P3',
            'price' => 499999,
            'category_id' => 4,
        ], [
            "name" => 'Pro 4',
            "short_name" => 'P4',
            'price' => 999999,
            'category_id' => 4,
        ],

    ],
    'categories-indirect-commissions' => [

        /**
         * beginner indirect settings
         */
        [
            'category_id' => 1,
            'level' => 1,
            'percentage' => 3
        ], [
            'category_id' => 1,
            'level' => 2,
            'percentage' => 2
        ], [
            'category_id' => 1,
            'level' => 3,
            'percentage' => 1
        ], [
            'category_id' => 1,
            'level' => 4,
            'percentage' => 1
        ],

        /**
         * Intermediate indirect settings
         */

        [
            'category_id' => 2,
            'level' => 1,
            'percentage' => 3
        ], [
            'category_id' => 2,
            'level' => 2,
            'percentage' => 2
        ], [
            'category_id' => 2,
            'level' => 3,
            'percentage' => 1
        ], [
            'category_id' => 2,
            'level' => 4,
            'percentage' => 1
        ], [
            'category_id' => 2,
            'level' => 5,
            'percentage' => 1
        ],

        /**
         * Advance indirect settings
         */


        [
            'category_id' => 3,
            'level' => 1,
            'percentage' => 3
        ], [
            'category_id' => 3,
            'level' => 2,
            'percentage' => 2
        ], [
            'category_id' => 3,
            'level' => 3,
            'percentage' => 1
        ], [
            'category_id' => 3,
            'level' => 4,
            'percentage' => 1
        ], [
            'category_id' => 3,
            'level' => 5,
            'percentage' => 1
        ], [
            'category_id' => 3,
            'level' => 6,
            'percentage' => 1
        ], [
            'category_id' => 3,
            'level' => 7,
            'percentage' => 1
        ],

        /**
         * Pro indirect settings
         */

        [
            'category_id' => 4,
            'level' => 1,
            'percentage' => 3
        ], [
            'category_id' => 4,
            'level' => 2,
            'percentage' => 2
        ], [
            'category_id' => 4,
            'level' => 3,
            'percentage' => 1
        ], [
            'category_id' => 4,
            'level' => 4,
            'percentage' => 1
        ], [
            'category_id' => 4,
            'level' => 5,
            'percentage' => 1
        ], [
            'category_id' => 4,
            'level' => 6,
            'percentage' => 1
        ], [
            'category_id' => 4,
            'level' => 7,
            'percentage' => 1
        ], [
            'category_id' => 4,
            'level' => 8,
            'percentage' => 1
        ], [
            'category_id' => 4,
            'level' => 9,
            'percentage' => 1
        ],
    ]

];
