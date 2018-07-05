<?php
/**
 * Created by PhpStorm.
 * User: sham
 * Date: 2018/6/14
 * Time: 12:12
 */
namespace app\index\model;

use think\Model;
class Account extends Model{

    public static function creatpass($password){
        return md5(md5($password));
    }
}