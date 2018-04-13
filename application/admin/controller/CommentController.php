<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/17
 * Time: 17:54
 */
namespace app\admin\controller;
use app\admin\model\Comment;
use app\AdminBaseController;
use think\Db;
use think\Validate;
class CommentController extends AdminBaseController
{
    private $model;

    //初始化模型对象
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Comment();
    }
    //评论视图
    public function index(){
        $this->isLogin();
        //关键字查询
        $request = input('request.');
        $keyword = '';
        $conditions = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $conditions['n.title|c.comment'] = ['like', "%$keyword%"];
        }

        $conditions['c.delete_time'] = 0;
        //查询所有评论列表
        //使用模型操作数据库，返回的时间戳可以被框架转成字符串；Db类操作数据库需要手动转成字符串
        $commentList = $this->model->alias('c')->join([
            ['news n','n.id=c.news_id'],
            ['department d','d.id=n.dep_id'],
            ['news_type nt','nt.id=n.type_id']
        ])->field(['c.*','n.title','d.name dep','nt.name type'])
            ->where($conditions)->order('c.create_time desc')->paginate(10, false, [
            'query' => [
                'keyword' => $keyword,
            ]
        ]);
        //获取分页显示
        $page = $commentList->render();
        $this->assign([
            'commentList' => $commentList,
            'page' => $page
        ]);
        return $this->fetch();
    }
    //删除评论
    public function delete(){
        $id = $this->request->param('id');
        //查询评论是否存在
        $find = $this->model->where('id',$id)->find();
        if ($find){
            $result = $this->model->where('id',$id)->update(['delete_time'=>time()]);
            if ($result){
                $this->success('评论删除成功!',url('admin/CommentController/index'),'删除评论:'.$find['comment']);
            }else{
                $this->error('评论删除失败!');
            }
        }else{
            $this->error('评论删除失败,记录不存在!');
        }
    }
}