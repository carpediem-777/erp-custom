<?php
/**
+-----------------------------------------------------------------------------------------------
* GouGuOPEN [ 左手研发，右手开源，未来可期！]
+-----------------------------------------------------------------------------------------------
* @Copyright (c) 2021~2024 http://www.gouguoa.com All rights reserved.
+-----------------------------------------------------------------------------------------------
* @Licensed 勾股OA，开源且可免费使用，但并不是自由软件，未经授权许可不能去除勾股OA的相关版权信息
+-----------------------------------------------------------------------------------------------
* @Author 勾股工作室 <hdm58@qq.com>
+-----------------------------------------------------------------------------------------------
*/
 
declare (strict_types = 1);

namespace app\customer\controller;

use app\base\BaseController;
use app\customer\model\CustomerTrace as CustomerTraceModel;
use think\facade\Db;
use think\facade\View;

class Trace extends BaseController
{
	/**
     * 构造函数
     */
	protected $model;
    public function __construct()
    {
		parent::__construct(); // 调用父类构造函数
        $this->model = new CustomerTraceModel();
    }
	
    /**
    * 数据列表
    */
    public function datalist()
    {
		$param = get_params();
		$uid=$this->uid;
        if (request()->isAjax()) {
			$where=[];
			$where[]=['delete_time','=',0];
            if (!empty($param['keywords'])) {
                $where[] = ['id|title', 'like', '%' . $param['keywords'] . '%'];
            }
			if (!empty($param['follow_time'])) {
				$follow_time =explode('~', $param['follow_time']);
				$where[] = ['follow_time', 'between',[strtotime(urldecode($follow_time[0])),strtotime(urldecode($follow_time[1].' 23:59:59'))]];
            }
			if (!empty($param['uid'])) {
                $where[] = ['admin_id','=',$param['uid']];
            }
			$map=[];
			$mapOr=[];
			$map[]=['delete_time','=',0];
			$map[]=['discard_time','=',0];
			
			$mapOr[] = ['belong_uid','=',$uid];
			$mapOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',share_ids)")];
			$dids_a = get_leader_departments($uid);
			//是否是客户管理员
			$auth = isAuth($uid,'customer_admin','conf_1');
			if($auth == 1){
				$dids_b = get_role_departments($uid);
				$dids = array_merge($dids_a, $dids_b);
				if(!empty($dids)){
					$mapOr[] = ['belong_did','in',$dids];
				}
			}
			else{
				if(!empty($dids_a)){
					$mapOr[] = ['belong_did','in',$dids_a];
				}
			}
			
			$cids = Db::name('Customer')
				->where($map)
				->where(function ($query) use($mapOr) {
					if (!empty($mapOr)){
						$query->whereOr($mapOr);
					}
				})->column('id');
				
			$where[] = ['cid', 'in',$cids];
            $list = $this->model->datalist($param,$where);
            return table_assign(0, '', $list);
        }
        else{
			View::assign('is_auth', isAuth($uid,'customer_admin','conf_1'));
            return view();
        }
    }
	
    /**
    * 添加/编辑
    */
    public function add()
    {
		$param = get_params();	
        if (request()->isAjax()) {
			if(isset($param['follow_time'])){
				$param['follow_time'] = strtotime($param['follow_time']);
			}
			if(isset($param['next_time'])){
				$param['next_time'] = strtotime($param['next_time']);
			}
			$param['update_time'] = time();
            if (!empty($param['id']) && $param['id'] > 0) {
				$this->model->edit($param);
            } else {
				$param['create_time'] = time();
                $param['admin_id'] = $this->uid;
                $this->model->add($param);
            }	 
        }else{
            $id = isset($param['id']) ? $param['id'] : 0;
			if ($id > 0) {
				View::assign('detail', $this->model->getById($id));
				return view('edit');
			}
            $customer_id = isset($param['cid']) ? $param['cid'] : 0;
			$customer_name = Db::name('Customer')->where('id',$customer_id)->value('name');
            View::assign('customer_id', $customer_id);
            View::assign('customer_name', $customer_name);
			if(is_mobile()){
				return view('qiye@/customer/trace_add');
			}
            return view();
		}
    }
	
    /**
    * 查看
    */
    public function view($id)
    {
		$detail = $this->model->getById($id);
		if (!empty($detail)) {
			$detail['contact'] = Db::name('CustomerContact')->where('id',$detail['contact_id'])->value('name');
			$detail['types_name'] = Db::name('BasicCustomer')->where('id',$detail['types'])->value('title');
			$detail['stage_name'] = Db::name('BasicCustomer')->where('id',$detail['stage'])->value('title');
			if($detail['chance_id']>0){
				$detail['chance'] = Db::name('CustomerChance')->where(['id' => $detail['chance_id']])->value('title');
			}
			else{
				$detail['chance']='-';
			}
			View::assign('detail', $detail);
			if(is_mobile()){
				return view('qiye@/customer/trace_view');
			}
			return view();
		}
		else{
			return view(EEEOR_REPORTING,['code'=>404,'warning'=>'找不到页面']);
		}
    }
	
   /**
    * 删除
    */
    public function del()
    {
		$param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		if (request()->isDelete()) {
			$this->model->delById($id);
		} else {
            return to_assign(1, "错误的请求");
        }
    }   

}
