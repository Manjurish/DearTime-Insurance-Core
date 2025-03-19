<?php     

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request,$title = null)
    {
        $firstTime = auth()->user()->profile->coverages_owner->count() == 0;
        $uid = $request->input('uid') ?? 0;
        $fill_type = $request->input('fill_type');
        return view('user.product',compact('firstTime','uid','title','fill_type'));
    }
}
