<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/3/19
 * Time: 15:21
 */
namespace app\admin\controller;
use app\admin\model\Config;
use app\AdminBaseController;
use think\Db;
use think\Validate;
class ConfigController extends AdminBaseController
{
    private $model;

    //初始化模型对象
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Config();
    }
    //开启/关闭注册功能
    public function openRegister(){
        $this->isLogin();

        $openRegister = $this->model->where('key','open_register')->find();
        $this->assign('openRegister',$openRegister['value']);
        return $this->fetch();
    }
    //开启关闭注册提交
    public function openRegisterPost(){
        $status = $this->request->param('openRegister');
        $result = Db::name('config')->where('key','open_register')->update(['value'=>$status]);
        $msg = $status ? "开启" : "关闭";
        if($result){
            $this->success($msg.'注册功能成功！','',$msg.'注册功能');
        }else{
            $this->error('没有修改配置!');
        }
    }
    //验证码邮箱配置
    public function mailer(){
        $this->isLogin();

        $mailer = $this->model->field(['key','value'])->where('module','mailer')->select();
        $this->assign('mailer',$mailer);
        return $this->fetch();

    }
    //验证码邮箱配置提交
    public function mailerPost(){
        $data = $this->request->post();
        $config= [
            ['id'=>3,'key'=>'from_name','value'=>$data['fromname']],
            ['id'=>8,'key'=>'username','value'=>$data['username']],
            ['id'=>9,'key'=>'password','value'=>$data['password']],
            ['id'=>4,'key'=>'host','value'=>$data['host']],
            ['id'=>5,'key'=>'port','value'=>$data['port']],
            ['id'=>6,'key'=>'smtp_secure','value'=>$data['secure']],
            ['id'=>7,'key'=>'subject','value'=>$data['subject']],
            ['id'=>2,'key'=>'body','value'=>$data['body']],
        ];
        $result = $this->model->saveAll($config);
        if(!empty($result)){
            $this->success('邮箱配置修改成功',url('admin/ConfigController/mailer'),'修改邮箱配置');
        }else{
            $this->error('邮箱配置修改失败');
        }
    }
}