<?php

return [
    'upload_types' => [
        'waddely' => [
            'visibility'               => 'private',
            'dont_clean'               => true,
            'supports_folders'         => true,
            'supports_max_space_usage' => true,
            'max_space_usage_setting'  => 'drive.default_available_space',
            'label'                    => 'Makhzan Waddely uploads',
            'description'              => 'All files uploaded by users in Makhzan Waddely.',
            'defaults'                 => [
                'max_file_size' => '100000000000',
            ],
        ],
    ],
];
