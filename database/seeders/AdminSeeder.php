<?php     

namespace Database\Seeders;

use App\InternalUser;
use Illuminate\Database\Seeder;
use App\Role;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        InternalUser::truncate();

        $user = new \App\InternalUser();
        $user->name = 'SuperAdmin';
        $user->email = 'SuperAdmin@deartime.com';
        $user->active = '1';
        $user->position = 'SuperAdmin';
        $user->password = bcrypt('abcde1234');
        $user->save();
        $role = Role::findOrCreate('SuperAdmin','internal_users');
        $user->syncRoles(['SuperAdmin']);


		$user = new \App\InternalUser();
		$user->name = 'pwc';
		$user->email = 'pwc@deartime.com';
		$user->active = '1';
		$user->position = 'SuperAdmin';
		$user->password = bcrypt('abcde1234');
		$user->save();
		$user->syncRoles(['SuperAdmin']);

		$user = new \App\InternalUser();
		$user->name = 'lgms';
		$user->email = 'lgms@deartime.com';
		$user->active = '1';
		$user->position = 'SuperAdmin';
		$user->password = bcrypt('abcde1234');
		$user->save();
		$user->syncRoles(['SuperAdmin']);
        
        $user = new \App\InternalUser();
		$user->name = 'pwc1';
		$user->email = 'pwc1@deartime.com';
		$user->active = '1';
		$user->position = 'SuperAdmin';
		$user->password = bcrypt('abcd1234');
		$user->save();
		$user->syncRoles(['SuperAdmin']);


    }
}
