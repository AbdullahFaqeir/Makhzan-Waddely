<?php

return [
    // laravel
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => public_path('storage'),
            'throw'  => true,
        ],

        'waddely_abdullah_backblaze' => [
            'driver'                  => 's3',
            'key'                     => '0043de1bccd87590000000003',
            'secret'                  => 'K004Xj10OKT9YFMsTrWqr+7o75sUb5U',
            'region'                  => 's3.us-west-004',
            'bucket'                  => 'waddely-backblaze-01',
            'endpoint'                => 'https://waddely-backblaze-01.s3.us-west-004.backblazeb2.com',
            'use_path_style_endpoint' => true,
            'visibility'              => 'private',
            'throw'                   => true,
            'checksum'                => false,
            'options'                 => [
                'http' => [
                    'verify'  => false, // just in case, for SSL edge cases
                    'headers' => [
                        'x-amz-checksum-crc32' => null,
                    ],
                ],
            ],
        ],

        'idrivee2_abdullah' => [
            'driver'                  => 's3',
            'key'                     => 'xCZRGwZsCl6QM0HIOjvJ',
            'secret'                  => 'DibwTPF4cVkNT2DzDvGTdbDBMLtliFfS3RLkCd3y',
            'region'                  => 'eu-central-2',
            'bucket'                  => 'waddely',
            'endpoint'                => 'https://s3.eu-central-2.idrivee2.com',
            'use_path_style_endpoint' => true,
            'visibility'              => 'private',
            'throw'                   => true,
            'checksum'                => false,
            'options'                 => [
                'http' => [
                    'verify'  => false, // just in case, for SSL edge cases
                    'headers' => [
                        'x-amz-checksum-crc32' => null,
                    ],
                ],
            ],
        ],

        'idrivee2_info' => [
            'driver'                  => 's3',
            'key'                     => 'gpjwWDvhCjfPZdik1IVs',
            'secret'                  => '9FDhWkYqiPn0MMepKIGLYT47J9HCcm7ZxeY8C5Hq',
            'region'                  => 'us-west-1',
            'bucket'                  => 'waddely',
            'endpoint'                => 'https://s3.us-west-1.idrivee2.com',
            'use_path_style_endpoint' => true,
            'visibility'              => 'private',
            'throw'                   => true,
            'checksum'                => false,
            'options'                 => [
                'http' => [
                    'verify'  => false, // just in case, for SSL edge cases
                    'headers' => [
                        'x-amz-checksum-crc32' => null,
                    ],
                ],
            ],
        ],

        'idrivee2_alfaqeir' => [
            'driver'                  => 's3',
            'key'                     => 'IPq0lzBMb8REmrLC9LOm',
            'secret'                  => '1eTwE4gnCYSz80Nknb7NumuNFTAj1zw34bTix2EG',
            'region'                  => 'eu-west-1',
            'bucket'                  => 'waddely',
            'endpoint'                => 'https://s3.eu-west-1.idrivee2.com',
            'use_path_style_endpoint' => true,
            'visibility'              => 'private',
            'throw'                   => true,
            'checksum'                => false,
            'options'                 => [
                'http' => [
                    'verify'  => false, // just in case, for SSL edge cases
                    'headers' => [
                        'x-amz-checksum-crc32' => null,
                    ],
                ],
            ],
        ],

        'idrivee2_teketat' => [
            'driver'                  => 's3',
            'key'                     => '6dYT6xBgFYuSW1UyeiN3',
            'secret'                  => '9078Vfq5mwndmyqoW5eOAst7MwTPRx1WLmlpvJdP',
            'region'                  => 'eu-west-3',
            'bucket'                  => 'waddely',
            'endpoint'                => 'https://s3.eu-west-3.idrivee2.com',
            'use_path_style_endpoint' => true,
            'visibility'              => 'private',
            'throw'                   => true,
            'checksum'                => false,
            'options'                 => [
                'http' => [
                    'verify'  => false, // just in case, for SSL edge cases
                    'headers' => [
                        'x-amz-checksum-crc32' => null,
                    ],
                ],
            ],
        ],

        'idrivee2_3eyadah' => [
            'driver'                  => 's3',
            'key'                     => 'X30SBq0OR48E1eKLws2d',
            'secret'                  => 'WWJRexAXSxUyCqfBZ5S6cXl3H5VmQQpUz3OTvXuX',
            'region'                  => 'eu-west-4',
            'bucket'                  => 'waddely',
            'endpoint'                => 'https://s3.eu-west-4.idrivee2.com',
            'use_path_style_endpoint' => true,
            'visibility'              => 'private',
            'throw'                   => true,
            'checksum'                => false,
            'options'                 => [
                'http' => [
                    'verify'  => false, // just in case, for SSL edge cases
                    'headers' => [
                        'x-amz-checksum-crc32' => null,
                    ],
                ],
            ],
        ],
    ],

    'upload_types'               => [
        'avatars'        => [
            'visibility'  => 'public',
            'label'       => 'User avatars',
            'description' => 'Avatars uploaded by users on the site.',
            'defaults'    => [
                'prefix'        => 'avatars',
                'accept'        => ['image'],
                'max_file_size' => '1048576',
            ],
        ],
        'brandingImages' => [
            'visibility'  => 'public',
            'label'       => 'Branding images',
            'description' => 'Logos, landing page images, previews etc. uploaded from admin area.',
            'defaults'    => [
                'prefix'        => 'branding-images',
                'accept'        => ['image'],
                'max_file_size' => '3145728',
            ],
        ],
        'articleImages'  => [
            'visibility'  => 'public',
            'label'       => 'Article images',
            'description' => 'Inline article and custom page images uploaded from editor.',
            'defaults'    => [
                'prefix'        => 'article-images',
                'accept'        => ['image'],
                'max_file_size' => '3145728',
            ],
        ],
    ],

    // app
    'disable_thumbnail_creation' => env('DISABLE_THUMBNAIL_CREATION', false),
    'use_presigned_s3_urls'      => env('USE_PRESIGNED_S3_URLS', true),
    'static_file_delivery'       => env('STATIC_FILE_DELIVERY', null),
    'uploads_disable_tus'        => env('UPLOADS_DISABLE_TUS'),
    'uploads_tus_method'         => env('UPLOADS_TUS_METHOD'),

    // legacy
    'uploads_disk_driver'        => env('UPLOADS_DISK_DRIVER', 'local'),
    'public_disk_driver'         => env('PUBLIC_DISK_DRIVER', 'local'),
    'file_preview_endpoint'      => env('FILE_PREVIEW_ENDPOINT'),
];
