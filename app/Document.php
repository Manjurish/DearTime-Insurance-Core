<?php       // ALL RIGHTS RESERVED ® DEARTIME BERHAD  // Last Updated: 24/09/2021 

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at', 'id', 'documentable_type', 'documentable_id','path','type','ext', 'created_by'];
    protected $appends = ['ThumbLink'];

    public function getS3UrlAttribute()
    {
        return Storage::disk('s3')->get($this->path);
    }
    public function getThumbS3UrlAttribute()
    {
        return Storage::disk('s3')->get($this->thumb_path);
    }
    public function getLinkAttribute()
    {
        return route('admin.dashboard.documentResize',['actual',$this->url,$this->ext]);
    }
    public function getTinyLinkAttribute()
    {
        return route('admin.dashboard.documentResize',['tiny',$this->url,$this->ext]);
    }
    public function getThumbLinkAttribute()
    {
        return route('admin.dashboard.documentResize',['thumb',$this->url,$this->ext]);
    }
    public function getBigLinkAttribute()
    {
        return route('admin.dashboard.documentResize',['big',$this->url,$this->ext]);
    }

    public function getLink($type)
    {
        return route('admin.dashboard.documentResize',[$type,$this->url,$this->ext]);
    }


}
