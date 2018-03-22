<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/1/29
 * Time: 22:08
 */
namespace app\index\model;
use think\Model;
class ProvinceCityModel extends Model
{
    protected $table = 'province_city';
//用于接收省市区联动的ajax传递的值，然后查询数据，返回值
    public function loadChildren($pid, $level) {
        $result = $this->where(['parent_id'=>$pid, 'level'=>$level, 'delete_time'=>0])->field(['id', 'name'])->select();

        return json([
            "code" => 1,
            "msg"  => "加载成功",
            "data" => $result,
            "url"  => ''
        ]);
    }
}