<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/18
 * Time: 15:11
 */
namespace app\admin\controller;
use app\AdminBaseController;
use think\Db;
use think\Validate;
class SlideController extends AdminBaseController
{
    public function index(){
        $this->isLogin();
        $table = Db::name('news n');
        //获取当前轮播列表
        $slideList = $table->join([
            ['department d','d.id=n.dep_id'],
            ['news_type nt','nt.id=n.type_id'],
        ])->field(['n.title','n.id','n.is_slide','d.name dep','nt.name type'])
            ->where(['n.delete_time'=>0,'n.is_slide'=>1])->select();

        //获取可设置轮播的列表
        //部门查询
        $dep_id = '';
        if (!empty($request['dep_id'])) {
            $dep_id = $request['dep_id'];
            $conditions['n.dep_id'] = $dep_id;
        }
        $depList = Db::name('department')->where('delete_time',0)->select();
        //类别查询
        $type_id = '';
        if (!empty($request['type_id'])) {
            $type_id = $request['type_id'];
            $conditions['n.type_id'] = $type_id;
        }
        $typeList = Db::name('news_type')->where('delete_time',0)->select();
        //过滤被删除的记录
        $conditions['n.delete_time'] = 0;
        //过滤没有图片的新闻
        $conditions['n.img'] = ['<>',''];
        //过滤is_slide=1的记录
        $conditions['n.is_slide'] = 0;

        $newsList = $table->join([
            ['department d','d.id=n.dep_id'],
            ['news_type nt','nt.id=n.type_id'],
        ])->field(['n.title','n.id','n.is_slide','d.name dep','nt.name type'])
            ->where($conditions)->paginate(6,false,[
                'query' => [
                    'dep_id' => $dep_id,
                    'type_id' => $type_id
                    ]
                ]);
        $page = $newsList->render();
        $this->assign([
            'slideList'=>$slideList,
            'depList' => $depList,
            'typeList' => $typeList,
            'dep_id' => $dep_id,
            'type_id' => $type_id,
            'newsList' => $newsList,
            'page' => $page
        ]);
        return $this->fetch();
    }
    //取消轮播
    public function cancelSlide(){
        $id = $this->request->param('id');
        $table = Db::name('news');
        $result = $table->where(['delete_time'=>0,'id'=>$id])->update(['is_slide'=>0]);
        //查询新闻标题
        $news = $table->where(['delete_time'=>0,'id'=>$id])->find();
        if ($result){
            $this->success('取消轮播成功！',url('admin/SlideController/index'),'取消轮播新闻:'.$news['title']);
        }else{
            $this->error('取消轮播失败！');
        }
    }
    //设置轮播
    public function setSlide(){
        $id = $this->request->param('id');
        $table = Db::name('news');
        $result = $table->where(['delete_time'=>0,'id'=>$id])->update(['is_slide'=>1]);
        //查询新闻标题
        $news = $table->where(['delete_time'=>0,'id'=>$id])->find();
        if ($result){
            $this->success('设置轮播成功！',url('admin/SlideController/index'),'设置轮播新闻:'.$news['title']);
        }else{
            $this->error('设置轮播失败！');
        }
    }
}