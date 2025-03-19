<?php     

namespace App\Http\Controllers;


use Illuminate\Http\Request;


class TPADataSyncController extends Controller
{
    public function upload(){

        $connection = (new \App\SSHConnection())
            ->to(config('remote.connections.staging.host'))
            ->onPort(22)
            ->as(config('remote.connections.staging.username'))
            ->withPassword(config('remote.connections.staging.password'))
            ->connect();
        $connection->upload(resource_path('reporting/UW_Ratio_V1.0.xlsx'), 'UW.xlsx');
        $content = $connection->download('UW.xlsx');
        // TODO: process claim data
       // return $content;
    }
}
