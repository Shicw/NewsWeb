<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/4
 * Time: 16:17
 */
namespace app\admin\controller;
use app\admin\model\Jobs;
use app\AdminBaseController;
use think\Db;
use think\Validate;
class JobsController extends AdminBaseController
{
    private $model;
    //初始化模型对象
    public function _initialize(){
        parent::_initialize();
        $this->model = new Jobs();
    }

    public function index(){
        $this->isLogin();

        //关键字查询
        $request = input('request.');
        $keyword = '';
        $conditions = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $conditions['j.name'] = ['like', "%$keyword%"];
        }
        //部门查询
        $dep_id = '';
        if (!empty($request['dep_id'])) {
            $dep_id = $request['dep_id'];
            $conditions['j.dep_id'] = $dep_id;
        }
        $depList = Db::name('department')->where('delete_time',0)->select();
        //过滤被删除的记录
        $conditions['j.delete_time'] = 0;
        //查询岗位列表
        $jobsList = $this->model->alias('j')->join('department d','d.id=j.dep_id')
            ->field(['j.id','j.name name','d.name dep'])->where($conditions)->paginate(10, false, [
            'query' => [
                'keyword' => $keyword,
                'dep_id' => $dep_id
            ]
        ]);
        //获取分页显示
        $page = $jobsList->render();
        $this->assign([
            'jobsList'=>$jobsList,
            'page' => $page,
            'depList' => $depList,
            'dep_id' => $dep_id,
        ]);
        return $this->fetch();
    }
    //岗位添加
    public function add(){
        $this->isLogin();

        $depList = Db::name('department')->where('delete_time',0)->select();
        $this->assign('depList',$depList);
        return $this->fetch();
    }
    //岗位添加提交
    public function addPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:6|max:21',
                'dep_id' => 'require|number',
            ]);
            $validate->message([
                'name.require' => '岗位名称不能为空',
                'name.chs' => '岗位名称只能为汉字',
                'name.min' => '岗位名称不能少于两个汉字',
                'name.max' => '岗位名称太长',
                'dep_id.require' => '所属部门不能为空',
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $update = $this->model->insert($data);
            if ($update){
                $this->success('添加成功！',url('admin/JobsController/index'),'添加岗位:'.$data['name']);
            }else{
                $this->error('添加失败！');
            }
        }else{
            $this->error('请求错误');
        }

    }
    //岗位修改
    public function edit(){
        $this->isLogin();
        $id = $this->request->param('id');
        $jobsData = $this->model->where(['delete_time'=>0,'id'=>$id])->find();

        $depList = Db::name('department')->where('delete_time',0)->select();
        $this->assign([
            'depList' => $depList,
            'jobsData' => $jobsData
        ]);
        return $this->fetch();
    }
    //岗位修改提交
    public function editPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:6|max:21',
                'dep_id' => 'require|number',
            ]);
            $validate->message([
                'name.require' => '岗位名称不能为空',
                'name.chs' => '岗位名称只能为汉字',
                'name.min' => '岗位名称不能少于两个汉字',
                'name.max' => '岗位名称太长',
                'dep_id.require' => '所属部门不能为空',
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $id = $this->request->param('id');
            $update = $this->model->where('id',$id)->update($data);
            if ($update){
                $this->success('修改成功！',url('admin/JobsController/index'),'修改岗位:'.$data['name']);
            }else{
                $this->error('没有新的修改信息！');
            }
        }else{
            $this->error('请求错误！');
        }
    }
    //岗位删除
    public function delete(){
        $id = $this->request->param('id');
        //查询记录是否存在
        $find = $this->model->where('id',$id)->find();
        if ($find){
            $result = $this->model->where('id',$id)->update(['delete_time'=>time()]);
            if ($result){
                $this->success('删除成功！',url('admin/JobsController/index'),'删除岗位:'.$find['name']);
            }else{
                $this->error('删除失败！');
            }
        }else{
            $this->error('删除失败,该岗位不存在！');
        }
    }
}