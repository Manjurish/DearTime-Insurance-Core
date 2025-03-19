<?php     

return [

    'no_coverage' => "你没有任何保障",
    'profile_updated' => '个人资料数据更新成功！',
    'upload_max_error' => '上传的文件太大',
    'package_edit_successful' => '配套编辑成功',
    'member_edit_successful' => '成员编辑成功',
    'member_add_successful' => '会员添加成功',
    'member_deleted_successful' => '成员删除成功',
    'package_deleted_successful' => '配套删除成功',
    'documents_uploaded' => '已上传文件以供验证！',
    'account_disabled' => '您的个人资料已被禁用。请联系管理员',
    'app_outdated' => '此APP已过时，请更新至最新版本。',
    'claim_submitted' => '索赔申请已提交！',
    'claim_deleted' => '索赔已删除',
    'claim_not_exists' => '索赔不存在',
    'bank_added' => '银行卡添加成功！',
    'bank_account_added' => '银行账户添加成功！',
    'bank_removed' => '银行卡删除成功',
    'bank_account_removed' => '银行账户删除成功',
    'other_coverages_edit_error' => '您不得编辑任何其他保障！',
    'coverage_removed' => '保障删除！',
    'charity_household_error' => '用户无法申请赞助保险！家庭收入超过 RM3169。',
    'charity_applied' => '您已成功申请赞助保险！',
    'code_verified' => '代码验证',
    'mobile_verification_required' => '需要手机号码验证。',
    'email_verification_required' => '需要电子邮件验证。',
    'register_successful' => '注册成功',
    'unauthorized' => '未经授权',
    'accountlocked' => 'Your Account is locked, pls try reset password option',
    'login_success' => '登陆成功',
    'logged_out' => '成功登出',
    'invalid_code' => '无效的激活码',
    'already_active' => '您的帐户已激活',
    'activated' => '您的帐户已成功激活。',
    'password_changed' => '密码已更改',
    'old_password_incorrect' => '旧密码不正确。',
    'new_password_confirmation' => '新密码与确认不匹配。',
    'unique_email' => '电子邮件已被注册。',
    'unique_mobile' => '手机号码已被注册。',
    'oauth_failed' => "使用 :provider 登录失败。请手动登录/注册",
    'child_above_16' => "您的孩子已满 16 岁。请让您的孩子在 DearTime 注册自己的个人帐户",
    'child_register' => "购买前请先与家长注册",
    'child_register_email' => "电子邮箱和大马卡/护照不匹配。请不要输入16岁以下的投保人的电子邮箱和手机号码",
    'share_text' =>"我邀请您加入 DearTime: :link",
    'nominee_text' => ":name 已将您选为提名人！您可以在索赔部分查看更多信息",
	'nominee_text_notif' => ":name 选择您作为提名人！ 您可以在通知部分查看更多信息",
	'nominee_text_notification' => ":name 已在 DearTime 购买了人寿保险，并已提名您为受益人。",
	'nominee_text_notification_email' => ":nominee, 您好。<br/> <b style='color: #000000'> :nominator </b>已在 DearTime 购买了人寿保险，并已提名您为受益人。 这封电子邮件是给您的通知，您不需要采取进一步行动。<br/> <a>谢谢您。</a> <br/>",
    'nominee_title' =>'提名人',
    'nominee_add_email' =>  ":name 选择您作为提名人！ 您可以在通知部分查看更多信息",

    // added on 23/4/2021
    'year' =>"年",
    'generate' =>"产生",
    'date_from' =>"日期从",
    'date_to' =>"日期到",
    'no_report' =>"此日期没有报告",
    'please_select_item' =>"请选择",
    'no_data_exist_for_report' => "报告不存在任何数据",
    'change' => "改变",
    'add_action' => "添加操作",
    'upload' => "上传",
    'succecfully_changed' => "成功改变",
    'succecfully_added' => "成功添加",
    'succecfully_recalculated' =>"成功地重新计算",
    'recalculate' =>"重新计算",
    'execute' => "执行",
    'column_name' => '列名',
    'name' => '名称',
    'group' => '团体',
    'old_value' => '旧值',
    'new_value' => '新价值',
    'created_by' => '由...制作',
    'created_at' => '创建于',
    'updated_at' => '更新于',
    'execute_on' => '执行于',
    'required_unless_passport_expiry_date' => '除非国籍是马来西亚，  否则 :attribute 字段是必需的',
    'required_field' => '此字段是必需的。',
    'before_after_dob' => '日期必须在当前日期之前或之后',
    'admin_area' => '行政区域',
    'customer' => '顾客',
    'customer_details' => '顾客信息',
    'particular_change' => '特殊变化',
    'change_payment_term' => '更改付款期限',
    'updating' => '正在更新...',
    'address' => '地址',
    'with_action' => '有行动',
    'without_action' => '没有行动',
    'email' => '电子邮件',
    'user' => '用户',
    'type' => '类型',
    'event' => '事件',
    'actions' => '行动',
    'status' => '地位',
    'operation' => '手术',
    'credit_list' => '信用清单',
    'refund_list' => '退款清单',
    'order_ref'=>'订单参考号',
    'amount'=>'数量',
    'credit'=>[
    'ref_no'=>'参考编号',
    'from'=>'从',
    'to'=>'至',
    'date'=>'日期',
    ],
    'no_action_added' => '未添加任何操作',
    'should_be_different' => ':attribute 应该与当前的 :attribute 不同',
    'refund' => [
    'ref_no'=>'参考编号',
    'payer'=>'付款人',
    'receiver'=>'接收者',
    'status'=>'地位',
    'amount'=>'数量',
    'authorized_by'=>'被授权于',
    'created_at'=>'创建于',
    'authorized_at'=>'授权于',
    'effective_date'=>'生效日期',
	'pay_ref_no' => '支付参考号码'
    ],
	'claim_data_import' => '索赔数据导入',
	'claim_type' => '索赔类型',
	'new_claim' => '新索赔',
	'claim_no' => '索赔编号',
	'policy_no' => '保单编号',
	'id_no' => '编号',
	'date_of_visit' => '访问日期',
	'date_of_discharge' => '出院日期',
	'diagnosis_code_1' => '诊断代码1',
	'diagnosis_code_2' => '诊断代码 2',
	'diagnosis_code_3' => '诊断代码3',
	'provider_code' => '供应商代码',
	'provider_name' => '供应商姓名',
	'provider_invoice_no' => '供应商发票编号',
	'date_claim_received' => '收到索赔的日期',
	'medical_leave_from' => '病假从',
	'medical_leave_to' => '病假到',
	'tpa_invoice_no' => 'tpa 发票号',
	'cliam_type' => '索赔 类型',
	'actual_invoice_amount' => '实际发票金额',
	'approved_amount' => '批准金额',
	'non_approved_amount' => '不批准金额',
	'import' => '导入',
	'check' => '检查',
	'nric' => '身份证' ,
	'mobile' => '移动' ,
	'list_active_policies' => '活跃保单列表' ,
	'claim_take_by_another_hospital' => '此索赔由另一家医院接受' ,
	'existing_claim' => '现有索赔' ,
	'unable_subscribe_heavy_smoker' => '抱歉，如果您是重度吸烟者，则无法订阅 DearTime。' ,
	'total_amount_zero'=> '您的总价为 0，您可以从保单页面购买保险',
	'denied_login_individual' => "您不允许在网络上登录。请下载应用程序",

    // added on 23/6/2021
    'title'=>"标题",
    'createdAt'=>"创建",
    'seen'=>'看过',
    'yes'=>"是",
    'no'=>'否',
    'detail'=>"详细信息",

	'i'=>'我',
	'registered_at' => '注册于',
	'selfie_match' => '自拍匹配',

	// added on 2021-07-01"
	'payment_at' => '付款时间',

	// added on 2021-07-02"
	'your_face_doesnt_match_mykad' => "您的面部扫描与您的大马卡/护照不符",

	// added on 2021-07-13
	'action' => '动作',

	// added on 2021-07-15
	'last_action' => "最后一个动作",

	// added on 2021-07-16
	'position' => "位置",
	'active'   => "活跃",
	'disable'  => "禁用",
	'owner'    => "持有者",
	'transactions_ref'    => "交易参考",
	'transactions_id'    => "交易编号",
	'card_type'    => "卡的种类",
	'card_no'    => "卡号",

    // edited on 2021-07-20
    'your_mykad_selfie_dosent_match' => '您的面部扫描与您的大马卡/护照不符',
    
      // added on 2022-10-06

  'order_email_title'                            => ' 你受保了',
  'order_email_subject'                          => ' 你受保了',
  'order_email_subject_payor'                    => '保险范围现已生效',


   // added on 2023-01-30

  'ben_email_title'                              => ' 受益人',
  'ben_email_subject'                            => ' 受益人',

  'failed_spo_application'=>"嗨 :name，我们很遗憾地通知您，您申请赞助保险未成功。您可以重新申请赞助保险或购买 DearTime 的保险，最低 RM0.10。",
  'verified_spo_application'=>":name, 您好，您的赞助保险申请已成功。您现在在等待获得赞助保险全额赞助的名单上。一旦您的保险生效，您将收到通知。请点击赞助保险，以查看您的赞助保险状态。",

    // added on 2023-09-08
    
  'payment_term_month_title' =>'成功更新付款方式',
  'payment_term_month_subject' =>'成功更新付款方式',
  
  'payment_term_annual_title' =>'更新支付方式',
  'payment_term_annual_subject' =>'更新支付方式',

  'payment_term_month_text_new' => "嗨 :owner,<br><br> :owner 您已成功将付款方式更改为每月。它将按以下方式生效：<br>
                                    <table width='500' border='5' cellspacing='0' cellpadding='8'>
                                    <tr><th>覆盖范围</th>
                                    <th>新模式生效日期</th>
                                    <th>新保费金额 (RM)</th>
                                    </tr>
                                    :rows
                                    </table>
                                    <br><br>
                                    在新模式生效之日，将生成新合约。
                                    <br><br>
                                    问候,<br>
                                    Deartime",

   'payment_term_annual_text_new' => "嗨 :owner,<br><br> :owner 已成功更改为年费模式，生效日期如下：<br>
                                   <table width='500' border='5' cellspacing='0' cellpadding='8'>
                                   <tr><th>覆盖范围</th>
                                   <th>新模式生效日期</th>
                                    <th>新保费金额 (RM)</th>
                                    </tr>
                                    :rows
                                    </table>
                                    <br><br>
                                    在新模式生效之日，将生成新合约。
                                    <br><br>
                                    问候,<br>
                                    Deartime",
                                    
     // 2-May-2024
     
     'payment_term_month_title_owner' =>'成功更新付款方式',
    'payment_term_month_subject_owner' =>'成功更新付款方式',
    
    'payment_term_annual_title_owner' =>'更新支付方式',
    'payment_term_annual_subject_owner' =>'更新支付方式',

    'payment_term_month_text_new_owner' => "嗨 :owner <br><br> :payor 您已成功将付款方式更改为每月。它将按以下方式生效： <br>
                                    <table width='500' border='5' cellspacing='0' cellpadding='8'>
                                    <tr><th>覆盖范围</th>
                                    <th>新模式生效日期</th>
                                    <th>新保费金额 (RM)</th>
                                    </tr>
                                    :rows
                                    </table>
                                    <br><br>
                                    在新模式生效之日，将生成新合约。
                                    <br><br>
                                    问候,<br>
                                    Deartime",

     'payment_term_annual_text_new_owner' => "嗨 :owner <br><br> :payor 已成功更改为年费模式，生效日期如下：<br>
                                    <table width='500' border='5' cellspacing='0' cellpadding='8'>
                                    <tr><th>覆盖范围</th>
                                    <th>新模式生效日期</th>
                                    <th>新保费金额 (RM)</th>
                                    </tr>
                                    :rows
                                    </table>
                                    <br><br>
                                    在新模式生效之日，将生成新合约。
                                    <br><br>
                                    问候,<br>
                                    Deartime",
    
    'payment_term_month_title_payor' =>'成功更新付款方式',
    'payment_term_month_subject_payor' =>'成功更新付款方式',
    
    'payment_term_annual_title_payor' =>'更新支付方式',
    'payment_term_annual_subject_payor' =>'更新支付方式',

    'payment_term_month_text_new_payor' => "嗨 :payor <br><br> 您已成功将 :owner 付款方式更改为每月。它将按以下方式生效：<br>
                                    <table width='500' border='5' cellspacing='0' cellpadding='8'>
                                    <tr><th>覆盖范围</th>
                                    <th>新模式生效日期</th>
                                    <th>新保费金额 (RM)</th>
                                    </tr>
                                    :rows
                                    </table>
                                    <br><br>
                                    问候,<br>
                                    Deartime",

     'payment_term_annual_text_new_payor' => "嗨 :payor <br><br> 您已成功将 :owner 付款模式更改为每年。它将按以下方式生效：<br>
                                    <table width='500' border='5' cellspacing='0' cellpadding='8'>
                                    <tr><th>覆盖范围</th>
                                    <th>新模式生效日期</th>
                                    <th>新保费金额 (RM)</th>
                                    </tr>
                                    :rows
                                    </table>
                                    <br><br>
                                    问候,<br>
                                    Deartime",

];
