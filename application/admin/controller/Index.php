<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/1/31
 * Time: 20:48
 */
namespace app\admin\controller;
use app\admin\model\Staff;
use app\AdminBaseController;
use think\Config;
use think\Db;
class Index extends AdminBaseController
{

    public function login(){
        return $this->fetch();
    }
    //后台登录验证
    public function doLogin(){
        $data = $this->request->param();
        $username = $data['username'];
        $password = md5($data['password']);
        $staff = new Staff();
        $result = $staff->field(['username','name','password','id','type'])->where(['username'=>$username,'delete_time'=>0])->find();
        if ($result) {
            if ($result['type'] == 0 || $result['type'] == 2) {
                //若新闻发布者登录，先判断发布者权限是否被暂停
                if ($result['type'] == 2) {
                    $find = Db::name('newsmaker')->where(['staff_id'=>$result['id'],'delete_time'=>0])->find();
                    if ($find['status'] == 0) $this->error('发布者权限被禁用，不能登录！');
                }

                if ($result['username'] == $username && $result['password'] == $password) {
                    //将用户信息写进session
                    session('admin', $result->getData());

                    //更新用户最后登录时间
                    $staff->where('id', $result['id'])->update(['last_login_time' => time()]);
                    $this->success('登录成功！', url('admin/index/index'), '后台登录');
                } else {
                    $this->error('用户名密码错误！');
                }
            } else {
                $this->error('此用户无权登录！');
            }
        }else{
            $this->error('不存在此用户');
        }
    }
    //后台注销
    public function loginout(){
        $this->successLoginout('注销成功！',url('admin/index/login'),'后台注销');
    }
    //加载主页框架
    public function index(){
        $this->isLogin();
        return $this->fetch();
    }
    public function left(){
        $this->isLogin();
        //将type赋值给模板，判断是admin还是新闻发布者，显示不同的slidenav
        $staffType = session('admin.type');
        $this->assign('staffType',$staffType);
        return $this->fetch();
    }
    public function main(){
        $this->isLogin();

        return $this->fetch();
    }
    public function top(){
        $this->isLogin();
        $admin = session('admin');
        $this->assign('admin',$admin);
        return $this->fetch();
    }

    //ajax获取首页仪表盘数据
    public function dashboard(){
        //获取当日0点时间戳
        $todayTime = strtotime(date('Y-m-d',time()));
        //查询"今日评论数"
        $commentCount = Db::name('comment')->where(['delete_time'=>0,'create_time'=>['>=',$todayTime]])->count();
        //查询"今日新闻数"
        $newsCount = Db::name('news')->where(['delete_time'=>0,'create_time'=>['>=',$todayTime]])->count();
        //查询"当前在线用户"
        $onlineCount = Db::name('staff')->where(['delete_time'=>0,'online'=>1])->count();
        $data = [
            'comment' => $commentCount,
            'news' => $newsCount,
            'online' => $onlineCount,
        ];
        return json(["data" => $data]);
    }
}
