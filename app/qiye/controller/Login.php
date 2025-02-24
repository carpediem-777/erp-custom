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
use think\facade\Session;
use app\attendance\model\AttendanceMonth;
use think\facade\View;

class Login extends BaseController
{
	/**
     * 构造函数
     */
    public function __construct()
    {
        $this->workchat = get_config('workchat');
    }
	//登录
	public function index()
    {	
        return view();
	}
	
    //提交登录
    public function login_submit()
    {
        $param = get_params();
        $admin = Db::name('Admin')->where(['username' => $param['username']])->find();
        if (empty($admin)) {
            $admin = Db::name('Admin')->where(['mobile' => $param['username']])->find();
            if (empty($admin)) {
                return to_assign(1, '用户名或手机号码错误');
            }
        }
        $param['pwd'] = set_password($param['password'], $admin['salt']);
        if ($admin['pwd'] !== $param['pwd']) {
            return to_assign(1, '用户或密码错误');
        }
        if ($admin['status'] != 1) {
            return to_assign(1, '该用户禁止登录,请与管理者联系');
        }
        $data = [
			'is_lock' => 0,
            'last_login_time' => time(),
            'last_login_ip' => request()->ip(),
            'login_num' => $admin['login_num'] + 1,
        ];
        Db::name('admin')->where(['id' => $admin['id']])->update($data);
        $session_admin = get_config('app.session_admin');
        Session::set($session_admin, $admin['id']);
        $token = make_token();
        set_cache($token, $admin, 7200);
        $admin['token'] = $token;
		$logdata = [
			'uid' => $admin['id'],
            'type' => 'login',
            'action' => '登录',
            'subject' => '系统',
			'param_id'=>$admin['id'],
			'param'=>'[]',
            'ip' => request()->ip(),
			'create_time' => time()
        ];
		Db::name('AdminLog')->strict(false)->field(true)->insert($logdata);
        return to_assign(0, '登录成功', ['uid' => $admin['id']]);
    }
	

	//登录错误
	public function error($m)
    {	
		View::assign('mobile', $m);
        return view();
	}
	
	//重置密码提示
	public function restpassword()
    {	
        return view();
	}
	
	//利用access_token、code读取userid
	function getUserInfoByAuth($accesstoken,$code)
	{
		//$url="https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=$accesstoken&code=$code";
		$url="https://qyapi.weixin.qq.com/cgi-bin/auth/getuserinfo?access_token=$accesstoken&code=$code";
		$content=curl_get($url);
		$ret= json_decode($content,true);
		return $ret;
	}
	
	//利用access_token、userid读取用户身份详细信息
	function getUserInfo($accesstoken,$userid)
	{
		 $url="https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=$accesstoken&userid=$userid";
		 $content=curl_get($url);
		 $ret= json_decode($content,true);
		 return $ret;
	}
	
	//获取访问用户敏感信息
	function getUserDetail($accesstoken,$user_ticket)
	{
		 $url="https://qyapi.weixin.qq.com/cgi-bin/auth/getuserdetail?access_token=$accesstoken";
		 $data=array(
			'user_ticket'=>$user_ticket
		 );
		 $content=curl_post($url,json_encode($data));
		 $ret= json_decode($content,true);
		 return $ret;
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
	
	//登录
	public function login()
    {	
		$wxwork = is_wxwork();
		if($wxwork){
			$appId = $this->workchat['corpid'];
			$redirectUri = urlencode($this->workchat['redirectUri']);
			$scope = 'snsapi_login'; // 登录授权作用域,自动授权
			if($this->workchat['authorization']){
				$scope = 'snsapi_privateinfo'; // 登录授权作用域：手动授权;
			}
			$agentid = $this->workchat['agentid']; // agentid
			// 构造授权链接
			//$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appId&redirect_uri=$redirectUri&response_type=code&scope=$scope&state=STATE#wechat_redirect";
			$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appId&redirect_uri=$redirectUri&response_type=code&scope=$scope&state=STATE&agentid=$agentid#wechat_redirect";
			// 重定向到授权链接
			return redirect($url);
		}
		else{
			return redirect('/qiye/login/index');
		}
	}
	
	public function callback()
	{
		$corpid=$this->workchat['corpid'];
		$corpsecret=$this->workchat['corpsecret'];
		//通过第“2”步骤获取code直接用$_GET['code'];方式取
		$code = input('code');
		//获取access_token
		$accesstoken=$this->get_access_token($corpid,$corpsecret);
		//测试code是否存在
		if (!empty($code)){
			$user=$this->getUserInfoByAuth($accesstoken,$code);
			//var_dump($user);exit;
			//$userid=$user["UserId"];
			//$deviceid=$user["DeviceId"];
			if($this->workchat['authorization'] == true){
				$user_ticket=$user["user_ticket"];
			}
			$userid=$user["userid"];
			//测试企业用户是否存在
			if (empty($userid)){
				return to_assign(1, '该用户尚未关注企业微信');
			}
			if($this->workchat['authorization'] == true){
				$userinfo=$this->getUserDetail($accesstoken,$user_ticket);
				$mobile = $userinfo["mobile"];
				$admin = Db::name('Admin')->where(['mobile' => $mobile])->find();
				if(empty($admin)){
					return redirect('/qiye/login/error?m='.$mobile);
					//return to_assign(1, '企业微信手机号码【'.$mobile.'】与系统用户的手机号不匹配，请联系管理员');
				}
				$data = [
					'is_lock' => 0,
					'userid' => $userid,
					'last_login_time' => time(),
					'last_login_ip' => request()->ip(),
					'login_num' => $admin['login_num'] + 1,
				];
				Db::name('admin')->where(['id' => $admin['id']])->update($data);
				$session_admin = get_config('app.session_admin');
				Session::set($session_admin, $admin['id']);
				$logdata = [
					'uid' => $admin['id'],
					'type' => 'login',
					'action' => '企业微信登录',
					'subject' => '系统',
					'param_id'=>$admin['id'],
					'param'=>'[]',
					'ip' => request()->ip(),
					'create_time' => time()
				];
				Db::name('AdminLog')->strict(false)->field(true)->insert($logdata);
				return redirect('/qiye/index/index');
                // exit;
				//return to_assign(0, '登录成功', ['uid' => $admin['id']]);
			}
			else{
				$userinfo=$this->getUserInfo($accesstoken,$userid);
			}
			//var_dump($userinfo);
			//$username= $userinfo["name"];
		}
	}
	
	//退出登录
    public function login_out()
    {
        $session_admin = get_config('app.session_admin');
        Session::delete($session_admin);
        return redirect('/qiye/login/index');
    }
}
