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
use app\finance\model\Expense as ExpenseModel;
use app\finance\model\Invoice as InvoiceModel;
use app\finance\model\Ticket as TicketModel;
use app\finance\model\InvoiceIncome as IncomeModel;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Finance extends BaseController
{
	/**
     * 构造函数
     */
	protected $model_e;
	protected $model_i;
	protected $model_t;
    public function __construct()
    {
		parent::__construct(); // 调用父类构造函数
        $this->model_e = new ExpenseModel();
        $this->model_i = new InvoiceModel();
        $this->model_t = new TicketModel();
    }
	
	
	//报销列表
    public function expense()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 1;
        if (request()->isAjax()) {
			$uid=$this->uid;
            $where = array();
            $whereOr = array();
			$where[]=['delete_time','=',0];
			if($tab == 0){
				//全部
				$whereOr[] = ['admin_id', '=', $this->uid];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
			if($tab == 1){
				//我申请的
				$where[] = ['admin_id', '=', $this->uid];
			}
			if($tab == 2){
				//待我审核的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
			}
			if($tab == 3){
				//我已审核的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
			}
			if($tab == 4){
				//抄送给我的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
            $list = $this->model_e->datalist($param,$where,$whereOr);
            return table_assign(0, '', $list);
        }
        else{
			View::assign('tab', $tab);
            return view();
        }
    }
	
	//发票列表
	public function invoice()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 1;
        if (request()->isAjax()) {
			$uid=$this->uid;
            $where = array();
            $whereOr = array();
			$where[]=['delete_time','=',0];
			$where[]=['invoice_type','>',0];
			if($tab == 0){
				//全部
				$whereOr[] = ['admin_id', '=', $this->uid];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
			if($tab == 1){
				//我申请的
				$where[] = ['admin_id', '=', $this->uid];
			}
			if($tab == 2){
				//待我审核的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
			}
			if($tab == 3){
				//我已审核的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
			}
			if($tab == 4){
				//抄送给我的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
			if($tab == 5){
				//已开具的
				$where[] = ['open_status', '=', 1];
			}
			if($tab == 6){
				//已作废的
				$where[] = ['open_status', '=', 2];
			}
            $list = $this->model_i->datalist($param,$where,$whereOr);
            return table_assign(0, '', $list);
        }
        else{
			View::assign('tab', $tab);
            return view();
        }
    }
	
	//收票列表
    public function ticket()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 1;
        if (request()->isAjax()) {
			$uid=$this->uid;
            $where = array();
            $whereOr = array();
			$where[]=['delete_time','=',0];
			$where[]=['invoice_type','>',0];
			if($tab == 0){
				//全部
				$whereOr[] = ['admin_id', '=', $uid];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
			if($tab == 1){
				//我创建的
				$where[] = ['admin_id', '=', $uid];
			}
			if($tab == 2){
				//待我审核的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
			}
			if($tab == 3){
				//我已审核的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
			}
			if($tab == 4){
				//抄送给我的
				$where[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
            $list = $this->model_t->datalist($param,$where,$whereOr);
            return table_assign(0, '', $list);
        }
        else{
			View::assign('tab', $tab);
            return view();
        }
    }
	
	//发票回款
    public function income()
    {
		$uid=$this->uid;
		$auth = isAuthIncome($uid);
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            $whereOr = array();
            $where[] = ['delete_time', '=', 0];
            $where[] = ['check_status', '=', 2];
            $where[] = ['open_status', '=', 1];
			$where[] = ['invoice_type','>',0];
			if($auth == 0){
				$whereOr[] = ['admin_id', '=', $uid];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
			$list = $this->model_i->datalist($param,$where,$whereOr);
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }
	
	//无发票回款
    public function income_a()
    {
		$uid=$this->uid;
		$auth = isAuthIncome($uid);
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            $whereOr = array();
            $where[] = ['delete_time', '=', 0];
            $where[] = ['check_status', '=', 2];
			$where[] = ['invoice_type','=',0];
			if($auth == 0){
				$whereOr[] = ['admin_id', '=', $uid];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
			$list = $this->model_i->datalist($param,$where,$whereOr);
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }
	
	//发票付款
    public function payment()
    {
		$uid=$this->uid;
		$auth = isAuthIncome($uid);
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            $whereOr = array();
            $where[] = ['delete_time', '=', 0];
            $where[] = ['check_status', '=', 2];
            $where[] = ['open_status', '=', 1];
			$where[] = ['invoice_type','>',0];
			if($auth == 0){
				$whereOr[] = ['admin_id', '=', $uid];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
			$list = $this->model_t->datalist($param,$where,$whereOr);
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }
	
	//无发票付款
    public function payment_a()
    {
		$uid=$this->uid;
		$auth = isAuthIncome($uid);
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            $whereOr = array();
            $where[] = ['delete_time', '=', 0];
            $where[] = ['check_status', '=', 2];
			$where[] = ['invoice_type','=',0];
			if($auth == 0){
				$whereOr[] = ['admin_id', '=', $uid];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_history_uids)")];
				$whereOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',check_copy_uids)")];
			}
			$list = $this->model_t->datalist($param,$where,$whereOr);
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }
}
