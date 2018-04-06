<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/4
 * Time: 21:16
 */
namespace app\admin\controller;
use app\AdminBaseController;
use app\admin\model\News;
use think\Db;
use think\Validate;
class NewsController extends AdminBaseController
{
    private $model;
    //初始化模型对象
    public function _initialize(){
        parent::_initialize();
        $this->model = new News();
    }

    public function index(){
        $this->isLogin();

        //关键字查询
        $request = input('request.');
        $keyword = '';
        $conditions = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $conditions['n.title'] = ['like', "%$keyword%"];
        }
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
        //判断当前登录的是发布者还是admin，发布者只能看到自己发布的新闻列表
        $user = session('admin');
        if($user['type'] == 2) $conditions['publisher'] = $user['id'];

        $newsList = $this->model->alias('n')->join([
            ['department d','d.id=n.dep_id'],
            ['news_type nt','nt.id=n.type_id'],
            ['staff s','s.id=n.publisher']
        ])->field(['n.*','d.name dep','nt.name type','s.name publisher'])
            ->where($conditions)->order('create_time desc')->paginate(10, false, [
                'query' => [
                    'keyword' => $keyword,
                    'dep_id' => $dep_id,
                    'type_id' => $type_id
                ]
        ]);
        $page = $newsList->render();
        $this->assign([
            'newsList'=>$newsList,
            'page' => $page,
            'depList' => $depList,
            'typeList' => $typeList,
            'dep_id' => $dep_id,
            'type_id' => $type_id,
            //赋值user_type,admin不能修改新闻
            'userType' => $user['type']
        ]);
        return $this->fetch();
    }
    //添加新闻(只有新闻发布者才能添加新闻)
    public function add(){
        $this->isLogin();
        //新闻类别下拉框赋值
        $typeList = Db::name('news_type')->where('delete_time',0)->select();
        //所属部门下拉框赋值
        $depList = Db::name('department')->where('delete_time',0)->select();
        $this->assign([
            'typeList' => $typeList,
            'depList' => $depList
        ]);
        return $this->fetch();
    }
    //添加新闻提交
    public function addPost(){
        //先判断是否接受到图片，有则先处理图片，返回图片名；然后在接收ajax传递新闻内容
        if(request()->file('image')) {
            //获取图片信息
            $file = request()->file('image');
            //如果有上传图片，则处理图片文件，否则$imgName置空写入数据库
            $imgName = '';
            if ($file) {
                //将图片保存到/public/static/news_imgs 目录下
                $info = $file->move(ROOT_PATH . 'public/static' . DS . 'news_imgs');
                //获取图片的文件名
                $imgName = $info->getSaveName();
                $this->success('','',$imgName);
            }
        }
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'type_id' => 'require|integer',
                'dep_id' => 'require|integer',
                'title' => 'require|min:6|max:60',
                'content' => 'require|min:6'
            ]);
            $validate->message([
                'type_id.require' => '新闻类别不能为空',
                'dep_id.require' => '部门不能为空',
                'title.require' => '标题不能为空',
                'title.min' => '标题太短',
                'title.max' => '标题太长',
                'content.require' => '内容不能为空',
                'content.min' => '内容太短'
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            //获取发布者id
            $publisher = session('admin.id');
            $time = time();
            $data = [
                'title' => $data['title'],
                'content' => $data['content'],
                'img' => $data['imgName'],
                'dep_id' => $data['dep_id'],
                'type_id' => $data['type_id'],
                'create_time' => $time,
                'update_time' =>$time,
                'publisher' => $publisher,
            ];
            //将新闻信息写入news表，同时根据新闻部门和类别更新department表和news_type表的new_count
            $result1 = $this->model->insert($data);
            $result2 = Db::name('department')->where(['id'=>$data['dep_id'],'delete_time'=>0])->setInc('news_count',1);
            $result3 = Db::name('news_type')->where(['id'=>$data['type_id'],'delete_time'=>0])->setInc('news_count',1);
            if ($result1 && $result2 && $result3){
                $this->success('新闻发布成功！',url('admin/NewsController/index'),'发布新闻:'.$data['title']);
            }else{
                $this->error('新闻发布失败！');
            }
        }else{
            $this->error('请求错误');
        }
    }
    //新闻修改
    public function edit(){
        $this->isLogin();
        //新闻类别下拉框赋值
        $typeList = Db::name('news_type')->where('delete_time',0)->select();
        //所属部门下拉框赋值
        $depList = Db::name('department')->where('delete_time',0)->select();
        //查询新闻信息
        $id = $this->request->param('id');
        $data = $this->model->where(['delete_time'=>0,'id'=>$id])->find();

        $this->assign([
            'typeList' => $typeList,
            'depList' => $depList,
            'data' => $data
        ]);
        return $this->fetch();
    }
    //新闻修改提交
    public function editPost(){
        //先判断是否接受到图片，有则先处理图片，返回图片名；然后在接收ajax传递新闻内容
        if(request()->file('image')) {
            //获取图片信息
            $file = request()->file('image');
            //如果有上传图片，则处理图片文件，否则$imgName置空写入数据库
            $imgName = '';
            if ($file) {
                //将图片保存到/public/static/news_imgs 目录下
                $info = $file->move(ROOT_PATH . 'public/static' . DS . 'news_imgs');
                //获取图片的文件名
                $imgName = $info->getSaveName();
                $this->success('','',$imgName);
            }
        }
        //php后端验证
        if ($this->request->isPost()) {
            $validate = new Validate([
                'type_id' => 'require|integer',
                'dep_id' => 'require|integer',
                'title' => 'require|min:6|max:60',
                'content' => 'require|min:6',
            ]);
            $validate->message([
                'type_id.require' => '新闻类别不能为空',
                'dep_id.require' => '部门不能为空',
                'title.min' => '标题太短',
                'title.max' => '标题太长',
                'content.require' => '内容不能为空',
                'content.min' => '内容太短',
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $data['update_time'] = time();
            $update = $this->model->where('id',$data['id'])->update($data);

            if ($update){
                $this->success('新闻修改成功！',url('admin/NewsController/index'),'修改新闻:'.$data['title']);
            }else{
                $this->error('没有新的修改信息！');
            }
        }else{
            $this->error('请求错误');
        }
    }
    //新闻删除
    public function delete(){
        $id = $this->request->param('id');
        //查询记录是否存在
        $find = $this->model->where('id',$id)->find();
        if ($find){
            $result = $this->model->where('id',$id)->update(['delete_time'=>time()]);
            if ($result){
                $this->success('新闻删除成功！',url('admin/NewsController/index'),'删除新闻:'.$find['title']);
            }else{
                $this->error('新闻删除失败！');
            }
        }else{
            $this->error('删除失败,该新闻不存在！');
        }
    }
    //查询部门新闻数量和各类别新闻数量，返回给ajax
    public function getNewsCount(){
        $typeCount = Db::name('news_type')->field(['name label','news_count value'])->where('delete_time',0)->select();
        $depCount = Db::name('department')->field(['name label','news_count value'])->where('delete_time',0)->select();

        //将查询出的数组json化，并且防止中文变成unicode
        $typeCount = json_encode($typeCount,JSON_UNESCAPED_UNICODE);
        $depCount = json_encode($depCount,JSON_UNESCAPED_UNICODE);
        $data = [
            "typeCount" => $typeCount,
            "depCount" => $depCount
        ];
        if($typeCount && $depCount){
            $this->success('图表信息查询成功','',$data);
        }else{
            $this->error('图表信息查询失败！');
        }
    }
}