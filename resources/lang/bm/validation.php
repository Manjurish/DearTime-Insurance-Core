<?php     

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ' :attribute mesti diterima.',
    'active_url' => ' :attribute URL ini tidak sah.',
    'after' => ' :attribute mestilah satu tarikh selepas :date.',
    'after_or_equal' => ' :attribute mestilah satu tarikh selepas atau bersamaan dengan :date.',
    'alpha' => ' :attribute hanya boleh mengandungi huruf.',
    'alpha_dash' => ' :attribute hanya boleh mengandungi huruf, nombor, sengkang dan garis bawah.',
    'alpha_num' => ' :attribute hanya boleh mengandungi huruf dan nombor.',
    'array' => ' :attribute mestilah tatasusunan',
    'before' => ' :attribute mestilah satu tarikh sebelum :date.',
    'before_or_equal' => ':attribute mestilah satu tarikh sebelum atau bersamaan dengan :date',
    'between' => [
        'numeric' => ' :attribute mestilah diantara :min and :max.',
        'file' => ' :attribute mestilah di antara: min dan :max kilobait.',
        'string' => ' :attribute mestilah antara: min dan :max aksara.',
        'array' => ' :attribute mesti mempunyai antara: min dan :max item.',
    ],
    'boolean' => ' :attribute medan mestilah benar atau palsu.',
    'confirmed' => ' :attribute pengesahan tidak padan.',
    'date' => ' :attribute ini bukan tarikh yang sah.',
    'date_equals' => ' :attribute mestilah satu tarikh bersamaan dengan :date.',
    'date_format' => ' :attribute tidak sepadan dengan format :format.',
    'different' => ' :attribute dan :other mestilah berbeza.',
    'digits' => ' :attribute mesti lah :digits digit.',
    'digits_between' => ' :attribute mestilah di antara :min dan :max digit.',
    'dimensions' => ' :attribute mempunyai dimensi imej yang tidak sah.',
    'distinct' => ' :attribute medan mempunyai nilai pendua.',
    'email' => ' :attribute mestilah alamat e-mel yang sah.',
    'exists' => ' :attribute yang dipilih adalah tidak sah.',
    'file' => ' :attribute mestilah satu fail.',
    'filled' => 'medan :attribute mestilah ada nilai.',
    'gt' => [
        'numeric' => ' :attribute mestilah lebih besar daripada :value.',
        'file' => ' :attribute mestilah lebih besar daripada :value kilobait.',
        'string' => ' :attribute mestilah lebih besar daripada :value aksara.',
        'array' => ' :attribute mesti mempunyai lebih daripada :value item .',
    ],
    'gte' => [
        'numeric' => ' :attribute mestilah lebih besar daripada atau sama :value.',
        'file' => ' :attribute mestilah lebih besar daripada atau sama :value kilobait.',
        'string' => ' :attribute mestilah lebih besar daripada atau sama :value aksara .',
        'array' => ' :attribute mesti mempunyai :value item atau lebih.',
    ],
    'image' => ' :attribute mestilah satu imej.',
    'in' => ' :attribute yang dipilih adalah tidak sah.',
    'in_array' => ' :attribute medan tidak wujud dalam :other.',
    'integer' => ' :attribute mestilah integer.',
    'ip' => ' :attribute mestilah alamat IP yang sah.',
    'ipv4' => ' :attribute mestilah alamat IPv4 yang sah.',
    'ipv6' => ' :attribute mestilah alamat IPv6 yang sah.',
    'json' => ' :attribute mestilah rentetan JSON yang sah.',
    'lt' => [
        'numeric' => ' :attribute mestilah kurang daripada :value.',
        'file' => ' :attribute mesti kurang daripada :value kilobait.',
        'string' => ' :attribute mesti kurang daripada :value aksara .',
        'array' => ' :attribute mesti mempunyai kurang daripada :value item.',
    ],
    'lte' => [
        'numeric' => ' :attribute mestilah kurang daripada atau sama :value.',
        'file' => ' :attribute mestilah kurang daripada atau sama :value kilobait.',
        'string' => ' :attribute mestilah kurang daripada atau sama :value aksara .',
        'array' => ' :attribute tidak boleh mempunyai lebih daripada :value item .',
    ],
    'max' => [
        'numeric' => ' :attribute tidak boleh lebih besar daripada :max.',
        'file' => ' :attribute tidak boleh lebih besar daripada :max kilobait.',
        'string' => ' :attribute tidak boleh lebih besar daripada :max aksara .',
        'array' => ' :attribute tidak boleh lebih daripada :max item.',
    ],
    'mimes' => ' :attribute mestilah satu fail jenis :value.',
    'mimetypes' => ' :attribute mestilah satu fail jenis :value.',
    'min' => [
        'numeric' => ' :attribute mestilah sekurang-kurangnya :min.',
        'file' => ' :attribute mestilah sekurang-kurangnya :min kilobait.',
        'string' => ' :attribute  mestilah sekurang-kurangnya :min aksara.',
        'array' => ' :attribute mestilah sekurang-kurangnya :min item.',
    ],
    'not_in' => ' :attribute yang dipilih adalah tidak sah.',
    'not_regex' => ' :attribute format adalah tidak sah.',
    'numeric' => ' :attribute mestilah nombor.',
    'present' => ' :attribute medan mestilah hadir.',
    'regex' => ' :attribute adalah tidak sah.',
    'required' => ' :attribute medan diperlukan.',
    'required_if' => ' :attribute medan diperlukan apabila :other ialah :value.',
    'required_unless' => ' :attribute medan diperlukan kecuali :other adalah dalam :value.',
    'required_with' => ' :attribute medan diperlukan apabila terdapat :value.',
    'required_with_all' => ' :attribute medan diperlukan apabila terdapat :value.',
    'required_without' => ' :attribute medan diperlukan apabila tidak terdapat :value.',
    'required_without_all' => ' :attribute medan diperlukan apabila tiada terdapat :value.',
    'same' => ' :attribute dan :other mesti padan.',
    'size' => [
        'numeric' => ' :attribute mestilah :size.',
        'file' => ' :attribute mestilah :size kilobait.',
        'string' => ' :attribute mestilah :size aksara.',
        'array' => ' :attribute mesti mengandungi :size item.',
    ],
    'starts_with' => ' :attribute mesti bermula dengan salah satu daripada yang berikut: :value',
    'string' => ' :attribute mestilah rentetan.',
    'timezone' => ' :attribute mestilah zon yang sah.',
    'unique' => ' :attribute telah pun diambil.',
    'uploaded' => ' :attribute  gagal untuk memuat naik.',
    'url' => ' :attribute format tidak sah.',
    'uuid' => ' :attribute mestilah UUID yang sah.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'mesej-custom',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
		'select_source_of_fund'       => "Pilih Sekurang-kurangnya Satu Sumber Pendapatan",
	],

];
