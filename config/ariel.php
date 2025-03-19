<?php     

return [
    'default_container'=>'bootstrap_v4',
    'inArray' => [
        'multiselect',
        'multicheckbox'
    ],
    'delete_old_files'=>false,
    'number_format_postfix'=>'$',
    'danger_alert'=>'danger',
    'info_alert'=>'info',
    'success_alert'=>'success',
    'upload_path'=>public_path('uploads'),
    'configureMethods'=>["addColumn","addAction","addField","addHidden","addProcess","clear","validateRequest","setRoute"],
    'skipTypes'=>['hr','imageview','view'],

    ];
