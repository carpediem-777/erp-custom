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
use think\facade\Db;
use app\project\model\Project as ProjectModel;
use think\facade\Request;
use think\facade\View;

class Project extends BaseController
{
	/**
     * 构造函数
     */
	protected $model;
    public function __construct()
    {
		parent::__construct(); // 调用父类构造函数
        $this->model = new ProjectModel();
    }
	//项目管理
    public function index()
    {
		$uid = $this->uid;
		$auth = isAuth($uid ,'project_admin','conf_1');
		$time = time();
		$dalay_time = $time+7*86400;
		$where = array();
		$where[] = ['delete_time', '=', 0];
		$where[] = ['status', '<', 3];
		if($auth == 0){
			$project_ids = Db::name('ProjectUser')->where(['uid' => $uid , 'delete_time' => 0])->column('project_id');
			$where[] = ['id', 'in', $project_ids];
			$whereOr[] = ['director_uid', '=', $uid];
			$dids_a = get_leader_departments($uid);	
			$dids_b = get_role_departments($uid);
			$dids = array_merge($dids_a, $dids_b);
			if(!empty($dids)){
				$whereOr[] = ['did','in',$dids];
			}
		}
		$project_count_a = Db::name('Project')->where($where)->count();
		$project_count_b = Db::name('Project')->where($where)->where([['end_time','between',[$time,$dalay_time]]])->count();
		$project_count_c = Db::name('Project')->where($where)->where([['end_time','<',$time]])->count();
		View::assign('project_count', [
			'a'=>$project_count_a,
			'b'=>$project_count_b,
			'c'=>$project_count_c
		]);
		
		$map = array();
		$mapOr = array();
		$map[] = ['delete_time', '=', 0];
		$map[] = ['status','<',3];
		if($auth == 0){
			$mapOr[] = ['admin_id', '=', $uid];
			$mapOr[] = ['director_uid', '=', $uid];
			$mapOr[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',assist_admin_ids)")];
			$dids_a = get_leader_departments($uid);	
			$dids_b = get_role_departments($uid);
			$dids = array_merge($dids_a, $dids_b);
			if(!empty($dids)){
				$mapOr[] = ['did','in',$dids];
			}
		}
		$task_count_a = Db::name('ProjectTask')
			->where($map)
			->where(function ($query) use ($mapOr) {
				if (!empty($mapOr))
					$query->whereOr($mapOr);
				})
			->count();
		$task_count_b = Db::name('ProjectTask')
			->where($map)
			->where([['end_time','between',[$time,$dalay_time]]])
			->where(function ($query) use ($mapOr) {
				if (!empty($mapOr))
					$query->whereOr($mapOr);
				})
			->count();
		$task_count_c = Db::name('ProjectTask')
			->where($map)
			->where([['end_time','<',$time]])
			->where(function ($query) use ($mapOr) {
				if (!empty($mapOr))
					$query->whereOr($mapOr);
				})
			->count();
		View::assign('task_count', [
			'a'=>$task_count_a,
			'b'=>$task_count_b,
			'c'=>$task_count_c
		]);
        return View();
    }
	//项目列表
    public function project()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }
	
	//任务列表
    public function task()
    {
		$param = get_params();
		$tab = isset($param['tab']) ? $param['tab'] : 0;
		View::assign('tab', $tab);
        return View();
    }
	
	//任务新增
    public function task_add()
    {
		$param = get_params();
		$project_id = isset($param['project_id']) ? $param['project_id'] : 0;
		View::assign('project_id', $project_id);
        return View();
    }
	
	//任务工时
    public function schedule()
    {
        return View();
    }
	
	//文档列表
    public function document()
    {
        return View();
    }
}
