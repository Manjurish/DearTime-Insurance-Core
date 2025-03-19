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

    'accepted' => ':attribute 必须被接受。',
    'active_url' => ':attribute 不是正确的网址。',
    'after' => ':attribute 必须是 :date 之后的日期。',
    'after_or_equal' => ':attribute 必须是等于 :date 或之后的日期.',
    'alpha' => ':attribute 只能接受文字.',
    'alpha_dash' => ':attribute 只能接受文字，数字，破折号和下划线.',
    'alpha_num' => ':attribute 只能接受文字和数字.',
    'array' => ':attribute 必须是一个数组.',
    'before' => ':attribute 必须是:date 之前的日期.',
    'before_or_equal' => ':attribute 必须是等于:date 或之前的日期.',
    'between' => [
        'numeric' => ':attribute 必须介于:min 和 :max.',
        'file' => ':attribute 必须介于:min 和 :max kilobytes .',
        'string' => ':attribute 必须介于:min 和 :max 字符.',
        'array' => ':attribute 必须介于:min 和 :max 项目.',
    ],
    'boolean' => ':attribute 必须为对或错.',
    'confirmed' => ':attribute 确认不符.',
    'date' => ':attribute 不是有效日期.',
    'date_equals' => ':attribute 必须是等于:date 的日期.',
    'date_format' => ':attribute 与 :format 格式不符.',
    'different' => ':attribute 和 :other 必须不同.',
    'digits' => ':attribute 必须:digits 数字.',
    'digits_between' => ':attribute 必须介于:min 和 :max 数字.',
    'dimensions' => ':attribute 的图像尺寸无效.',
    'distinct' => ':attribute 字段有重复.',
    'email' => ':attribute 必须是一个有效的电子邮件地址.',
    'exists' => '所选的 :attribute 无效.',
    'file' => ':attribute 必须是一个文件.',
    'filled' => ':attribute 必须要填上.',
    'gt' => [
        'numeric' => ':attribute 必须大于 :value.',
        'file' => ':attribute 必须大于:value kilobytes.',
        'string' => ':attribute 必须大于 :value 字符.',
        'array' => ':attribute 必须超过 :value 项目.',
    ],
    'gte' => [
        'numeric' => ':attribute 必须大于或等于 :value.',
        'file' => ':attribute 必须大于或等于:value kilobytes.',
        'string' => ':attribute 必须大于或等于:value 字符.',
        'array' => ':attribute 必须是 :value 项目或更多.',
    ],
    'image' => ':attribute 必须是图像.',
    'in' => '所选的:attribute 无效.',
    'in_array' => ':attribute 不存在于:other.',
    'integer' => ':attribute 必须是整数.',
    'ip' => ':attribute 必须是有效的IP地址.',
    'ipv4' => ':attribute 必须是有效的IPv4地址.',
    'ipv6' => ':attribute 必须是有效的IPv6地址.',
    'json' => ':attribute 必须是有效的JSON字符串.',
    'lt' => [
        'numeric' => ':attribute 必须小于 :value.',
        'file' => ':attribute 必须小于:value kilobytes.',
        'string' => ':attribute 必须小于:value 字符.',
        'array' => ':attribute 必须少于:value 项目.',
    ],
    'lte' => [
        'numeric' => ':attribute 必须小于或等于:value.',
        'file' => ':attribute 必须小于或等于:value kilobytes.',
        'string' => ':attribute 必须小于或等于:value 字符.',
        'array' => ':attribute 不得超过:value 项目.',
    ],
    'max' => [
        'numeric' => ':attribute 不得大于:max.',
        'file' => ':attribute 不得大于:max kilobytes.',
        'string' => ':attribute 不得大于:max 字符.',
        'array' => ':attribute 可能不超过:max 项目.',
    ],
    'mimes' => ':attribute 必须是:values 类型的文件.',
    'mimetypes' => ':attribute 必须是:values 类型的文件.',
    'min' => [
        'numeric' => ':attribute 必须至少:min.',
        'file' => ':attribute 必须至少:min kilobytes.',
        'string' => ':attribute 必须至少:min 字符.',
        'array' => ':attribute 必须至少有:min 项目.',
    ],
    'not_in' => '所选的:attribute 无效.',
    'not_regex' => ':attribute 格式无效.',
    'numeric' => ':attribute 必须是数字.',
    'present' => ':attribute 必须要填上.',
    'regex' => ':attribute 格式无效.',
    'required' => ':attribute 必须要填上.',
    'required_if' => '当:other 是:value 时，:attribute 必须要填上.',
    'required_unless' => ':attribute 必须要填上， 除非:other 是在:values.',
    'required_with' => '当:values 存在时，:attribute 必须要填上.',
    'required_with_all' => '当:values 存在时，:attribute 必须要填上.',
    'required_without' => '当:values 不存在时，:attribute 必须要填上.',
    'required_without_all' => '当:values 都不存在时，:attribute 必须要填上.',
    'same' => ':attribute 和 :other 必须相称.',
    'size' => [
        'numeric' => ':attribute 必须:size.',
        'file' => ':attribute 必须:size kilobytes.',
        'string' => ':attribute 必须:size 字符.',
        'array' => ':attribute 必须包含:size 项目.',
    ],
    'starts_with' => ':attribute必须从下列其中一项开头: :values',
    'string' => ':attribute必须是一个字符串.',
    'timezone' => ':attribute 必须是有效区域.',
    'unique' => ':attribute 已经被采用.',
    'uploaded' => ':attribute 上载失败.',
    'url' => ':attribute 格式无效.',
    'uuid' => ':attribute 必须是有效的UUID.',

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
            'rule-name' => '特定信息',
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
		'select_source_of_fund'       => "选择至少一种收入来源",
	],

];
