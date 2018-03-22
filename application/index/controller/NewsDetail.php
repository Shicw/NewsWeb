<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/4
 * Time: 22:25
 */
namespace app\index\controller;
use app\UserBaseController;
use think\Validate;
use think\Db;
class NewsDetail extends UserBaseController
{
    public function index(){

        //导航栏加载
        $nav = $this->navList();
        //获取前台登录的用户信息
        $user = session('user');

        $id = $this->request->param('id');
        //将news表新闻的view_num字段加1
        Db::name('news')->where(['id'=>$id,'delete_time'=>0])->setInc('view_num',1);
        //查询新闻详情
        $data = Db::name('news n')->join([
            ['department d','d.id=n.dep_id'],
            ['news_type nt','nt.id=n.type_id'],
            ['staff s','s.id=n.publisher']
        ])->field(['n.*','d.name dep','nt.name type','s.name publisher'])
            ->where(['n.id'=>$id,'n.delete_time'=>0])->find();
        //查询新闻评论列表,关联staff表得到头像
        $comment = Db::name('comment c')->join('staff s','s.id=c.staff_id')
            ->field(['c.*','s.avatar'])->where(['c.delete_time'=>0,'news_id'=>$id])
            ->order('c.create_time desc')->paginate(10);
        //获取分页显示
        $page = $comment->render();
        $this->assign([
            'data'=>$data,
            'nav' => $nav,
            'user' => $user,
            'comment' => $comment,
            'page' => $page
        ]);
        return $this->fetch();
    }
    //发表评论
    public function comment(){
        //PHP后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'comment' => 'require',
            ]);
            $validate->message([
                'comment.require'   => '评论不能为空',
            ]);
            $data = $this->request->post();
            //$newsId = $this->request->param('newsId');
            //$comment = $this->request->param('comment');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            //评论数据数组
            $commentData = [
                'news_id' => $data['newsId'],
                'comment' => $data['comment'],
                'staff_id' => session('user.id'),
                'staff_name' => session('user.name'),
                'create_time' => time(),
            ];
            //将评论写入comment表
            $result1 = Db::name('comment')->insert($commentData);
            //将news表中新闻comment_num字段加1
            $result2 = Db::name('news')->where(['id'=>$data['newsId'],'delete_time'=>0])->setInc('comment_num',1);
            if ($result1 && $result2){
                $this->success('评论成功！',url('index/NewsDetail/index',array('id'=>$data['newsId'])),'新闻评论：'.$data['title']);
            }else{
                $this->error('评论失败！');
            }
        }else {
            $this->error("请求错误!");
        }
    }
}