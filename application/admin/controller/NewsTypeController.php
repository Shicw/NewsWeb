<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/4
 * Time: 20:39
 */
namespace app\admin\controller;
use app\AdminBaseController;
use app\admin\model\NewsType;
use think\Db;
use think\Validate;
class NewsTypeController extends AdminBaseController
{
    private $model;
    //初始化模型对象
    public function _initialize(){
        parent::_initialize();
        $this->model = new  NewsType();
    }

    public function index(){
        $this->isLogin();

        //关键字查询
        $request = input('request.');
        $keyword = '';
        $conditions = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $conditions['name|description'] = ['like', "%$keyword%"];
        }
        //过滤被删除的记录
        $conditions['delete_time'] = 0;
        //查询类别列表
        $typeList = $this->model->where($conditions)->paginate(10, false, [
            'query' => ['keyword' => $keyword]
        ]);
        $page = $typeList->render();
        $this->assign([
            'typeList'=>$typeList,
            'page' => $page,
        ]);
        return $this->fetch();
    }
    //添加类别
    public function add(){
        $this->isLogin();

        return $this->fetch();
    }
    //添加类别提交
    public function addPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:6|max:21',
                'description' => 'chs|max:60',
            ]);
            $validate->message([
                'name.require' => '新闻类别不能为空',
                'name.chs' => '新闻类别只能为汉字',
                'name.min' => '新闻类别不能少于两个汉字',
                'name.max' => '新闻类别太长',
                'description.chs' => '备注只能为中文',
                'description.max' => '备注太长'
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $update = $this->model->insert($data);
            if ($update){
                $this->success('添加成功！',url('admin/NewsTypeController/index'),'添加新闻类别:'.$data['name']);
            }else{
                $this->error('添加失败！');
            }
        }else{
            $this->error('请求错误');
        }
    }
    //新闻类别修改
    public function edit(){
        $this->isLogin();
        $id = $this->request->param('id');
        $typeData = $this->model->where(['delete_time'=>0,'id'=>$id])->find();

        $this->assign('typeData',$typeData);
        return $this->fetch();
    }
    //新闻类别修改提交
    public function editPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:6|max:21',
                'description' => 'chs|max:60',
            ]);
            $validate->message([
                'name.require' => '新闻类别不能为空',
                'name.chs' => '新闻类别只能为汉字',
                'name.min' => '新闻类别不能少于两个汉字',
                'name.max' => '新闻类别太长',
                'description.chs' => '备注只能为中文',
                'description.max' => '备注太长'
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $id = $this->request->param('id');
            $update = $this->model->where('id',$id)->update($data);
            if ($update){
                $this->success('修改成功！',url('admin/NewsTypeController/index'),'修改新闻类别:'.$data['name']);
            }else{
                $this->error('没有新的修改信息！');
            }
        }else{
            $this->error('请求错误');
        }
    }
    //新闻类别删除
    public function delete(){
        $id = $this->request->param('id');
        //查询记录是否存在
        $find = $this->model->where('id',$id)->find();
        if ($find){
            $result = $this->model->where('id',$id)->update(['delete_time'=>time()]);
            if ($result){
                $this->success('删除成功！',url('admin/NewsTypeController/index'),'删除新闻类别:'.$find['name']);
            }else{
                $this->error('删除失败！','','删除新闻类别:'.$find['name']);
            }
        }else{
            $this->error('删除失败,该新闻类别不存在','','删除新闻类别:'.$find['name']);
        }
    }
}