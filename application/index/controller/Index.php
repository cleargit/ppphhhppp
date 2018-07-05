<?php
namespace app\index\controller;

use app\index\model\Account;

use app\index\model\Member_info;
use app\index\model\MemberInfo;
use app\index\model\ProduceType;
use think\Db;
use think\helper\Time;
use think\Route;
Route::rule('hello','index/index/index');
header('Access-Control-Allow-Origin:*');
header("Content-type:text/html;charset=utf-8");
class Index extends BaseController
{
    public function index(){
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
    }
    public function register(){
        if ($this->request->isPost()){
            $accountdata = [		//接受传递的参数
                'account' => $this->request->post('userPhone'),
                'password' => Account::creatpass(($this->request->post('password'))),
                'system' => $this->request->post('registerSys'),
                'accessToken'=>$this->create_uuid(),
                'roleId'=>4,
                'registerTime'=> date("Y-m-d",\time())
            ];
            $user=Account::get(['account'=>$accountdata['account']]);
            if ($user){
                return ResultVo::error('手机号已存在');
            }
            $account=new Account();
            $info=new MemberInfo();
            $account->startTrans();
            $result= $account->insertGetId($accountdata);
            $infodata=[
                'account_id' => $result,
                'inviteCode' => $this->request->post('invitecode'),
                'SIM' => $this->request->post('SIM'),
                'IMEI' => $this->request->post('IMEI'),
            ];
            $info->insert($infodata);
            if ($account && $info){
                $account->commit();
                return  ResultVo::success('注册成功');
            }else{
                $account->rollback();
            }
            return ResultVo::error();
        }
    }
    public  function login(){
        if ($this->request->isPost()){
            $data = [		//接受传递的参数
                'account' => $this->request->post('userName'),
                'password' => Account::creatpass(($this->request->post('password'))),
            ];

            $user=Account::get(['account'=>$data['account']]);
            if (!isset($user) && $user==null){
                return ResultVo::error('','账号不存在','1');
            }
            if ($user->password==$data['password']){
                return ResultVo::success($user['accessToken']);
            }else{
                return ResultVo::error('','密码错误','1');
            }
        }else{
            return $this->fetch();
        }

    }
    function create_uuid(){
        $str = md5(uniqid(mt_rand(), true));
        return $str;
    }


    public function getBannerList(){
        return  ResultVo::success(parent::find('ssg_banner'));
    }

    public function getThreeGoodsADInfo(){
        return ResultVo::success(parent::find('ssg_ad_entrance'));
    }

    public function getRecommendation(){
        $page=$this->request->get('page');
        return ResultVo::success(parent::find('ssg_host_sale',"",$page));
    }
    public function getClassifyone(){
        $type=new ProduceType();
        $result=$type->field('id,name')->where("parentID=0")->select();
        return ResultVo::success($result);
    }
    public function  getClassifyTwo(){
        if ($this->request->isPost()) {
            $classifyId=$this->request->post('classifyId');
            $type=new ProduceType();
            $result=$type->field('id,name')->where("parentID={$classifyId}")->select();
            return ResultVo::success($result);
        }
    }
    public function  getClassifyThree(){
        if ($this->request->isPost()) {
            $classifyId=$this->request->post('classifyId');
            $type=new ProduceType();
            $result=$type->field('id,name')->where("parentID={$classifyId}")->select();
            return ResultVo::success($result);
        }
    }
    public function getGoodsList(){
        $keyword=$this->request->post('keyword');
        $three_type='';
        $ClassifyThreeId=$this->request->post('ClassifyThreeId');
        if ($ClassifyThreeId!=0){
            $three_type="a.produce_type_three_id={$ClassifyThreeId} AND";
        }
        $difference=$this->request->post('difference');
        $expand='';
        if ($difference==1){
            $expand='AND a.isRecommended=1';
        }elseif ($difference==2){
            $expand='AND a.isHostSale=1';
        }
        $type=$this->request->post('type'); //要你有何用
        $page=parent::getPage();
        $sql="SELECT
	a.NAME,
	a.goodsCode,
	a.originalPrice,
	a.price,
	(SELECT  image FROM ssg_produce i WHERE a.goodsCode=i.goodsCode) as image
FROM
	ssg_produce a
	LEFT JOIN ssg_produce_images b ON a.goodsCode = b.goodsCode WHERE {$three_type} a.`name` LIKE '%{$keyword}%' {$expand}
	LIMIT {$page}
	";

        return ResultVo::success( parent::exec($sql));
    }
    public function getGoodsDetails(){
//        $accessToken=parent::getAccountId()['0']['id'];  //todo 没写收藏
        $goodsCode=$this->request->post('goodsCode');
        $imagesql="SELECT b.image FROM ssg_produce a LEFT JOIN ssg_produce_images b ON a.goodsCode=b.goodsCode WHERE a.goodsCode={$goodsCode} ";
        $infosql="SELECT a.*,SUM( c.stock ) as totalNum FROM ssg_produce a LEFT JOIN ssg_produce_specifications c ON a.goodsCode = c.goodsCode WHERE a.goodsCode = {$goodsCode} ";
        //可写成一条 用GROUP_CONCAT 后期处理 php不熟
        $images=parent::exec($imagesql);
        $info=parent::exec($infosql);
        $info['goodsImageList']=$images;
        return ResultVo::success($info);
    }
    public function getGoodsSpecifications(){
        $goodsCode=$this->request->post('goodsCode');
        $sql="select * from ssg_produce_specifications where goodsCode={$goodsCode}";
        $model=new Account();
        return ResultVo::success($model->query($sql));
    }
    public function getMyCartInfo(){
        $accessToken=parent::getAccountId()['0']['id'];
        $page=parent::getPage();
        $sql = "SELECT 
	a.id,
	d.name,
	d.price,
	a.goodsCode,
	a.produceNum,
	b.model,
	a.specifications,
	b.size,
	(SELECT image FROM ssg_produce_images k WHERE k.goodsCode=a.goodsCode LIMIT 1)
FROM
	ssg_cart a 
	LEFT JOIN ssg_produce_specifications b ON a.goodsCode = b.goodsCode
	LEFT JOIN ssg_produce d ON a.goodsCode = d.goodsCode
WHERE
	a.account_id = {$accessToken} AND a.specifications=b.id
	LIMIT {$page}
	";

        return ResultVo::success(parent::exec($sql));
    }
    public function addGoodsToCart(){
        $data = [
            'account_id'=>parent::getAccountId()['0']['id'],
            'goodsCode'=>$this->request->post('goodsCode'),
            'produceNum' =>$this->request->post('goodsNum'),
            'specifications'=>$this->request->post('specificationsId')
        ];
        parent::save('ssg_cart',$data);
        return ResultVo::success("添加成功");
    }
    public function deleteGoodsForCart(){
        $w=[
            'account_id'=>parent::getAccountId()['0']['id'],
            'id'=>$this->request->post('id')
        ];
        parent::delete('ssg_cart',$w);
        return ResultVo::success("成功");
    }
    public function editGoodsForCart(){
        $accessToken=parent::getAccountId()['0']['id'];
        $editParam=$this->request->post('editParam');
        Db::table('cart')->where("account_id",$accessToken)->where('id',$editParam['id'])->exp('goodsCode',$editParam['goodsCode'])->exp('produceNum',$editParam['specificationsId'])->exp('number',$editParam['specificationsId']);
        return ResultVo::success("成功");
    }
    public function getMyShare(){
        $accessToken=parent::getAccountId()['0']['id'];
        $page=parent::getPage();
        $sql="SELECT
	b.`name`,
	b.price,
	a.goodsCode,
	a.id,
	(SELECT image FROM ssg_produce_images c WHERE a.goodsCode=c.goodsCode LIMIT 1 )
FROM
	ssg_share_record a
	LEFT JOIN ssg_produce b ON a.goodsCode = b.goodsCode 
WHERE
	a.account_id ={$accessToken}
	LIMIT {$page}
	";
        return ResultVo::success(parent::exec($sql));
    }
    public function deleteMyShare(){
        $w=[
            'account_id'=>parent::getAccountId()['0']['id'],
            'id'=>$this->request->post('id')
        ];
        parent::delete('ssg_share_record',$w);
        return ResultVo::success("删除成功");
    }

    public function shareGoods(){
        $data=[
            'goodsCode'=>$this->request->post('goodsCode'),
            'account_id'=>parent::getAccountId()['0']['id'],
            'shareTime'=>date("Y-m-d",\time())
        ];
        parent::save('ssg_share_record',$data);
        return ResultVo::success("");
    }
    public function addCollection(){
        $data=[
            'goodsCode'=>$this->request->post('goodsCode'),
            'account_id'=>parent::getAccountId()['0']['id'],
            'time'=> date("Y-m-d",\time())
        ];
        parent::save('ssg_collection',$data);
        return ResultVo::success("添加成功");
    }
    public function getMyCollection(){
        $accessToken=parent::getAccountId()['0']['id'];
        $page=parent::getPage();
        $sql="SELECT
	b.`name`,
	b.price,
	a.goodsCode,
	a.id,
	(SELECT image FROM ssg_produce_images c WHERE a.goodsCode=c.goodsCode LIMIT 1 )
FROM
	ssg_collection a
	LEFT JOIN ssg_produce b ON a.goodsCode = b.goodsCode 
WHERE
	a.account_id ={$accessToken}
	LIMIT {$page}
	";

        return ResultVo::success(parent::exec($sql));
    }
    public function deleteCollection(){
        $w=[
            'account_id'=>parent::getAccountId()['0']['id'],
            'id'=>$this->request->post('id')
        ];
        parent::delete('ssg_collection',$w);
        return ResultVo::success("删除成功");
    }
    public function addReceiveAddress(){
        $data=[
            'account_id'=>parent::getAccountId()['0']['id'],
            'name'=>$this->request->post('name'),
            'phone'=>$this->request->post('phone'),
            'province'=>$this->request->post('province'),
            'city'=>$this->request->post('city'),
            'area'=>$this->request->post('area'),
            'address'=>$this->request->post('address')
        ];
        parent::save('ssg_address',$data);
        return ResultVo::success("添加成功");
    }
    public function deleteAddress(){
        $w=[
            'account_id'=>parent::getAccountId()['0']['id'],
            'id'=>$this->request->post('id')
        ];
        parent::delete('ssg_address',$w);
        return ResultVo::success("删除成功");
    }
    public function getReceiveAddress(){
        $w=[
            'account_id'=> parent::getAccountId()['0']['id']
        ];
        return ResultVo::success(parent::find("ssg_address",$w));
    }
    public function getAddressDetail(){
        $data=[
            'account_id'=>parent::getAccountId()['0']['id'],
            'id'=> $id=$this->request->post('id')
        ];
        return ResultVo::success(parent::find("ssg_address",$data));
    }
    public function editAddress(){
        $w=[
            'id'=>$this->request->post('id'),
            'account_id'=> parent::getAccountId()['0']['id']
        ];
        $data=[
            'name'=>$this->request->post('name'),
            'phone'=>$this->request->post('phone'),
            'province'=>$this->request->post('province'),
            'city'=>$this->request->post('city'),
            'area'=>$this->request->post('area'),
            'address'=>$this->request->post('address')
        ];
        return ResultVo::success(parent::update("ssg_address",$data,$w));
    }
    public function personalDetails(){
        $accessToken=parent::getAccountId()['0']['id'];
        $data=[
            'account_id'=>$accessToken
        ];
        return ResultVo::success(parent::find("ssg_member_info",$data));
    }
    public function changePernalInfo(){
        $w=[
            'account_id'=> parent::getAccountId()['0']['id']
        ];
        $data=$this->request->post();
        unset($data['accessToken']);
        return ResultVo::success(parent::update("ssg_member_info",$data,$w));
    }
    public function getMyAlipayInfo(){
        $accessToken=parent::getAccountId()['0']['id'];
        $data=[
            'account_id'=>$accessToken
        ];
        return ResultVo::success(parent::find("",$data));
    }
    public function getMyBankCardInfo(){
        $accessToken=parent::getAccountId()['0']['id'];
        $data=[
            'account_id'=>$accessToken
        ];
        return ResultVo::success(parent::find("",$data));
    }
    public function getMyOrder(){
        $accessToken=parent::getAccountId()['0']['id'];
        $page=parent::getPage();
        $sql="SELECT
	a.orderNum,
	a.orderPrice,
	a.orderStatus,
	a.addressId,
	c.`name`,
	b.number ,
	(SELECT image FROM ssg_produce_images k WHERE k.goodsCode=b.goodsCode LIMIT 1) as goodsImage
FROM
	ssg_order a
	LEFT JOIN ssg_order_produce b ON a.orderNum = b.orderNum 
	LEFT JOIN ssg_produce c ON b.goodsCode=c.goodsCode
WHERE
	a.account_id ={$accessToken}
	LIMIT {$page}
	";

        return ResultVo::success(parent::exec($sql));
    }
    public function confirmReceiving(){
        $data=[
            'orderStatus'=>4,
        ];
        $w=[
            'account_id'=>parent::getAccountId()['0']['id'],
            'orderNum'=>$this->request->post('orderNum')
        ];
        return ResultVo::success(parent::update("ssg_order",$data,$w));
    }
    public function deleteOrder(){

        $w=[
            'account_id'=>parent::getAccountId()['0']['id'],
            'orderNum'=>$this->request->post('orderNum')
        ];
        return ResultVo::success(parent::delete("ssg_order",$w));
    }
    public function getOrderDetail(){
        $accessToken=parent::getAccountId()['0']['id'];
        $orderNum=$this->request->post('orderNum');
        $info="SELECT
	a.orderNum,
	a.orderTime,
	a.orderPrice,
	a.orderStatus,
	a.addressId,
	e.name,
	e.phone,
	e.address,
	(SELECT image FROM ssg_produce_images k WHERE k.goodsCode=b.goodsCode LIMIT 1) as goodsImage
FROM
	ssg_order a
	LEFT JOIN ssg_order_produce b ON a.orderNum = b.orderNum 
	LEFT JOIN ssg_produce c ON b.goodsCode=c.goodsCode
	LEFT JOIN ssg_produce_specifications d ON d.goodsCode=b.goodsCode
	LEFT JOIN ssg_address e ON a.addressId=e.id
WHERE
	a.account_id ={$accessToken} AND a.orderNum={$orderNum} LIMIT 1";
        $data="SELECT
	c.`name` as goodsName,
	c.price,
	b.number ,
	d.model,
	d.size,
	(SELECT image FROM ssg_produce_images k WHERE k.goodsCode=b.goodsCode LIMIT 1) as goodsImage
FROM
	ssg_order a
	LEFT JOIN ssg_order_produce b ON a.orderNum = b.orderNum 
	LEFT JOIN ssg_produce c ON b.goodsCode=c.goodsCode
	LEFT JOIN ssg_produce_specifications d ON d.id=b.specificationsId
WHERE
	a.account_id ={$accessToken} AND a.orderNum={$orderNum}";
        //可写成一条sql 用GROUP_CONCAT 后期处理 php不熟
        $result= parent::exec($info);
        $result['goodsList']=parent::exec($data);
        return ResultVo::success($result);
    }
    public function confirmOrder(){

        $data=file_get_contents("php://input");
        $hand=str_replace("code","payType",$data);
        $hand=str_replace("data","orderPrice",$hand);

       $input=json_decode($hand,true);
       if ($input['orderNum']==-1){
           $input['orderNum']=rand(10,1000)*time();
       }
       $goods=$input['goodsList'];
      unset($input['goodsList']);

        //减库存 没加事务
        $w=[
          'id'=>$goods['specificationsId']
        ];
        $num=$goods['num'];
        $stock=Db::table('ssg_produce_specifications')->field('stock')->where($w)->select();
        $stock=$stock[0]['stock'];
        if ($stock<$num){
            return ResultVo::error('库存不足');
        }
        $data=[
          'stock'=>$stock-$num
        ];
        parent::save('ssg_order',$input);
        parent::save('ssg_order_produce',$goods);
        parent::update('ssg_produce_specifications',$data,$w);
        return ResultVo::success('生成订单成功');
    }
    public function getAppWelcome(){
        return ResultVo::success( parent::find('ssg_start_page'));
    }
    public function getProvinceList(){
        returnResultVo::success( parent::find('ssg_province'));
    }
    public function getCityList(){
        return ResultVo::success(parent::find('ssg_city',$this->request->post('pCode')));
    }
    public function getAreaList(){
        return ResultVo::success(parent::find('ssg_area',$this->request->post('getAreaList')));
    }
    public function checkUpdate(){
        $w=[
            'number'=>$this->request->post('versionNum'),
            'system'=>$this->request->post('system')
        ];
        return parent::find('ssg_province',$w);
    }
    public function serverFeedback(){
        $w=[
            'versionNum'=>$this->request->post('version'),
            'system'=>$this->request->post('system'),
            'errorContent'=>$this->request->post('errorContent'),
            'time'=> date("Y-m-d",\time())
        ];
        return parent::save('ssg_server_feedback',$w);
    }
    public function page (){
        $w=[
            'number'=>$this->request->post('versionNum'),
            'system'=>$this->request->post('system')
        ];
        unset($w['number']);
        return ResultVo::success($this->request->post());
    }
}
