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
use app\contract\model\Contract as ContractModel;
use app\contract\model\Purchase as PurchaseModel;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Contract extends BaseController
{
	/**
     * 构造函数
     */
	protected $model_c; 
	protected $model_p; 
    public function __construct()
    {
		parent::__construct(); // 调用父类构造函数
        $this->model_c = new ContractModel();
        $this->model_p = new PurchaseModel();
    }
	
	//合同管理
    public function index()
    {
		$uid = $this->uid;
		$auth = isAuth($uid,'contract_admin','conf_1');
		$dids = [];
		
		$where=[];
		$whereOr=[];
		$where[]=['delete_time','=',0];
		$where[]=['archive_time','=',0];
		$where[]=['stop_time','=',0];
		$where[]=['void_time','=',0];

		if($auth==0){
			$whereOr[] =['admin_id|prepared_uid|sign_uid|keeper_uid', '=', $uid];
			$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',share_ids)")];
			$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
			$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
			$dids = get_leader_departments($uid);
			if(!empty($dids)){
				$whereOr[] =['did', 'in', $dids];
			}
		}
		$contract_count_a = $this->model_c->where($where)
			->where(function ($query) use($whereOr) {
				if (!empty($whereOr)){
					$query->whereOr($whereOr);
				}
			})->count();

		$where_b=$where;
		$where_b[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
		$contract_count_b = $this->model_c->where($where_b)->count();

		$where_c=$where;
		$where_c[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
		$contract_count_c = $this->model_c->where($where_c)->count();
		
		$map=[];
		$map[]=['delete_time','=',0];
		$map[]=['archive_time','=',0];
		$map[]=['stop_time','=',0];
		$map[]=['void_time','=',0];
		
		if($auth==0){
			$mapOr[] =['admin_id|prepared_uid|sign_uid|keeper_uid', '=', $uid];
			$mapOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',share_ids)")];
			$mapOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
			$mapOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];			
			if(!empty($dids)){
				$mapOr[] =['did', 'in', $dids];
			}
		}
		
		$purchase_count_a = $this->model_p->where($map)
			->where(function ($query) use($whereOr) {
				if (!empty($mapOr)){
					$query->whereOr($whereOr);
				}
			})->count();

		$map_b=$map;
		$map_b[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
		$purchase_count_b = $this->model_p->where($map_b)->count();

		$map_c=$map;
		$map_c[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
		$purchase_count_c = $this->model_p->where($map_c)->count();
		

		View::assign('contract_count', [
			'a'=>$contract_count_a,
			'b'=>$contract_count_b,
			'c'=>$contract_count_c
		]);
		
		View::assign('purchase_count', [
			'a'=>$purchase_count_a,
			'b'=>$purchase_count_b,
			'c'=>$purchase_count_c
		]);
        return View();
    }
	
	
	//销售合同
    public function contract()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }
	
	//归档销售合同
    public function contract_archive()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }

	//中止销售合同
    public function contract_stop()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }

	//作废销售合同
    public function contract_void()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }
	
	//采购合同
    public function purchase()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }
	
	//归档采购合同
    public function purchase_archive()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }
	
	//中止采购合同
    public function purchase_stop()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }
	
	//作废采购合同
    public function purchase_void()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }
}
