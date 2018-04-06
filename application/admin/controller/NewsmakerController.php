<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/4
 * Time: 17:33
 */
namespace app\admin\controller;
use app\admin\model\Newsmaker;
use app\AdminBaseController;
use think\Db;
use think\Validate;
class NewsmakerController extends AdminBaseController
{
    private $model;
    //初始化模型对象
    public function _initialize(){
        parent::_initialize();
        $this->model = new  Newsmaker();
    }

    public function index(){
        $this->isLogin();

        //关键字查询
        $request = input('request.');
        $keyword = '';
        $conditions = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $conditions['s.name'] = ['like', "%$keyword%"];
        }
        //过滤被删除的记录
        $conditions['n.delete_time'] = 0;
        //查询发布者列表
        $newsList = $this->model->alias('n')->join([
            ['staff s','s.id=n.staff_id'],
            ['department d','d.id=s.dep_id'],
            ['jobs j','j.id=s.job_id'],
        ])->field(['n.status','n.id','s.name','d.name dep','j.name job'])
            ->where($conditions)->paginate(10, false, [
            'query' => ['keyword' => $keyword]
        ]);
        //获取分页显示
        $page = $newsList->render();
        $this->assign([
            'newsList'=>$newsList,
            'page' => $page
        ]);
        return $this->fetch();
    }
    //添加发布者
    public function add(){
        $this->isLogin();

        $depList = Db::name('department')->where('delete_time',0)->select();
        $this->assign('depList',$depList);
        return $this->fetch();
    }
    //添加发布者提交
    public function addPost(){
        $id = $this->request->param('staff_id');
        $data = [
            'staff_id' => $id,
            'status' => 1,
        ];
        $result1 = $this->model->insert($data);
        //staff表中type字段置2
        $result2 = Db::name('staff')->where('id',$id)->update(['type'=>2]);
        //根据staff_id查找name
        $staff = Db::name('staff')->field('name')->where('id',$id)->find();
        if ($result1 && $result2){
            $this->success('添加成功！',url('admin/NewsmakerController/index'),'添加新闻发布者:'.$staff['name']);
        }else{
            $this->error('添加失败！');
        }
    }
    //暂停赋权
    public function disable(){
        $id = $this->request->param('id');
        //查询对应发布者的name
        $staff = Db::name('staff s')->join('newsmaker n','n.staff_id=s.id')
            ->field(['name'])->where('n.id',$id)->find();
        $result = $this->model->where('id',$id)->update(['status'=>0]);
        if ($result) {
            $this->success('暂停赋权成功！',url('admin/NewsmakerController/index'),'暂停发布者权限:'.$staff['name']);
        }else{
            $this->error('暂停失败！');
        }
    }
    //开启权限
    public function enable(){
        $id = $this->request->param('id');
        //查询对应发布者的name
        $staff = Db::name('staff s')->join('newsmaker n','n.staff_id=s.id')
            ->field(['name'])->where('n.id',$id)->find();
        $result = $this->model->where('id',$id)->update(['status'=>1]);
        if ($result) {
            $this->success('开启赋权成功！',url('admin/NewsmakerController/index'),'开启发布者权限:'.$staff['name']);
        }else{
            $this->error('开启失败！');
        }
    }
    //删除
    public function delete(){
        $id = $this->request->param('id');
        //查询对应发布者的name
        $staff = Db::name('staff s')->join('newsmaker n','n.staff_id=s.id')
            ->field(['s.name','s.id'])->where('n.id',$id)->find();
        //删除newsmaker表中数据
        $result1 = $this->model->where('id',$id)->update(['delete_time'=>time()]);
        //staff表中type字段置1
        $result2 = Db::name('staff')->where('id',$staff['id'])->update(['type'=>1]);

        if ($result1 && $result2){
            $this->success('删除成功！',url('admin/NewsmakerController/index'),'删除新闻发布者'.$staff['name']);
        }else{
            $this->error('删除失败！');
        }
    }
    //接收ajax值，查询所选部门下的员工列表
    public function showStaff($did){
        $data = Db::name('staff')->field(['id','name'])
            ->where(['delete_time'=>0,'dep_id'=>$did])->select();
        $this->success('','',$data);
    }

}