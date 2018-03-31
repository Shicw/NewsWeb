<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/1/31
 * Time: 21:12
 */
namespace app;
use think\Db;
use think\Controller;
class AdminBaseController extends Controller
{
    //重载父类Controller中的success和error方法，将当前操作的信息写进日志表
    public function success($msg = '', $url = null, $data = '', $wait = 1, array $header = [])
    {
        //检测到用户登录，才执行日志记录
        if(session('admin.id')) {
            $logsData = [
                'msg' => $msg,
                'data' => $data,
                'staff_id' => session('admin.id'),
                'create_time' => time()
            ];
            if ($msg != '' && $data != '') Db::name('logs')->insert($logsData);
        }
        parent::success($msg, $url, $data, $wait, $header);
    }
    //public function error($msg = '', $url = null, $data = '', $wait = 1, array $header = [])
    //{
    //    //检测到用户登录，才执行日志记录
    //    if(session('admin.id')) {
    //        $logsData = [
    //            'msg' => $msg,
    //            'data' => $data,
    //            'staff_id' => session('admin.id'),
    //            'create_time' => time()
    //        ];
    //        if ($msg != '' && $data != '') Db::name('logs')->insert($logsData);
    //    }
    //    parent::error($msg, $url, $data, $wait, $header);
    //}

    //注销success方法
    public function successLoginout($msg = '', $url = null, $data = '', $wait = 1, array $header = [])
    {
        //检测到用户登录，才执行日志记录
        if(session('admin.id')) {
            $logsData = [
                'msg' => $msg,
                'data' => $data,
                'staff_id' => session('admin.id'),
                'create_time' => time()
            ];
            Db::name('logs')->insert($logsData);

        }
        //清除用户session
        session('admin', null);
        parent::success($msg, $url, $data, $wait, $header);
    }
    //后台检测用户是否登录,防止通过url直接进入后台页面
    public function isLogin(){
        $admin = session('admin');
        if (!$admin) $this->error('未登录！',url('admin/index/login'));
    }

}