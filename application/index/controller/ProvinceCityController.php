<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/1/29
 * Time: 22:07
 */
namespace app\index\controller;
use app\UserBaseController;
use app\index\model\ProvinceCity;
use think\Db;
class ProvinceCityController extends UserBaseController
{

    public function index(){

    }
    public function loadChildren($pid, $level) {

        $model = new ProvinceCity();
        //用于接收省市区联动的ajax传递的值，然后查询数据，返回值
        $result = $model->where(['parent_id'=>$pid, 'level'=>$level, 'delete_time'=>0])->field(['id', 'name'])->select();

        return json([
            "data" => $result
        ]);

    }
}