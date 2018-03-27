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
class NewsDetailController extends UserBaseController
{
    public function index(){

        //导航栏加载
        $nav = $this->navList();
        //获取前台登录的用户信息
        $user = session('user');

        $id = $this->request->param('id');
        $table = Db::name('news');
        //将news表新闻的view_num字段加1
        $table->where(['id'=>$id,'delete_time'=>0])->setInc('view_num',1);
        //查询新闻详情
        $data = $table->alias('n')->join('staff s','s.id=n.publisher')
            ->field(['n.*','s.name publisher'])
            ->where(['n.id'=>$id,'n.delete_time'=>0])->find();
        //查询当前用户是否收藏该新闻
        $collect = Db::name('collection')->where(['staff_id'=>$user['id'],'news_id'=>$id,'delete_time'=>0])->find();
        //查询新闻评论列表,关联staff表得到头像
        $comment = Db::name('comment c')->join('staff s','s.id=c.staff_id')
            ->field(['c.*','s.avatar'])->where(['c.delete_time'=>0,'news_id'=>$id])
            ->order('c.create_time desc')->paginate(10);
        //获取分页显示
        $page = $comment->render();
        //查询点赞数
        $this->assign([
            'data'=>$data,
            'nav' => $nav,
            'user' => $user,
            'comment' => $comment,
            'collect' => $collect,
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
                $this->success('评论成功！',url('index/NewsDetailController/index',array('id'=>$data['newsId'])),'新闻评论：'.$data['title']);
            }else{
                $this->error('评论失败！');
            }
        }else {
            $this->error("请求错误!");
        }
    }
    //收藏 0：未登录 1：收藏成功 2：收藏失败 3：取消收藏 4：取消失败
    public function collect($id){
        $userId = session('user.id');
        $code = null;
        //如果用户未登录，返回0
        if (!isset($userId)){
            return json(['code' => 0]);
        }else{
            $news = Db::name('news')->where(['delete_time'=>0,'id'=>$id])->find();
            //查询当前用户是否收藏过改新闻，是则取消收藏，否则添加收藏
            $table = Db::name('collection');
            $find = $table->where(['staff_id'=>$userId,'news_id'=>$id,'delete_time'=>0])->find();
            if($find){//取消收藏
                $result = $table->where(['staff_id'=>$userId,'news_id'=>$id,'delete_time'=>0])->setField('delete_time',time());
                $code = $result ? 3 : 4;
                //操作成功写入日志，此处是返回给ajax，不方便调用success方法来写日志
                if($code == 3) Db::name('logs')->insert(['msg'=>'取消收藏成功！','data'=>'取消收藏：'.$news['title'],'staff_id'=>$userId]);
            }else{//添加收藏
                $result = $table->insert(['staff_id'=>$userId,'news_id'=>$id,'create_time'=>time()]);
                $code = $result ? 1 : 2;
                if($code == 1) Db::name('logs')->insert(['msg'=>'添加收藏成功！','data'=>'添加收藏：'.$news['title'],'staff_id'=>$userId]);

            }
            return json(['code' => $code]);
        }
    }
    //点击头像查看用户信息 ajax
    public function showStaff($id){
        $data = Db::name('staff s')->join([
            ['department d','d.id=s.dep_id'],
            ['jobs j','j.id=s.job_id'],
        ])->field(['s.email','s.mobile','d.name dep','j.name job'])
            ->where(['s.delete_time'=>0,'s.id'=>$id])->find();
        $code = $data ? 1 : 0;
        return json([
            'code' => $code,
            'data' => $data
        ]);
    }

}