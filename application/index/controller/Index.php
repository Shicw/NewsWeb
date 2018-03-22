<?php
namespace app\index\controller;
use app\admin\model\Comment;
use app\admin\model\News;
use app\UserBaseController;
use think\Validate;
use think\Db;
class Index extends UserBaseController
{
  public function index(){
      $this->autoLoginout();
    //导航栏加载
    $nav = $this->navList();
    //获取前台登录的用户信息
    $user = session('user');

    //最新发布新闻列表
    $news = new News();
    $newsList = $news->alias('n')->join([
        ['department d','d.id=n.dep_id'],
        ['news_type nt','nt.id=n.type_id'],
    ])->field(['n.*','d.name dep','nt.name type'])->where('nt.delete_time',0)
        ->limit(7)->order('n.create_time desc')->select();
    //最新评论列表
    $comment = new Comment();
    $commentList = $comment->alias('c')->join('news n','n.id=c.news_id')
        ->field(['n.id','n.title','c.comment','c.news_id','c.staff_name','c.create_time'])
        ->where('c.delete_time',0)->limit(4)
        ->order('c.create_time desc')->select();
    //获取主要部门
    $mainDep = Db::name('department')->where(['delete_time'=>0,'is_main'=>1])->select();
    //获取轮播
    $Slide = $news->where(['delete_time'=>0,'is_slide'=>1])->select();
    $this->assign([
        'user' => $user,
        'nav' => $nav,
        'newsList' => $newsList,
        'commentList' => $commentList,
        'mainDep' => $mainDep,
        'Slide' => $Slide
    ]);

    return $this->fetch();
  } 

}