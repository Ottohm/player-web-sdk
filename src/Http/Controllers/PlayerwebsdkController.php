<?php

namespace Ottohm\PlayerWebSDK\Http\Controllers;

use App\Http\Controllers\Controller; 

use Illuminate\Http\Request;

class PlayerwebsdkController extends Controller 
{
    public function index($id){
        $videoPlayerBaseURL = config('playerwebsdk.videoPlayerBaseURL');
        $getVideoDetails = config('playerwebsdk.getVideoDetails');
        $secretKey = config('playerwebsdk.secretKey');
        $data = [
            "id" => $id,
            "videoPlayerBaseURL" => $videoPlayerBaseURL,
            "getVideoDetails" => $getVideoDetails,
            "secretKey"=> $secretKey

        ];
        return view('playerwebsdk::playerwebsdk')->with($data);
    }

    
    public function exit(){
            echo "<script>exit()<\/script>";
    }

    public function test($id2){
            echo "<script>newVideoPLay($id2)<\/script>";
    }
}
