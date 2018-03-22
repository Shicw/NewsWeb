<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/1
 * Time: 21:37
 */
namespace app\admin\controller;
use app\AdminBaseController;
use app\admin\model\Staff;
use think\Db;
use think\Validate;
class StaffController extends AdminBaseController
{
    private $model;
    //初始化模型对象
    public function _initialize(){
        parent::_initialize();
        $this->model = new  Staff();
    }

    //人员管理主页
    public function index(){
        $this->isLogin();
        //关键字查询
        $request = input('request.');
        $keyword = '';
        $conditions = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $conditions['s.name|s.username|s.email|s.mobile'] = ['like', "%$keyword%"];
        }
        //部门查询
        $dep_id = '';
        if (!empty($request['dep_id'])) {
            $dep_id = $request['dep_id'];
            $conditions['s.dep_id'] = $dep_id;
        }
        $depList = Db::name('department')->where('delete_time',0)->select();
        //性别查询
        $sex = '';
        if (!empty($request['sex'])) {
            $sex = $request['sex'];
            $conditions['s.sex'] = $sex;
        }
        //入职时间查询
        $date_end = '';
        $date_begin = '';
        if (!empty($request['date_end'])) {
            $date_end = $request['date_end'];
            $conditions['s.employ_date'] = ['<= time', $date_end];//time为时间比较条件，兼容时间戳和时间字符串
        }
        if (!empty($request['date_begin'])) {
            $date_begin = $request['date_begin'];
            $conditions['s.employ_date'] = ['>= time', $date_begin];//time为时间比较条件，兼容时间戳和时间字符串
        }
        //过滤被删除的记录
        $conditions['s.delete_time'] = 0;
        //查询人员列表
        $staffList = $this->model->alias('s')->join([
            ['department d','d.id=s.dep_id'],
            ['jobs j','j.id=s.job_id'],
        ])->field(['s.*','d.name dep','j.name job'])
            ->where($conditions)->order('id desc')->paginate(10, false, [
                'query' => [
                    'keyword' => $keyword,
                    'dep_id' => $dep_id,
                    'sex' => $sex,
                    'date_begin' => $date_begin,
                    'date_end' => $date_end,
                ],
            ]);
        //获取分页显示
        $page = $staffList->render();
        $this->assign([
            'staffList'=> $staffList,
            'page' => $page,
            'depList' => $depList,
            'dep_id' => $dep_id,
            'sex' => $sex
        ]);
        return $this->fetch();
    }
    //添加人员
    public function add(){
        $this->isLogin();
        //岗位、省份下拉框赋值
        $depList = Db::name('department')->where('delete_time',0)->select();
        $provinceList = Db::name('province_city')->where(['delete_time'=>0,'level'=>1])->select();

        $this->assign([
            'depList' => $depList,
            'provinceList' => $provinceList,
        ]);
        return $this->fetch();
    }
    //添加人员提交
    public function addPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:6|max:12',
                'sex' => 'require|integer',
                'dep_id' => 'require|integer',
                'job_id' => 'require|integer',
                'job_number' => 'require|integer',
                'province_id' => 'require|integer',
                'city_id' => 'require|integer',
                'district_id' => 'require|integer',
                'mobile' => 'require|number|min:11|max:11',
                'email' => 'require|email',
                'birthday' => 'require|integer',
                'employ_date' => 'require|integer',
                'username' => 'require|alphaNum|min:4|max:8',
                'password' => 'require|alphaDash|min:6|max:12'
            ]);
            $validate->message([
                'name.require' => '姓名不能为空',
                'name.chs' => '姓名只能为汉字',
                'name.min' => '姓名不能少于两个汉字',
                'name.max' => '姓名太长',
                'sex.require' => '性别不能为空',
                'dep_id.require' => '部门不能为空',
                'province_id' => '省份不能为空',
                'city_id' => '城市不能为空',
                'district_id' => '区县不能为空',
                'mobile' => '手机不能为空',
                'email' => '邮箱不能为空',
                'birthday' => '出生日期不能为空',
                'employ_date' => '入职时间不能为空',
                'username' => '用户名不能为空',
                'password' => '密码不能为空'
            ]);
            $data = $this->request->post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['employ_date'] = strtotime($data['employ_date']);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $data['password'] = md5($data['password']);
            $update = $this->model->insert($data);
            if ($update){
                $this->success('添加成功！',url('admin/StaffController/index'),'添加人员:'.$data['name']);
            }else{
                $this->error('添加失败！');
            }
        }else{
            $this->error('请求错误','','添加人员');
        }

    }
    //人员修改
    public function edit(){
        $this->isLogin();
        $id = $this->request->param('id');
        //根据id查找对应用户的信息
        $data = $this->model->where('id',$id)->find();
        //省市区赋值
        $provinceList = Db::name('province_city')->where(['level'=>1,'delete_time'=>0])->select();
        $cityList = Db::name('province_city')->where(['level' => 2, 'parent_id' => $data['province_id']])->select();
        $districtList = Db::name('province_city')->where(['level' => 3, 'parent_id' => $data['city_id']])->select();
        //部门岗位赋值
        $depList = Db::name('department')->where('delete_time',0)->select();
        $jobList = Db::name('jobs')->where(['delete_time'=>0,'dep_id'=>$data['dep_id']])->select();
        $this->assign([
            'provinceList' => $provinceList,
            'cityList' => $cityList,
            'districtList' => $districtList,
            'depList' => $depList,
            'jobList' => $jobList,
            'data' => $data
        ]);
        return $this->fetch();

    }
    //TODO 没有新修改信息提交成功BUG
    //人员修改提交
    public function editPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:6|max:12',
                'sex' => 'require|integer',
                'dep_id' => 'require|integer',
                'job_id' => 'require|integer',
                'job_number' => 'require|integer',
                'province_id' => 'require|integer',
                'city_id' => 'require|integer',
                'district_id' => 'require|integer',
                'mobile' => 'require|number|min:11|max:11',
                'email' => 'require|email',
                'birthday' => 'require|integer',
                'employ_date' => 'require|integer',
                'username' => 'require|alphaNum|min:4|max:8',

            ]);
            $validate->message([
                'name.require' => '姓名不能为空',
                'name.chs' => '姓名只能为汉字',
                'sex.require' => '性别不能为空',
                'dep_id.require' => '部门不能为空',
                'job_id' => '岗位不能为空',
                'province_id' => '省份不能为空',
                'city_id' => '城市不能为空',
                'district_id' => '区县不能为空',
                'mobile' => '手机不能为空',
                'email' => '邮箱不能为空',
                'birthday' => '出生日期不能为空',
                'employ_date' => '入职时间不能为空',
                'username' => '用户名不能为空',
            ]);
            $data = $this->request->post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['employ_date'] = strtotime($data['employ_date']);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $update = Db::name('staff')->where('id',$data['id'])->update($data);
            if ($update){
                $this->success('修改成功！',url('admin/StaffController/index'),'修改人员:'.$data['name']);
            }else{
                $this->error('没有新修改的信息！');
            }
        }else{
            $this->error('请求错误');
        }
    }
    //禁用
    public function disable(){
        $id  = $this->request->param('id');
        //查询用户是否存在
        $find = $this->model->where(['id'=>$id,'delete_time'=>0])->find();
        if ($find){
            $name = $find['name'];
            $result = $this->model->where(['id'=>$id,'delete_time'=>0])->update(['status'=>0]);
            if ($result){
                $this->success('禁用成功！',url('admin/StaffController/index'),'禁用用户:'.$name);
            }else{
                $this->error('禁用失败！','','禁用用户:'.$name);
            }
        }else{
            $this->error('禁用失败,该用户不存在！');
        }

    }
    //启用
    public function enable(){
        $id  = $this->request->param('id');
        //查询用户是否存在
        $find = $this->model->where(['id'=>$id,'delete_time'=>0])->find();
        if ($find){
            $name = $find['name'];
            $result = $this->model->where(['id'=>$id,'delete_time'=>0])->update(['status'=>1]);
            if ($result){
                $this->success('启用成功！',url('admin/StaffController/index'),'启用用户:'.$name);
            }else{
                $this->error('启用失败！','','启用用户:'.$name);
            }
        }else{
            $this->error('启用失败,该用户不存在！');
        }
    }
    //人员删除
    public function delete(){
        $id  = $this->request->param('id');
        //查询用户是否存在
        $find = $this->model->where('id',$id)->find();
        if ($find){
            $name = $find['name'];
            $result = $this->model->where('id',$id)->update(['delete_time'=>time()]);
            if ($result){
                $this->success('删除成功！',url('admin/StaffController/index'),'删除用户:'.$name);
            }else{
                $this->error('删除失败！','','删除用户:'.$name);
            }
        }else{
            $this->error('删除失败,该用户不存在！');
        }
    }
    //接收ajax传值，检查用户名是否重复
    public function checkUsername($username){
        $find = $this->model->where('username',$username)->find();
        $data = $find ? 1 : 0;
        return json([
            "code" => 1,
            "msg"  => "加载成功",
            "data" => $data,
            "url"  => ''
        ]);
    }
    //接受ajax传值，查询人员详情
    public function showDetail($id){
        $data = $this->model->alias('s')->join([
            ['department d','d.id=s.dep_id'],
            ['jobs j','j.id=s.job_id'],
            ['province_city pc1', 'pc1.id=s.province_id'],
            ['province_city pc2', 'pc2.id=s.city_id'],
            ['province_city pc3', 'pc3.id=s.district_id'],
        ])->field(['s.*','d.name dep','j.name job','pc1.name province', 'pc2.name city', 'pc3.name district'])
            ->where('s.id',$id)->find();
        $sex = array("0"=>"未设置","1"=>"男","2"=>"女",);
        $status = array("0"=>"禁用","1"=>"启用");
        $data['sex'] = $sex[$data['sex']];
        $data['status'] = $status[$data['status']];
        return json([
            "code" => 1,
            "msg"  => "加载成功",
            "data" => $data,
            "url"  => ''
        ]);
    }

}