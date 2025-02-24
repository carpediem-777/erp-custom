<?php
/**
* @Copyright (c) 2021~2024 http://www.gouguoa.com All rights reserved.
+-----------------------------------------------------------------------------------------------
* @Author Phoenix 工作室 <hdm58@qq.com>
+-----------------------------------------------------------------------------------------------
*/
declare (strict_types = 1);
namespace app\qiye\controller;

use app\qiye\BaseController;
use app\customer\model\Customer as CustomerModel;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Customer extends BaseController
{
	/**
     * 构造函数
     */
	protected $model;
    public function __construct()
    {
		parent::__construct(); // 调用父类构造函数
        $this->model = new CustomerModel();
    }
	
	//客户管理
    public function index()
    {
		$uid = $this->uid;
		$where=[];
		$where[]=['delete_time','=',0];
		$where[]=['discard_time','=',0];
		$count_a = $this->model->where($where)->where('belong_uid','=',$uid)->count();
		
		$where[]=['belong_uid','<>',$uid];
		$count_b = $this->model->where($where)->where('belong_did','in',get_leader_departments($uid))->count();
		
		$where[]=['', 'exp', Db::raw("FIND_IN_SET('{$uid}',share_ids)")];
		$count_c = $this->model->where($where)->count();
		View::assign('customer_count', [
			'a'=>$count_a,
			'b'=>$count_b,
			'c'=>$count_c
		]);
        return View();
    }
	
    /**
    * 我的客户
    */
    public function datalist()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return view();
    }
	
	
	//抢客宝
    public function rush()
    {
        return View();
    }
	
	//公海客户
    public function sea()
    {
        return View();
    }
	
	//废弃客户
    public function trash()
    {
        return View();
    }
	
	//客户联系人
    public function contact()
    {
        return View();
    }
	
	//客户线索机会
    public function chance()
    {
        return View();
    }
	
	//客户跟进记录
    public function trace()
    {
        return View();
    }
}
