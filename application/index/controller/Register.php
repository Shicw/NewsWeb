<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/1/28
 * Time: 18:52
 */
namespace app\index\controller;
use app\UserBaseController;
use think\Validate;
use think\Db;
class Register extends UserBaseController
{
    public function index(){
        //导航栏
        $nav = $this->navList();
        //查询用户注册功能是否关闭
        $openRegister = Db::name('config')->where('key','open_register')->find();
        $this->assign('nav',$nav);
        $this->assign('openRegister',$openRegister['value']);
        return $this->fetch();
    }
    public function doRegister(){
        //PHP验证表单
        if ($this->request->isPost()) {
            $validate = new Validate([
                'username' => 'require|alphaNum|min:4|max:8',
                'password' => 'require|alphaDash|min:6|max:12',
                'mobile'   => 'require|number|min:11|max:11',
                'captcha'  => 'require'
            ]);
            $validate->message([
                'username.require'   => '用户名不能为空',
                'username.alphaNum'  => '用户名只能输入字母和数字',
                'password.require'   => '密码不能为空',
                'password.alphaDash' => '密码只能输入只允许字母、数字和-、_',
                'mobile.require'     => '手机号不能为空',
                'mobile.min'         => '手机号位数不符合',
                'mobile.max'         => '手机号位数不符合',
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            if (!cmf_captcha_check($data['captcha'])){
                $this->error('验证码错误！');
            }
            $userData = [
                'username' => $data['username'],
                'password' => md5($data['password']),
                'mobile' => $data['mobile'],
                'create_time' => time(),
            ];
            $table = Db::name("staff");
            //将注册信息写入数据表并获取新增纪录id
            $userId = $table->insertGetId($userData);
            //根据ID查询出用户信息，并更新前台登录的用户信息
            $data   = $table->where('id', $userId)->find();
            session('user', $data);

            if ($userId && $data) {
                //注册成功后更新用户最后登录时间,online字段置1，跳转到个人中心
                Db::name('staff')->where('id',$userId)->update(['last_login_time'=>time(),'online'=>1]);
                $this->success('注册成功！已为您自动登录，请修改您的个人资料', url('index/Person/index'),'用户注册');
            }
        }else {
            $this->error("请求错误");
        }
    }
}