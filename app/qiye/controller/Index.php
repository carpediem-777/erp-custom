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
use think\facade\Request;
use think\facade\View;

class Index extends BaseController
{
	//工作台
    public function index()
    {		
		$user = Db::name('Admin')->field('a.*,d.title as department,p.title as position')
                ->alias('a')
                ->join('Department d', 'a.did = d.id', 'LEFT')
                ->join('Position p', 'a.position_id = d.id', 'LEFT')
                ->where('a.id',$this->uid)
                ->find();
	    View::assign('user', $user);
        return View();
    }
	
	//日程安排
    public function calendar()
    {
		if (request()->isAjax()) {
            $param = get_params();
			$start = strtotime($param['start_date']);
			$end = strtotime($param['end_date']);
            $where = [];

            $where[] = ['delete_time', '=', 0];
            $where[] = ['admin_id', '=', $this->uid];
            $where[] = ['start_time','<',$end];
            $where[] = ['end_time','>',$start];

            $list = Db::name('Plan')
            ->where($where)
            ->field('id,title,type,remind_type,start_time,end_time')
            ->select()->toArray();
			$days = [];
			foreach ($list as $k => $v) {
                $v['title'] = date('Y年n月j日', $v['start_time']);
				$days[]=$v;
				$star_time = $v['start_time'];
				for($i=1;$i<32;$i++){
					$time = $star_time+$i*86400;
					if($time<$v['end_time']){
						$v['title'] = date('Y年n月j日', $time);
						$days[]=$v;
					}
				}
            }
            return to_assign(0,'',$days);
        } else {
            return view();
        }
    }
	
	//获取日期日程
    public function events()
    {
		if (request()->isAjax()) {
            $param = get_params();
            $start = strtotime($param['day']);
            $end = $start+86400;
			$w = date('w', $start);
            $where = [];

            $where[] = ['delete_time', '=', 0];
            $where[] = ['admin_id', '=', $this->uid];
            $where[] = ['start_time','<',$end];
            $where[] = ['end_time','>',$start];

            $list = Db::name('Plan')
            ->where($where)
            ->field('id,title,type,remind_type,start_time,end_time')
            ->select()->toArray();
			
			foreach ($list as $k => &$v) {
                $v['start_a'] = date('Y-m-d', $v['start_time']);
                $v['start_b'] = date('H:i', $v['start_time']);
                $v['start'] = date('Y-m-d H:i', $v['start_time']);
				
                $v['end_a'] = date('Y-m-d', $v['end_time']);
                $v['end_b'] = date('H:i', $v['end_time']);
                $v['end'] = date('Y-m-d H:i', $v['end_time']);
				
				$str='';
				if($v['start_time']>=$start && $v['end_time']<=$end){
					$str= $v['start_b'].'<br>'.$v['end_b'];
				}
				if($v['start_time']>=$start && $v['end_time']>$end){
					$str= $v['start_b'].'<br>开始';
				}
				if($v['start_time']<$start && $v['end_time']<=$end){
					$str= $v['end_b'].'<br>结束';
				}
				if($v['start_time']<$start && $v['end_time']>$end){
					$str= '全天';
				}
				$v['show'] = $str;
            }
			$dayOfWeek = [
				0 => '周日',
				1 => '周一',
				2 => '周二',
				3 => '周三',
				4 => '周四',
				5 => '周五',
				6 => '周六',
			];
            return to_assign(0,date('Y年n月j日',$start).' ('.$dayOfWeek[$w].')',$list);
        } else {
            return view();
        }
    }
	
	//日程安排新增
    public function calendar_add()
    {
        return View();
    }
	
	//工作记录
    public function schedule()
    {
        return View();
    }
	
	//工作记录新增
    public function schedule_add()
    {
		$param = get_params();
		$task_id = isset($param['task_id']) ? $param['task_id'] : 0;
		if($task_id>0){
			$task = Db::name('ProjectTask')->find($task_id);
			View::assign('task', $task);
		}
        return View();
    }
	
	//工作汇报
    public function work()
    {
		$param = get_params();
		$send = isset($param['send']) ? $param['send'] : 1;
		View::assign('send', $send);
        return View();
    }
	
	//工作新增
    public function work_add($types=1)
    {
		View::assign('types', $types);
        return View('work_add_'.$types);
    }
	
	//公告通知
    public function note()
    {
        return View();
    }
	
	//公司新闻
	public function news()
    {
        return View();
    }

	//会议纪要
	public function meeting()
    {
        return View();
    }
}
