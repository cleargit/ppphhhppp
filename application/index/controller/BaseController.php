<?php
/**
 * Created by PhpStorm.
 * User: sham
 * Date: 2018/6/30
 * Time: 11:42
 */

namespace app\index\controller;


use app\index\model\Account;
use think\Controller;
use think\Db;
use think\Model;

class BaseController extends Controller{
    public function select($model,$page=""){
    $m=\model($model);
    return ResultVo::success($m->page($page)->select());
    }
    public function delete($db,$w){
        Db::table($db)->where($w)->delete();
    }
    public function save($db,$data){
        Db::table($db)->insert($data);
    }
    public function find($db,$data='',$page=''){
        return Db::table($db)->where($data)->page($page)->select();
    }
    public function update($db,$data,$w){
        return Db::table($db)->where($w)->update($data);
    }
    public function exec($sql){
        $model=new Account();
         return  $model->query($sql);

    }
    public function getAccountId(){
        $accessToken=$this->request->post('accessToken');
        $acconut=new Account();
        $sql="SELECT id FROM ssg_account WHERE accessToken ='{$accessToken}'";
        return $acconut->query($sql);
    }
    public function  getPage(){
        $page=$this->request->post('page');
        if ($page==null){
            return '0,10';
        }
        $data=explode(',',$page);
        $pageSize=$data[1];
        $pageNo=($data[0]-1)*$pageSize;
        return "{$pageNo},{$pageSize}";

    }
}