<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;

class CoverageOrder extends Model
{
    public function coverage()
    {
        return $this->belongsTo(Coverage::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
