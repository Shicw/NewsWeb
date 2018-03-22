<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/5
 * Time: 19:17
 */
namespace app\index\controller;
use app\admin\model\News;
use app\UserBaseController;
use think\Validate;
use think\Db;
class NewsList extends UserBaseController
{
    private $model;
    //初始化模型对象
    public function _initialize(){
        parent::_initialize();
        $this->model = new News();
    }

    public function index(){
        //导航栏加载
        $nav = $this->navList();
        //获取前台登录的用户信息
        $user = session('user');
        //获取部门id和name
        $id = $this->request->param('id');
        $dep = Db::name('department')->field(['name'])->where(['id'=>$id,'delete_time'=>0])->find();
        $depName = $dep['name'];
        //关键字查询
        $request = input('request.');
        $keyword = '';
        $conditions = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $conditions['n.title|n.content'] = ['like', "%$keyword%"];
        }
        //类别查询
        $type_id = '';
        if (!empty($request['type_id'])) {
            $type_id = $request['type_id'];
            $conditions['n.type_id'] = $type_id;
        }
        $typeList = Db::name('news_type')->where('delete_time',0)->select();
        //日期筛选
        $time_end = '';
        $time_begin = '';
        if (!empty($request['time_end'])) {
            $time_end = $request['time_end'];
            $conditions['n.create_time'] = ['<= time', $time_end];//time为时间比较条件，兼容时间戳和时间字符串
        }
        if (!empty($request['time_begin'])) {
            $time_begin = $request['time_begin'];
            $conditions['n.create_time'] = ['>= time', $time_begin];//time为时间比较条件，兼容时间戳和时间字符串
        }

        $conditions['n.delete_time'] = 0;
        $conditions['n.dep_id'] = $id;
        //对应部门新闻列表
        $newsList = $this->model->alias('n')->join([
            ['department d','d.id=n.dep_id'],
            ['news_type nt','nt.id=n.type_id'],
        ])->field(['n.*','d.name dep','nt.name type'])
            ->where($conditions)->order('n.create_time desc')->paginate(10, false, [
            'query' => [
                'keyword' => $keyword,
                'type_id' => $type_id,
                'time_begin' => $time_begin,
                'time_end' => $time_end,
            ]
        ]);
        //获取分页显示
        $page = $newsList->render();
        $this->assign([
            'user' => $user,
            'nav' => $nav,
            'newsList' => $newsList,
            'page' => $page,
            'typeList' => $typeList,
            'typeId' => $type_id,
            'depId' => $id,
            'depName' => $depName
        ]);
        return $this->fetch();

    }
}