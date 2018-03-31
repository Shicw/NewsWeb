<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/1
 * Time: 14:06
 */
namespace app\admin\controller;
use app\admin\model\Staff;
use app\AdminBaseController;
use think\Db;
class ChangePasswordController extends AdminBaseController
{
    public function index(){
        $this->isLogin();
        return $this->fetch();
    }
    //修改密码提交
    public function changePost(){
        $data = $this->request->param();
        $username = $data['username'];
        $passwordOld = md5($data['passwordOld']);
        $passwordNew = md5($data['passwordNew']);
        $passwordNewAgain = md5($data['passwordNewAgain']);

        $adminId = session('admin.id');
        $staff = new Staff();
        $adminData = $staff->field(['username','password'])->where('id',$adminId)->find();
        if ($username != $adminData['username']) $this->error('用户名不正确');
        if ($passwordOld != $adminData['password']) $this->error('旧密码不正确');
        if ($passwordNewAgain !== $passwordNew) $this->error('两次输入的密码不一致');

        $updatePwd = $staff->where('id',$adminId)->update(['password'=>$passwordNew]);
        if ($updatePwd){
            $this->success('密码修改成功！',url('admin/index/main'),'密码修改');
        }else{
            $this->error('密码修改失败，请重试！');
        }

    }
}