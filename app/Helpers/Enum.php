<?php     

namespace App\Helpers;

class Enum
{
	// Product
	const PRODUCT_NAME_DEATH            = 'Death';
	const PRODUCT_NAME_DISABILITY       = 'Disability';
	const PRODUCT_NAME_ACCIDENT         = 'Accident';
	const PRODUCT_NAME_CRITICAL_ILLNESS = 'Critical Illness';
	const PRODUCT_NAME_MEDICAL          = 'Medical';

	// Coverage
	const COVERAGE_STATUS_ACTIVE         = 'active';
	const COVERAGE_STATUS_UNPAID         = 'unpaid';
	const COVERAGE_STATUS_CANCELLED      = 'cancelled';
	const COVERAGE_STATUS_PENDING        = 'pending';
	const COVERAGE_STATUS_DECREASED      = 'decreased';
	const COVERAGE_STATUS_EXPIRED        = 'expired';
	const COVERAGE_STATUS_FULFILLED      = 'fulfilled';
	const COVERAGE_PAYMENT_TERM_MONTHLY  = 'monthly';
	const COVERAGE_PAYMENT_TERM_ANNUALLY = 'annually';
	const COVERAGE_STATE_ACTIVE          = 'active';
	const COVERAGE_STATE_INACTIVE        = 'inactive';
	const COVERAGE_STATE_DEACTIVATE        = 'deactivate';
	const COVERAGE_OWNER_TYPE_MYSELF     = 'myself';
	const COVERAGE_OWNER_TYPE_OTHERS     = 'others';
	const COVERAGE_OWNER_TYPE_CHILD      = 'child';

    const COVERAGE_STATUS_GRACE_UNPAID          = 'grace-unpaid';
    const COVERAGE_STATUS_ACTIVE_GRACE          = 'active-grace';
    const COVERAGE_STATUS_GRACE_TERMINATE          = 'grace-terminate';
    const COVERAGE_STATUS_FULFILLED_GRACE          = 'fulfilled-grace';

    const COVERAGE_STATUS_GRACE_INCREASE_UNPAID          = 'grace-increase-unpaid';

	const COVERAGE_STATUS_ACTIVE_INCREASED        = 'active-increased';
	const COVERAGE_STATUS_INCREASE_UNPAID  = 'increase-unpaid';
	const COVERAGE_STATUS_INCREASE_TERMINATE  = 'increase-terminate';
	const COVERAGE_STATUS_FULFILLED_INCREASE  = 'fulfilled-increased';

	const COVERAGE_STATUS_DECREASE_UNPAID        = 'decrease-unpaid';
	const COVERAGE_STATUS_DECREASE_TERMINATE        = 'decrease-terminate';
    const COVERAGE_STATUS_ACTIVE_DECREASED        = 'active-decreased';
    const COVERAGE_STATUS_FULFILLED_DECREASED        = 'fulfilled-decreased';

	const COVERAGE_STATUS_PAYMENT_TERMINATE        = 'payment-terminate';
	const COVERAGE_STATUS_TERMINATE          = 'terminate';
	const COVERAGE_STATUS_DEACTIVATE         = 'deactivating';
	const COVERAGE_STATUS_FULFILLED_DEACTIVATE  = 'deactivated';
	const COVERAGE_STATUS_GRACE_DEACTIVATE ='grace-deactivated';
	
	const COVERAGE_STATUS_ADDPREM_UNPAID = 'addprem-unpaid';
	const COVERAGE_STATUS_ADDPREM_INCREASE_UNPAID = 'addprem-increase-unpaid';
	// Underwriting
	const UNDERWRITING_ACCEPTED = 'accepted';
	const UNDERWRITING_REJECTED = 'rejected';

	// coverage moderation
	const COVERAGE_MODERATION_ACTION_ALLOW_INCREASE    = 'allow-increase';
	const COVERAGE_MODERATION_ACTION_DISALLOW_INCREASE = 'disallow-increase';
	const COVERAGE_MODERATION_ACTION_ALLOW_PURCHASE    = 'allow-purchase';
	const COVERAGE_MODERATION_ACTION_DISALLOW_PURCHASE = 'disallow-purchase';
	const COVERAGE_MODERATION_ACTION_NO                = 'NO ACTION';
	const COVERAGE_MODERATION_STATE_NOT_APPLICABLE     = 'N/A';

	// address
	const ADDRESS_TYPE_RESIDENTIAL = 'residential';

	// actions
	const ACTION_STATUS_PENDING_REVIEW               = 'pending-review';
	const ACTION_STATUS_PENDING_PAYMENT              = 'pending-payment';
	const ACTION_STATUS_PENDING_NEXT_DUE_DATE        = 'pending-next-due-date';
	const ACTION_STATUS_EXECUTED                     = 'executed';
	const ACTION_STATUS_REJECTED                     = 'rejected';
	const ACTION_STATUS_CANCEL                       = 'request Cancelled';
	const ACTION_TYPE_PARTICULAR_CHANGE              = 'Particular Change';
	const ACTION_TYPE_CANCELL_COVERAGE               = 'Cancell Coverage';
	const ACTION_TYPE_BANK_INFO                      = 'Bank Info';
	const ACTION_TYPE_PLAN_CHANGE                    = 'Plan Change';
	const ACTION_TYPE_MEMEBR_ADDITION                = 'Member Addition';
	const ACTION_TYPE_PROMOTER_REFUND                = 'Promoter Refund';
	const ACTION_TYPE_AMENDMENT                      = 'Amendment';
	const ACTION_TYPE_TERMINATE                      = 'Terminate';
	const ACTION_EVENT_CHANGE_NAME                   = 'changeName';
	const ACTION_EVENT_CHANGE_EMAIL                  = 'changeEmail';
	const ACTION_EVENT_ADD_BANK_CARD                 = 'addBankCard';
	const ACTION_EVENT_DELETE_BANK_CARD              = 'deleteBankCard';
	const ACTION_EVENT_ADD_BANK_ACCOUNT              = 'addBankAccount';
	const ACTION_EVENT_CHANGE_MOBILE                 = 'changeMobile';
	const ACTION_EVENT_CHANGE_NATIONALITY            = 'changeNationality';
	const ACTION_EVENT_CHANGE_ADDRESS                = 'changeAddress';
	const ACTION_EVENT_CHANGE_DOB                    = 'changeDob';
	const ACTION_EVENT_CHANGE_GENDER                 = 'changeGender';
	const ACTION_EVENT_CHANGE_OCCUPATION             = 'changeOccupation';
	const ACTION_EVENT_CHANGE_PAYMENT_TERM           = 'changePaymentTerm';
	const ACTION_EVENT_CANCELL_COVERAGE              = 'cancellCoverage';
	const ACTION_EVENT_NEW_MEMBER                    = 'newMember';
	const ACTION_EVENT_TERMINATE                     = 'terminate';
	const ACTION_EVENT_DEACTIVATE                    = 'deactivate';
	const ACTION_EVENT_PLAN_CHANGE                   = 'PlanChange';
	const ACTION_EVENT_REJECTED                      = 'rejected';
	const ACTION_EVENT_CREATE_REFUND                 = 'createRefund';
	const ACTION_METHOD_CHANGE_NAME                  = 'changeName';
	const ACTION_METHOD_CHANGE_NATIONALITY           = 'changeNationality';
	const ACTION_METHOD_CHANGE_ADDRESS               = 'changeAddress';
	const ACTION_METHOD_CHANGE_DOB                   = 'changeDob';
	const ACTION_METHOD_CHANGE_GENDER                = 'changeGender';
	const ACTION_METHOD_CHANGE_OCCUPATION            = 'changeOccupation';
	const ACTION_METHOD_CHANGE_PAYMENT_TERM          = 'changePaymentTerm';
	const ACTION_METHOD_FULL_REFUND                  = 'fullRefund';
	const ACTION_METHOD_PARTIAL_REFUND               = 'partialRefund';
	const ACTION_METHOD_REDUCE_COVERAGE              = 'reduceCoverage';
	const ACTION_METHOD_RENEW_COVERAGE               = 'renewCoverage';
	const ACTION_METHOD_ADDITIONAL_PREMIUM           = 'additionalPremium';
	const ACTION_METHOD_CHANGE_PAYMENT_TERM_COVERAGE = 'changePaymentTermCoverage';
	const ACTION_METHOD_TERMINATE                    = 'terminate';
	const ACTION_METHOD_DEACTIVATE                   = 'deactivate';
	const ACTION_TABLE_TYPE_BASIC_INFO               = 'basic-info';
	const ACTION_TABLE_TYPE_PAYMENT_TERM             = 'payment-term';
	const ACTION_TABLE_TYPE_CANCELL_COVERAGE         = 'cancell-coverage';
	const ACTION_TYPE_DEACTIVATE                   = 'deactivate coverage';
	const ACTION_METHOD_REFUND                     ='refund';

	// Order
	const ORDER_SUCCESSFUL   = 'successful';
	const ORDER_UNSUCCESSFUL = 'unsuccessful';
	const ORDER_PENDING      = 'pending';
	const ORDER_TYPE_NEW     = 'new';
	const ORDER_TYPE_RENEW   = 'renew';

	// Screening
	const SCREENING_STATUS_APPROVE = 'approve';
	const SCREENING_STATUS_REJECT  = 'reject';
	const SCREENING_STATUS_PENDING = 'pending';

	// modal
	const MODAL_TYPE_SUCCESS = 'success';
	const MODAL_ICON_SUCCESS = 'success';
	const MODAL_ICON_ERROR   = 'error';
	// Thanksgiving
	const THANKSGIVING_TYPE_SELF     = 'self';
	const THANKSGIVING_TYPE_CHARITY  = 'charity';
	const THANKSGIVING_TYPE_PROMOTER = 'promoter';

	// Credit
	const CREDIT_TYPE_THANKS_GIVING = 'App\Thanksgiving';
	const CREDIT_TYPE_ACTION = 'App\Action';

	// Card
	const CARD_AUTO_DEBIT_ACTIVE   = '1';
	const CARD_AUTO_DEBIT_DEACTIVE = '0';

	// individual
	const INDIVIDUAL_GENDER_MALE   = 'male';
	const INDIVIDUAL_GENDER_FEMALE = 'female';

	// refund
	const REFUND_PAYER_DEARTIME   = 'deartime';
	const REFUND_PAYER_CHARITY    = 'charity_fund';
	const REFUND_STATUS_PENDING   = 'pending';
	const REFUND_STATUS_APPROVE   = 'approve';
	const REFUND_STATUS_REJECT    = 'reject';
	const REFUND_STATUS_COMPLETED = 'completed';

	// page types
	const PAGE_ACTION_TYPE_MODAL           = 'modal';
	const PAGE_ACTION_TYPE_NEXT_PAGE       = 'nextPage';
	const PAGE_ACTION_TYPE_NEXT_PAGE_MODAL = 'nextPage_modal';
	const PAGE_ACTION_TYPE_TOAST           = 'toast';
	const PAGE_ACTION_TYPE_NEXT_PAGE_TOAST = 'nextPage_toast';

	// particular changes
	const PARTICULAR_CHANGE_COLUMN_NAME_NAME                 = 'name';
	const PARTICULAR_CHANGE_COLUMN_NAME_COUNTRY              = 'country';
	const PARTICULAR_CHANGE_COLUMN_NAME_NRIC                 = 'nric';
	const PARTICULAR_CHANGE_COLUMN_NAME_PASSPORT_EXPIRY_DATE = 'passport expiry date';
	const PARTICULAR_CHANGE_COLUMN_NAME_ADDRESS              = 'address';
	const PARTICULAR_CHANGE_COLUMN_NAME_STATE                = 'state';
	const PARTICULAR_CHANGE_COLUMN_NAME_CITY                 = 'city';
	const PARTICULAR_CHANGE_COLUMN_NAME_POSTCODE             = 'postcode';
	const PARTICULAR_CHANGE_COLUMN_NAME_DOB                  = 'dob';
	const PARTICULAR_CHANGE_COLUMN_NAME_GENDER               = 'gender';
	const PARTICULAR_CHANGE_COLUMN_NAME_INDUSTRY             = 'industry';
	const PARTICULAR_CHANGE_COLUMN_NAME_JOB                  = 'job';

	// Claim
	const CLAIM_STATUS_DRAFT = 'draft';
	const CLAIM_STATUS_PENDING_FOR_OS_DOCUMENT = 'pending for os document';

	// users
	const USER_TYPE_INDIVIDUAL = 'individual';
	const USER_TYPE_CORPORATE = 'corporate';

	// beneficiary
    const BENEFICIARY_TYPE_HIBAH = 'hibah';
    const BENEFICIARY_TYPE_TRUSTEE = 'trustee';

    const BENEFICIARY_STATUS_PENDING = 'pending';
    const BENEFICIARY_STATUS_APPROVE = 'approve';
    const BENEFICIARY_STATUS_DECLINED = 'declined';
    const BENEFICIARY_STATUS_SENT_EMAIL = 'sent-email';

	// transaction
	const TRANSACTION_STATUS_SUCCESSFUL = 'successful';
	const TRANSACTION_STATUS_UNSUCCESSFUL = 'unsuccessful';


}
