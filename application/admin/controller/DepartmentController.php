<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/4
 * Time: 16:17
 */
namespace app\admin\controller;
use app\admin\model\Department;
use app\AdminBaseController;
use think\Db;
use think\Validate;
class DepartmentController extends AdminBaseController
{
    private $model;
    //初始化模型对象
    public function _initialize(){
        parent::_initialize();
        $this->model = new  Department();
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
        //查询部门列表
        $depList = $this->model->where($conditions)->paginate(10, false, [
            'query' => ['keyword' => $keyword]
        ]);
        //查询is_main=1的记录数量，当记录>4，则不能新增主要部门
        $mainCount = $this->model->where(['delete_time'=>0,'is_main'=>1])->count();
        //获取分页显示
        $page = $depList->render();
        $this->assign([
            'depList'=>$depList,
            'page' => $page,
            'mainCount' => $mainCount
        ]);
        return $this->fetch();
    }
    //部门添加
    public function add(){
        $this->isLogin();
        return $this->fetch();
    }
    //部门添加提交
    public function addPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:9|max:21',
                'description' => 'chs|max:60',
            ]);
            $validate->message([
                'name.require' => '部门名称不能为空',
                'name.chs' => '部门名称只能为汉字',
                'name.min' => '部门名称不能少于三个汉字',
                'name.max' => '部门名称太长',
                'description.chs' => '备注只能为中文',
                'description.max' => '备注太长'
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $update = $this->model->insert($data);
            if ($update){
                $this->success('添加成功！',url('admin/DepartmentController/index'),'添加部门:'.$data['name']);
            }else{
                $this->error('添加失败！');
            }
        }else{
            $this->error('请求错误');
        }

    }
    //部门修改
    public function edit(){
        $this->isLogin();
        $id = $this->request->param('id');
        $depData = $this->model->where(['delete_time'=>0,'id'=>$id])->find();
        $this->assign('depData',$depData);
        return $this->fetch();
    }
    //部门修改提交
    public function editPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:9|max:21',
                'description' => 'chs|max:60',
            ]);
            $validate->message([
                'name.require' => '部门名称不能为空',
                'name.chs' => '部门名称只能为汉字',
                'name.min' => '部门名称不能少于两个汉字',
                'name.max' => '部门名称太长',
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
                $this->success('修改成功！',url('admin/DepartmentController/index'),'修改部门:'.$data['name']);
            }else{
                $this->error('没有新的修改信息！');
            }
        }else{
            $this->error('请求错误');
        }
    }
    //部门删除
    public function delete(){
        $id = $this->request->param('id');
        //查询记录是否存在
        $find = $this->model->where(['id'=>$id,'delete_time'=>0])->find();
        if ($find){
            $result = $this->model->where('id',$id)->update(['delete_time'=>time()]);
            if ($result){
                $this->success('删除成功！',url('admin/DepartmentController/index'),'删除部门:'.$find['name']);
            }else{
                $this->error('删除失败！');
            }
        }else{
            $this->error('删除失败,该部门不存在！');
        }
    }
    //设为主要部门
    public function isMain(){
        $id = $this->request->param('id');
        //查询记录是否存在
        $find = $this->model->where(['id'=>$id,'delete_time'=>0])->find();
        if ($find){
            $result = $this->model->where('id',$id)->update(['is_main'=>1]);
            if ($result){
                $this->success('设置成功！',url('admin/DepartmentController/index'),'设置主要部门:'.$find['name']);
            }else{
                $this->error('设置失败！');
            }
        }else{
            $this->error('设置失败,该部门不存在！');
        }
    }
    //取消主要部门
    public function notMain(){
        $id = $this->request->param('id');
        //查询记录是否存在
        $find = $this->model->where(['id'=>$id,'delete_time'=>0])->find();
        if ($find){
            $result = $this->model->where('id',$id)->update(['is_main'=>0]);
            if ($result){
                $this->success('取消成功！',url('admin/DepartmentController/index'),'取消主要部门:'.$find['name']);
            }else{
                $this->error('取消失败！');
            }
        }else{
            $this->error('取消失败,该部门不存在！');
        }
    }
}