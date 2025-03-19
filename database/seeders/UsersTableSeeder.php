<?php     

namespace Database\Seeders;

use App\Address;
use App\BankAccount;
use App\BankCard;
use App\City;
use App\Coverage;
use App\Individual;
use App\PostalCode;
use App\State;
use App\Underwriting;
use App\User;
use App\UserPdsReview;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

		// $faker = Factory::create();

		// $user = User::create([
		// 	'type'     => 'individual',
		// 	'email'    => 'sh.bahmanpoor@gmail.com',
		// 	'password' => bcrypt('123456789'),
		// 	'locale'   => 'en',
		// ]);

		// $indiv = Individual::create([
		// 	'user_id'              => $user->id,
		// 	'name'                 => 'Shahriar Bahmanpour',
		// 	'nric'                 => 'A4411441',
		// 	'nationality'          => 'Iranian',
		// 	'dob'                  => '1991-05-08',
		// 	'gender'               => 'male',
		// 	'mobile'               => '0183588857',
		// 	'household_income'     => 8000,
		// 	'personal_income'      => 8000,
		// 	'occ'                  => 1,
		// 	'passport_expiry_date' => '2022-05-08'
		// ]);

		// for ($i = 0; $i < 10; $i++) {
		// 	$user = User::create([
		// 		'type'     => 'individual',
		// 		'email'    => $faker->email,
		// 		'password' => bcrypt('123456789'),
		// 		'locale'   => 'en',

		// 	]);

		// 	$indiv = Individual::create([
		// 		'user_id'              => $user->id,
		// 		'name'                 => $faker->name,
		// 		'nric'                 => 'A4411441',
		// 		'nationality'          => 'Iranian',
		// 		'dob'                  => '1991-05-08',
		// 		'gender'               => 'male',
		// 		'mobile'               => '0183588857',
		// 		'household_income'     => 8000,
		// 		'personal_income'      => 8000,
		// 		'occ'                  => 1,
		// 		'passport_expiry_date' => '2022-05-08'
		// 	]);
		// }

		// // create test user
		// $testUser = User::create([
		// 	'type'     => 'individual',
		// 	'email'    => 'test@deartime.com',
		// 	'password' => bcrypt('123456'),
		// 	'locale'   => 'en',
		// ]);

		// $address = Address::create([
		// 	'address1'  => $faker->address,
		// 	'address2'  => $faker->address,
		// 	'address3'  => $faker->address,
		// 	'state'    => State::where('name','Wp Kuala Lumpur')->first()->uuid,
		// 	'city'     => City::where('state_id',1)->first()->uuid,
		// 	'postcode' => PostalCode::where('city_id',1)->first()->uuid,
		// ]);

		// $individual = Individual::create([
		// 	'user_id'              => $testUser->id,
		// 	'name'                 => 'test user deartime',
		// 	'nric'                 => '870321553311',
		// 	'religion'             => 'muslim',
		// 	'nationality'          => 'Iranian',
		// 	'country_id'           => 135,
		// 	'dob'                  => '1987-03-21',
		// 	'gender'               => 'male',
		// 	'mobile'               => '0183580000',
		// 	'household_income'     => 8000,
		// 	'personal_income'      => 8000,
		// 	'occ'                  => 45,
		// 	'passport_expiry_date' => '2022-05-08',
		// 	'address_id'           => $address->id,
		// 	'type'                 => 'owner'
		// ]);

		// for ($i = 1; $i <= 4; $i++) {
		// 	UserPdsReview::create([
		// 		'individual_id' => $individual->id,
		// 		'product_id'    => $i,
		// 	]);
		// }

		// Underwriting::create([
		// 	'individual_id' => $individual->id,
		// 	'death'         => 1,
		// 	'disability'    => 1,
		// 	'ci'            => 1,
		// 	'medical'       => 1,
		// 	'created_by'    => $individual->id,
		// 	'answers'       => json_decode("eyJpdiI6IlBxOUpScjAwT2l3SFZBWEVaUk8yUkE9PSIsInZhbHVlIjoiRGswUzBSR1FhMk1mQ0lnVk9Ea0lkbmhDYVFrT2orNlBKSnQrUkFQcXJ0UmlJZHZ3OXpha0VJQmFpN0thblNOSGtBcExUZ0J2bGkwQjA4amFTY0pTczIzWWk1Y0d0ODdzaUhvNXo5ZGR3aFFSY2hCTUhURlpOOHFtTUFVaEpaamhSWDlRc0w5VHVOdk54dE5kKzNqZEo5RkRCQ29IMWFCTGtXdmR2SlN3L3FQck9TbnhVa0FLRFNNN253UUpOSUo4IiwibWFjIjoiNDNkOTNhZTIzMjRlODUzNjM3YzNmNGU0OWI5OWY5NTE2MTNhZmMzMTY2YzRjMTEzNDc4MGEyMDEyNzhiNzIxMSJ9"),
		// ]);

		// BankCard::create([
		// 	'owner_id'     => $individual->id,
		// 	'owner_type'   => 'App\Individual',
		// 	'token'        => '2d4fd93ee68aa666140502abbf4d13fe',
		// 	'saved_date'   => '2021-05-12 15:16:24',
		// 	'scheme'       => 'MASTERCARD',
		// 	'masked_pan'   => '520473XXXXXX1003',
		// 	'holder_name'  => 'test user deartime',
		// 	'expiry_month' => 12,
		// 	'expiry_year'  => 2050,
		// 	'code'         => 'success',
		// 	'message'      => 'success',
		// 	'auto_debit'   => 1,
		// ]);

		// BankAccount::create([
		// 	'owner_id'   => $individual->id,
		// 	'owner_type' => 'App\Individual',
		// 	'bank_name'  => 'bank1',
		// 	'account_no' => '123456000',
		// 	'deleted_at' => Carbon::now()->subMonth(6)
		// ]);

		// BankAccount::create([
		// 	'owner_id'   => $individual->id,
		// 	'owner_type' => 'App\Individual',
		// 	'bank_name'  => 'bank2',
		// 	'account_no' => '123456789',
		// ]);

		// death
		/*$coverage = Coverage::create([
			'owner_id'         => $individual->id,
			'payer_id'         => $individual->id,
			'covered_id'       => $individual->id,
			'product_id'       => 1,
			'product_name'     => 'Death',
			'state'            => 'active',
			'status'           => 'active',
			'payment_term'     => 'monthly',
			'coverage'         => '252000',
			'deductible'       => 0,
			'max_coverage'     => 0,
			'payment_monthly'  => 16.01,
			'payment_annually' => 191.52,
			'has_loading'      => 1,
			'uw_id'            => 1,
			'first_payment_on' => '2021-03-01 08:22:11',
			'next_payment_on'  => '2021-04-01 08:22:11',
			'last_payment_on'  => '2021-04-01 08:22:11',
		]);*/

		// thanksgiving

		// self = 10
		/*$t1 = Thanksgiving::create([
			'individual_id' => $individual->id,
			'type' => Enum::THANKSGIVING_TYPE_SELF,
			'percentage' => 10,
		]);

		Credit::create([
			'user_id'   =>  $individual->id,
			'from_id'   =>  $individual->id,
			'amount'    =>  10,
			'type'      =>  Enum::CREDIT_TYPE_THANKS_GIVING,
			'type_item_id'   =>  $t1->id,
		]);

		Credit::create([
			'user_id'   =>  $individual->id,
			'from_id'   =>  $individual->id,
			'amount'    =>  -10,
			'type'      =>  Enum::CREDIT_TYPE_THANKS_GIVING,
			'type_item_id'   =>  $t1->id,
		]);

		// promoter = 10
		$t2 = Thanksgiving::create([
			'individual_id' => $individual->id,
			'type' => Enum::THANKSGIVING_TYPE_PROMOTER,
			'percentage' => 30,
		]);

		Credit::create([
			'user_id'   =>  1,
			'from_id'   =>  $individual->id,
			'amount'    =>  30,
			'type'      =>  Enum::CREDIT_TYPE_THANKS_GIVING,
			'type_item_id'   =>  $t2->id,
		]);

		// charity = 10
		$t3 = Thanksgiving::create([
			'individual_id' => $individual->id,
			'type' => Enum::THANKSGIVING_TYPE_CHARITY,
			'percentage' => 40,
		]);

		Credit::create([
			'from_id'   =>  $individual->id,
			'amount'    =>  10,
			'type'      =>  Enum::CREDIT_TYPE_THANKS_GIVING,
			'type_item_id'   =>  $t3->id,
		]);

		$coverage->thanksgivings()->attach([$t1->id, $t2->id, $t3->id]);

		$coverage = Coverage::create([
			'owner_id'  => $individual->id,
			'payer_id'  => $individual->id,
			'covered_id'  => $individual->id,
			'product_id'  => 1,
			'product_name'  => 'Death',
			'state'  => 'active',
			'status'  => 'active',
			'payment_term'  => 'monthly',
			'coverage'  => '252000',
			'deductible' => 0,
			'max_coverage' => 0,
			'payment_monthly' => 17.02,
			'payment_annually' => 191.52,
			'has_loading' => 1,
			'uw_id' => 1,
			'first_payment_on' => '2021-04-01 08:22:11',
			'next_payment_on'  => '2021-05-01 08:22:11',
			'last_payment_on'  => '2021-05-01 08:22:11',
		]);

		$coverage = Coverage::create([
			'owner_id'  => $individual->id,
			'payer_id'  => $individual->id,
			'covered_id'  => $individual->id,
			'product_id'  => 1,
			'product_name'  => 'Death',
			'state'  => 'active',
			'status'  => 'active',
			'payment_term'  => 'monthly',
			'coverage'  => '252000',
			'deductible' => 0,
			'max_coverage' => 0,
			'payment_monthly' => 18.03,
			'payment_annually' => 191.52,
			'has_loading' => 1,
			'uw_id' => 1,
			'first_payment_on' => '2021-05-01 08:22:11',
			'next_payment_on'  => '2021-06-01 08:22:11',
			'last_payment_on'  => '2021-05-01 08:22:11',
		]);*/

		// Disability
		/*Coverage::create([
			'owner_id'         => $individual->id,
			'payer_id'         => $individual->id,
			'covered_id'       => $individual->id,
			'product_id'       => 2,
			'product_name'     => 'Disability',
			'state'            => 'active',
			'status'           => 'active',
			'payment_term'     => 'monthly',
			'coverage'         => '173000',
			'deductible'       => 0,
			'max_coverage'     => 0,
			'payment_monthly'  => 2.14,
			'payment_annually' => 25.18,
			'has_loading'      => 1,
			'uw_id'            => 1,
			'first_payment_on' => '2021-05-13 08:22:11',
			'next_payment_on'  => '2022-05-13 08:22:11',
			'last_payment_on'  => '2021-05-13 08:22:11',
		]);

		// Accident
		Coverage::create([
			'owner_id'         => $individual->id,
			'payer_id'         => $individual->id,
			'covered_id'       => $individual->id,
			'product_id'       => 3,
			'product_name'     => 'Accident',
			'state'            => 'active',
			'status'           => 'active',
			'payment_term'     => 'monthly',
			'coverage'         => '155000',
			'deductible'       => 0,
			'max_coverage'     => 0,
			'payment_monthly'  => 9.37,
			'payment_annually' => 110.28,
			'has_loading'      => 1,
			'uw_id'            => 1,
			'first_payment_on' => '2021-05-13 08:22:11',
			'next_payment_on'  => '2022-05-13 08:22:11',
			'last_payment_on'  => '2021-05-13 08:22:11',
		]);

		// Critical Illness
		Coverage::create([
			'owner_id'         => $individual->id,
			'payer_id'         => $individual->id,
			'covered_id'       => $individual->id,
			'product_id'       => 4,
			'product_name'     => 'Critical Illness',
			'state'            => 'active',
			'status'           => 'active',
			'payment_term'     => 'monthly',
			'coverage'         => '177000',
			'deductible'       => 0,
			'max_coverage'     => 0,
			'payment_monthly'  => 20.44,
			'payment_annually' => 240.42,
			'has_loading'      => 1,
			'uw_id'            => 1,
			'first_payment_on' => '2021-05-13 08:22:11',
			'next_payment_on'  => '2022-05-13 08:22:11',
			'last_payment_on'  => '2021-05-13 08:22:11',
		]);

		// Medical
		Coverage::create([
			'owner_id'         => $individual->id,
			'payer_id'         => $individual->id,
			'covered_id'       => $individual->id,
			'product_id'       => 5,
			'product_name'     => 'Medical',
			'state'            => 'active',
			'status'           => 'active',
			'payment_term'     => 'monthly',
			'coverage'         => '2',
			'deductible'       => 1000,
			'max_coverage'     => 0,
			'payment_monthly'  => 50.35,
			'payment_annually' => 592.34,
			'has_loading'      => 1,
			'uw_id'            => 1,
			'first_payment_on' => '2021-05-13 08:22:11',
			'next_payment_on'  => '2022-05-13 08:22:11',
			'last_payment_on'  => '2021-05-13 08:22:11',
		]);*/


	}
}
