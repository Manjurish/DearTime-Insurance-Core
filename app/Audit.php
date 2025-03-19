<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends \OwenIt\Auditing\Models\Audit
{
    use HasFactory;
    protected $appends = ['clearValues'];

    public function getClearValuesAttribute(){
        $clearNewValues = [];
        $clearOldValues = [];
        foreach ($this->new_values as $key=>$value){
            switch ($key){
                case 'country_id':
                    $clearNewValues[$key] = Country::whereId($value)->first()->country ?? '';
                    $clearOldValues[$key] = Country::whereId($this->old_values[$key])->first()->country ?? '';
                    break;
                case 'occ':
                    $clearNewValues[$key] = IndustryJob::findOrFail($value)->name ?? '';
                    $clearOldValues[$key] = IndustryJob::whereId($this->old_values[$key])->first()->name ?? '';
                    break;
                case 'address_id':
                    $address = Address::whereId($value)->withTrashed()->first();
                    $clearNewValues['address_id'] = '';
                    if(!empty($address)){
                        $clearNewValues['address'] = $address->address1.'-'.$address->address2.'-'.$address->address3;
                        $clearNewValues['state'] = State::whereUuid($address->state)->first()->name ?? '';
                        $clearNewValues['city'] = City::whereUuid($address->city)->first()->name ?? '';
                        $clearNewValues['postcode'] = PostalCode::whereUuid($address->postcode)->first()->name ?? '';
                        $clearNewValues['address_id'] =$clearNewValues['state'].'/'.$clearNewValues['city'].'/'.$clearNewValues['postcode']
                            .'/'.$clearNewValues['address'] ?? '';
                    }

                    $address = Address::whereId($this->old_values[$key])->withTrashed()->first();
                    $clearOldValues['address_id'] = '';
                    if(!empty($address)){
                        $clearOldValues['state'] = State::whereUuid($address->state ?? '')->first()->name ?? '';
                        $clearOldValues['city'] = City::whereUuid($address->city ?? '')->first()->name ?? '';
                        $clearOldValues['postcode'] = PostalCode::whereUuid($address->postcode ?? '')->first()->name ?? '';

                        $clearOldValues['address'] = $address->address1 .'-'.$address->address2 .'-'.$address->address3;
                        $clearOldValues['address_id']=$clearOldValues['state'].'/'.$clearOldValues['city'].'/'.$clearOldValues['postcode']
                            .'/'.$clearOldValues['address'];
                    }


                    break;
                case 'has_other_life_insurance':
                    $clearNewValues[$key] = $value === false? 'false': ($value===true?'true':$value);
                    $clearOldValues[$key] = $this->old_values[$key] === false? 'false':($this->old_values[$key]===true?'true':$this->old_values[$key]);
                    break;
            }
        }
        return [
            'old_values'=>$clearOldValues,
            'new_values'=>$clearNewValues,
        ];
    }

	protected function serializeDate(DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}
}
