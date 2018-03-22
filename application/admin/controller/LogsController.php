<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/2/1
 * Time: 16:37
 */
namespace app\admin\controller;
use app\AdminBaseController;
use app\admin\model\Logs;
use think\Db;
class LogsController extends AdminBaseController
{
    public function index(){
        $this->isLogin();
        //关键字查询
        $request = input('request.');
        $keyword = '';
        $conditions = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $conditions['msg|data|s.username|s.name'] = ['like', "%$keyword%"];
        }
        //日期筛选
        $time_end = '';
        $time_begin = '';
        if (!empty($request['time_end'])) {
            $time_end = $request['time_end'];
            $conditions['l.create_time'] = ['<= time', $time_end];//time为时间比较条件，兼容时间戳和时间字符串
        }
        if (!empty($request['time_begin'])) {
            $time_begin = $request['time_begin'];
            $conditions['l.create_time'] = ['>= time', $time_begin];//time为时间比较条件，兼容时间戳和时间字符串
        }
        //日志列表查询
        $logs = new Logs();
        $logsData = $logs->alias('l')->join('staff s','l.staff_id=s.id')
            ->field(['l.*','s.name','s.username'])->where($conditions)
            ->order('id desc')->paginate(10, false, [
                'query' => [
                    'keyword' => $keyword,
                    'time_begin' => $time_begin,
                    'time_end' => $time_end,
                ],
            ]);
        //获取分页显示
        $page = $logsData->render();
        $this->assign([
            'logsData'=> $logsData,
            'page'=> $page,
        ]);
        return $this->fetch();
    }
}