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

class User extends BaseController
{
    public function index()
    {
		$user = Db::name('Admin')->field('a.*,d.title as department')
                ->alias('a')
                ->join('Department d', 'a.did = d.id', 'LEFT')
                ->where('a.id',$this->uid)
                ->find();
	    View::assign('user', $user);
        return View();
    }
	
    public function user()
    {
        return View();
    }
}
