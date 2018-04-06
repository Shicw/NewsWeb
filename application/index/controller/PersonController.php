<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/1/29
 * Time: 18:20
 */
namespace app\index\controller;
use app\index\model\ProvinceCity;
use app\index\model\Staff;
use app\UserBaseController;
use think\Validate;
use think\Db;
class PersonController extends UserBaseController
{
    public function index(){
        //导航栏
        $nav = $this->navList();
        //读取前台登录的用户信息
        $user = session('user');

        $depList = Db::name('department')->where('delete_time',0)->select();
        //若用户信息包含dep_id则获取对应部门的岗位列表，否则岗位列表置空
        $jobList =[];
        if($user['dep_id']!=0) {
            $jobList = Db::name('jobs')->where(['delete_time' => 0, 'dep_id' => $user['dep_id']])->select();
        }
        //省市区下拉框赋值
        $province = new ProvinceCity();
        $provinceList = $province->where(['delete_time'=>0,'level'=>1])->select();
        $cityList = $province->where(['delete_time'=>0,'level'=>2,'parent_id'=>$user['province_id']])->select();
        $districtList = $province->where(['delete_time'=>0,'level'=>3,'parent_id'=>$user['city_id']])->select();

        $this->assign([
            'user' => $user,
            'nav'  => $nav,
            'depList' => $depList,
            'jobList' => $jobList,
            'provinceList' => $provinceList,
            'cityList' => $cityList,
            'districtList' => $districtList,
        ]);
        return $this->fetch();
    }
    //个人中心修改提交
    public function editPost(){
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'name' => 'require|chs|min:6|max:12',
                'sex' => 'require|integer',
                'dep_id' => 'require|integer',
                'job_id' => 'require|integer',
                'job_number' => 'require|integer',
                'mobile' => 'require|number|min:11|max:11',
                'email' => 'require|email',
                'birthday' => 'require|integer',
                'employ_date' => 'require|integer',
                'province_id' => 'require|integer',
                'city_id' => 'require|integer',
                'district_id' => 'require|integer',
            ]);
            $data = $this->request->post();
            $data['birthday'] = strtotime($data['birthday']);
            $data['employ_date'] = strtotime($data['employ_date']);
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $staff = new Staff();
            $update = $staff->where('id',$data['id'])->update($data);
            if ($update){
                //用户更新个人信息后，接收到的数据中没有avatar，因此先将之前session中的avatar放入data数组
                //防止直接将data更新进session中丢失头像信息的问题
                //$data['avatar'] = session('user.avatar');
                $find = $staff->where('id',$data['id'])->find();
                session('user', $find);

                $this->success('个人信息修改成功！','','个人信息修改');
            }else{
                $this->error('没有新修改的信息！');
            }
        }else {
            $this->error("请求错误");
        }
    }
    //修改头像提交
    public function avatarPost(){
        //获取上传的头像
        $file = request()->file('avatar');
        //获取当前用户信息
        $user = session('user');
        //将头像文件保存到/public/static/avatar 目录下
        $info = $file->move(ROOT_PATH . 'public/static' . DS . 'avatar');
        if ($info) {
            //获取保存的图片文件名
            $avatarName = $info->getSaveName();
            //将头像文件名写入staff表
            $staff = new Staff();
            $result = $staff->where('id',$user['id'])->update(['avatar'=>$avatarName]);
            if ($result){
                //更新session中的用户头像信息
                $user['avatar'] = $avatarName;
                session('user', $user);
                $this->success('头像修改成功！',url('index/PersonController/index'),'修改头像');
            }else{
                $this->error('头像修改失败！');
            }
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }
    //修改密码
    public function pwdEdit(){
        $data = $this->request->param();
        $username = $data['username'];
        $passwordOld = md5($data['passwordOld']);
        $passwordNew =  md5($data['passwordNew']);
        $passwordNewAgain =  md5($data['passwordNewAgain']);
        $userId = session('user.id');

        $staff = new Staff();
        $userData = $staff->field(['username','password'])->where('id',$userId)->find();
        //各错误类型判断
        if ($username != $userData['username']) $this->error('用户名不正确');
        if ($passwordOld != $userData['password']) $this->error('旧密码不正确');
        if ($passwordNewAgain !== $passwordNew) $this->error('两次输入的密码不一致');
        if ($passwordOld == $passwordNew) $this->error('新密码不能与旧密码相同');
        $updatePwd = $staff->where('id',$userId)->update(['password'=>$passwordNew]);
        if ($updatePwd){
            $this->success('密码修改成功！',url('index/index/index'),'密码修改');
        }else{
            $this->error('密码修改失败，请重试！');
        }
    }
    //我的评论
    public function comment(){
        //导航栏
        $nav = $this->navList();
        //读取前台登录的用户信息
        $user = session('user');
        //我的评论列表
        $id = $user['id'];
        //查询评论列表，关联news表
        //使用Db类操作数据库得到的时间戳字段复制到模板需要使用date函数转换成时间字符串
        $list = Db::name('comment c')->join('news n','n.id=c.news_id')
            ->field(['n.id','n.title','c.comment','c.news_id','c.staff_name','c.create_time'])
            ->where(['c.delete_time'=>0,'c.staff_id'=>$id])->order('create_time desc')->paginate(5);
        //获取分页显示
        $page = $list->render();
        $this->assign([
            'nav'  => $nav,
            'user' => $user,
            'list' => $list,
            'page' => $page
        ]);
        return $this->fetch();
    }
    //我的收藏
    public function collection(){
        //导航栏
        $nav = $this->navList();
        //读取前台登录的用户信息
        $user = session('user');
        //我的收藏列表
        $id = $user['id'];
        //查询收藏列表，关联news,type,department表
        $list = Db::name('collection c')->join([
            ['news n','n.id=c.news_id'],
            ['department d','d.id=n.dep_id'],
            ['news_type nt','nt.id=n.type_id']
        ])->field(['n.id','n.title','n.create_time ncreate_time','d.name dep','nt.name type','c.create_time'])
            ->where(['c.delete_time'=>0,'c.staff_id'=>$id])->order('c.create_time desc')->paginate(10);
        //获取分页显示
        $page = $list->render();
        $this->assign([
            'nav'  => $nav,
            'user' => $user,
            'list' => $list,
            'page' => $page
        ]);
        return $this->fetch();
    }
    //接收ajax发送的dep_id值，查询所对应的岗位列表，返回
    public function loadJobs($did){
        $data = Db::name('jobs')->where(['delete_time'=>0,'dep_id'=>$did])->select();
        return json([
            "code" => 1,
            "msg" => "加载成功",
            "data" => $data,
            "url" => ''
        ]);
    }
}