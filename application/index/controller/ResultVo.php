<?php
/**
 * Created by PhpStorm.
 * User: sham
 * Date: 2018/6/30
 * Time: 9:34
 */

namespace app\index\controller;
use app\index\model\Account;
use think\Controller;
class ResultVo {
    static function  error($data,$message='失败',$code=1){
        $json_arr = array('code'=>$code,'message'=>$message,'data'=>$data);
        $json_obj = json_encode($json_arr,JSON_UNESCAPED_UNICODE);
        return $json_obj;
    }
    static function success($data,$message='成功',$code=0){
        $json_arr = array('code'=>$code,'message'=>$message,'data'=>$data);
        $json_obj = json_encode($json_arr,JSON_UNESCAPED_UNICODE);
        return $json_obj;
    }

}