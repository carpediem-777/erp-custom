<?php
/**
* @Copyright (c) 2021~2024 http://www.gouguoa.com All rights reserved.
+-----------------------------------------------------------------------------------------------
* @Author Phoenix 工作室 <hdm58@qq.com>
+-----------------------------------------------------------------------------------------------
*/
use think\facade\Db;

//是否是报销打款管理员,count>1即有权限
function isAuthExpense($uid)
{
	if($uid == 1){
		return 1;
	}
	$map = [];
	$map[] = ['name', '=', 'finance_admin'];
	$map[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',conf_1)")];
    $count = Db::name('DataAuth')->where($map)->count();
    return $count;
}

//是否是发票管理员,count>1即有权限
function isAuthInvoice($uid)
{
	if($uid == 1){
		return 1;
	}
	$map = [];
	$map[] = ['name', '=', 'finance_admin'];
	$map[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',conf_2)")];
    $count = Db::name('DataAuth')->where($map)->count();
    return $count;
}

//是否是到账管理员,count>1即有权限
function isAuthIncome($uid)
{
	if($uid == 1){
		return 1;
	}
	$map = [];
	$map[] = ['name', '=', 'finance_admin'];
	$map[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',conf_3)")];
    $count = Db::name('DataAuth')->where($map)->count();
    return $count;
}

//是否是付款管理员,count>1即有权限
function isAuthPayment($uid)
{
	if($uid == 1){
		return 1;
	}
	$map = [];
	$map[] = ['name', '=', 'finance_admin'];
	$map[] = ['', 'exp', Db::raw("FIND_IN_SET('{$uid}',conf_4)")];
    $count = Db::name('DataAuth')->where($map)->count();
    return $count;
}
