<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/1/28
 * Time: 19:11
 */
namespace app;
use think\Db;
use think\Controller;
class UserBaseController extends Controller
{
    //读取数据库加载导航栏
    public function navList(){
        $navList = Db::name('department')->field(['id','name'])->where('delete_time',0)->select();
        return $navList;
    }
    //重载父类Controller中的success和error方法，将当前操作的信息写进日志表
    public function success($msg = '', $url = null, $data = '', $wait = 1, array $header = [])
    {
        //检测到用户登录，或者充值密码时 执行日志记录
        if(session('user.id') || session('resetUserId')) {
            $logsData = [
                'msg' => $msg,
                'data' => $data,
                'staff_id' => session('user.id')?session('user.id'):session('resetUserId'),
                'create_time' => time()
            ];
            Db::name('logs')->insert($logsData);
        }
        parent::success($msg, $url, $data, $wait, $header);
    }
    public function error($msg = '', $url = null, $data = '', $wait = 1, array $header = [])
    {
        if(session('user.id')) {
            $logsData = [
                'msg' => $msg,
                'data' => $data,
                'staff_id' => session('user.id'),
                'create_time' => time()
            ];
            Db::name('logs')->insert($logsData);
        }
        parent::error($msg, $url, $data, $wait, $header);
    }

    //注销success方法
    public function successLoginout($msg = '', $url = null, $data = '', $wait = 1, array $header = [])
    {
        if(session('user.id')) {
            $logsData = [
                'msg' => $msg,
                'data' => $data,
                'staff_id' => session('user.id'),
                'create_time' => time()
            ];
            Db::name('logs')->insert($logsData);
            //手动注销，online字段置0
            Db::name('staff')->where(['delete_time'=>0,'id'=>session('user.id')])->update(['online'=>0]);
        }
        //清除用户session
        session('user', null);
        parent::success($msg, $url, $data, $wait, $header);
    }
}