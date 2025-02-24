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
use app\home\model\Message;
use app\home\model\Msg as MsgList;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;

class Msg extends BaseController
{	
    //收件箱
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = [];
			$where[] = ['to_uid', '=', $this->uid];
			$where[] = ['delete_time', '=', 0];
            if (!empty($param['keywords'])) {
                $where[] = ['title', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['read'])) {
				if($param['read']==1){
					$where[] = ['read_time', '=', 0];
				}else{
					$where[] = ['read_time', '>', 0];
				}                
            }
			if (!empty($param['types'])) {
				if($param['types']==1){
					$where[] = ['from_uid', '=', 0];
				}else{
					$where[] = ['from_uid', '>', 0];
				}                
            }
            //按发送时间检索
			if (!empty($param['range_time'])) {
				$range_time =explode('~', $param['range_time']);
				$where[] = ['send_time', 'between',[strtotime(urldecode($range_time[0])),strtotime(urldecode($range_time[1]))]];
			}
			$model = new MsgList();
            $list = $model->datalist($where,$param);
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }
	
    //查看收件箱消息
    public function read($id )
    {
		$model = new MsgList();
		$model2 = new Message();
        $detail = $model->detail($id);
        if (empty($detail)) {
            throw new \think\exception\HttpException(406, '找不到记录');
        }
        if ($detail['to_uid'] != $this->uid) {
            throw new \think\exception\HttpException(406, '找不到记录');
        }
        MsgList::where(['id' => $id])->update(['read_time' => time()]);
		if($detail['message_id']>0){
			 View::assign('message', $model2->detail($detail['message_id']));
		}
		$detail['from_uname'] = Db::name('Admin')->where('id', $detail['from_uid'])->value('name');
        View::assign('detail', $detail);
        return view();
    }
	
    //评论信息
    public function comment()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $param['status'] = 1;
            $map = [];
            if (!empty($param['keywords'])) {
                $map[] = ['title', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['read'])) {
				if($param['read']==1){
					$map[] = ['read_time', '=', 0];
				}else{
					$map[] = ['read_time', '>', 0];
				}                
            }
            if (!empty($param['template'])) {
				if($param['template']==0){
					$map[] = ['template', '=', 0];
				}else{
					$map[] = ['template', '>', 0];
				}
            }
            $map[] = ['to_uid', '=', $this->uid];
            $map[] = ['status', '=', $param['status']];
            $map[] = ['is_draft', '=', 3];
            //按时间检索
            $start_time = isset($param['start_time']) ? strtotime(urldecode($param['start_time'])) : 0;
            $end_time = isset($param['end_time']) ? strtotime(urldecode($param['end_time'])) : 0;
            if ($start_time > 0 && $end_time > 0) {
                $map[] = ['send_time', 'between', "$start_time,$end_time"];
            }
			$model = new MessageList();
            $list = $model->get_list($param,$map,$this->uid);
            return table_assign(0, '', $list);
        } else {
            return view();
        }
    }
	
    //回复和转发消息
    public function reply()
    {
        $id = empty(get_params('id')) ? 0 : get_params('id');
        $type = empty(get_params('type')) ? 0 : get_params('type');
        $model = new MessageList();
        $detail = $model->detail($id);
        if (empty($detail)) {
			throw new \think\exception\HttpException(406, '找不到记录');
        }
        if ($detail['to_uid'] != $this->uid && $detail['from_uid'] != $this->uid) {
            throw new \think\exception\HttpException(406, '找不到记录');
        }
		$send_uids = $detail['from_uid'];
        $send_names = Db::name('Admin')->where('id', '=', $send_uids)->value('name');
		if($type == 2){
			if(!empty($detail['copy_uids'])){
				$send_uids = $detail['copy_uids'].','.$detail['from_uid'];
			}
			$send_uids = parseUids($send_uids,$this->uid);
			$send_names = Db::name('Admin')->where('id', 'in', $send_uids)->column('name');
			$send_names = implode(",", $send_names);
		}
        View::assign('send_uids', $send_uids);
        View::assign('send_names', $send_names);
        View::assign('detail', $detail);
        View::assign('fid', $id);
        View::assign('type', $type);
        return view();
    }
	
	//获取access_token
	function get_access_token($corpid,$corpsecret)
	{
		$url="https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$corpsecret";
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

		$output=curl_exec($ch);

		curl_close($ch);
		$jsoninfo = json_decode($output,true);
		return $jsoninfo["access_token"];
	}


	//获取访问用户敏感信息
	function sendMsg()
	{
		$workchat = get_config('workchat');
		$corpid=$workchat['corpid'];
		$corpsecret=$workchat['corpsecret'];
		$agentid=$workchat['agentid'];
		$content='测试发消息，点击可查看<a href=\"http://work.weixin.qq.com\">具体详情</a>，并回复信息。"';
		//获取access_token
		$accesstoken=$this->get_access_token($corpid,$corpsecret);
		$url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=$accesstoken";
		$data = array(
			'touser' => 'XiaoMing|RuXiBa',
			'agentid' => $agentid,
			'msgtype' => 'textcard',
			"textcard" => array(
				"title" => "您有一条私人消息",
				"description" => "<div class=\"gray\">2016年9月26日</div> <div class=\"normal\">恭喜你抽中iPhone 7一台，领奖码：xxxx</div><div class=\"highlight\">请于2016年10月10日前联系行政同事领取</div>",
				"url" => "http://qy.test.gougucms.com/qiye/note/view/id/3.html",
                "btntxt"=>"详情"
			)
		);
		$res=curl_post($url,json_encode($data));
		$ret= json_decode($res,true);
		$errcode = $ret['errcode'];
		if ($errcode == 0) {
			echo '消息发送成功';
		} else {
			echo '消息发送失败，错误码：' . $errcode;
		}
	}
}
