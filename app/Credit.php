<?php      
 // ALL RIGHTS RESERVED ® DEARTIME BERHAD 
 // Last Updated: 24/09/2021 


namespace App;

use App\Helpers\Enum;
use App\Traits\Uuids;
use App\Traits\UuidsRefs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;
    use UuidsRefs;
    public $prefix = 'CR';

    protected $guarded = [];

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function thanksgiving(){
        return $this->belongsTo(Thanksgiving::class,'type_item_id');
    }

    public function fromUser(){
        return $this->belongsTo(User::class,'from_id');
    }

    public function toUser(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function typeable(){
        return $this->morphTo(__FUNCTION__,'type','type_item_id');
    }

    public function typeableWithTrashed(){
        return $this->morphTo(__FUNCTION__,'type','type_item_id')->withTrashed();
    }

    public static function createDepositCharity($ownerId,$order){
        $individual_id = Individual::where('user_id',$ownerId)->first()->id;
        $thanksgiving = Thanksgiving::where('individual_id',$individual_id)->where('type','charity')->first();
        Credit::create([
            'order_id'=>$order->id,
            'from_id'=>$ownerId,
            'amount'=>$order->true_amount * ($thanksgiving->percentage / config('static.thanksgiving_percent')),
            'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
            'type_item_id'=>$thanksgiving->id
        ]);
    }

    public static function createDepositSelf($ownerId,$order,$amount){
        $individual_id = Individual::where('user_id',$ownerId)->first()->id;
        $thanksgiving = Thanksgiving::where('individual_id',$individual_id)->where('type',Enum::THANKSGIVING_TYPE_SELF)->first();
        Credit::create([
            'order_id'=>$order->id,
            'user_id'=>$ownerId,
            'from_id'=>$ownerId,
            'amount'=>$amount,
            'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
            'type_item_id'=> $thanksgiving->id
        ]);
    }

    public static function createWithdrawSelf($ownerId,$order){
        $individual_id = Individual::where('user_id',$ownerId)->first()->id;
        $thanksgiving = Thanksgiving::where('individual_id',$individual_id)->where('type',Enum::THANKSGIVING_TYPE_SELF)->first();
         if($thanksgiving){
        $amount = Credit::where('order_id',$order->id)->where('type',Enum::CREDIT_TYPE_THANKS_GIVING)
            ->whereHas('thanksgiving',function ($q){
                $q->where('type',Enum::THANKSGIVING_TYPE_SELF);
            })->first()->amount;

        Credit::create([
            'order_id'=>$order->id,
            'user_id'=>$ownerId,
            'from_id'=>$ownerId,
            'amount'=>-1*($amount),
            'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
            'type_item_id'=>$thanksgiving->id
        ]);
        }
    }

    public static function createDepositPromoter($ownerId,$order){
        $individual_id = Individual::where('user_id',$ownerId)->first()->id;
        $thanksgiving = Thanksgiving::where('individual_id',$individual_id)->where('type',Enum::THANKSGIVING_TYPE_PROMOTER)->first();
        $promoter = User::where('id',$ownerId)->first();
        Credit::create([
            'order_id'=>$order->id,
            'user_id'=>$promoter->promoter_id,
            'from_id'=>$ownerId,
            'amount'=>$order->true_amount * ($thanksgiving->percentage / config('static.thanksgiving_percent')),
            'type'=>Enum::CREDIT_TYPE_THANKS_GIVING,
            'type_item_id'=>$thanksgiving->id
        ]);
    }

    public function scopeFilterType($query, $value){
        return $query->whereHas('thanksgiving',function ($q)use($value){
            $q->where('type',$value);
        });
    }
}
