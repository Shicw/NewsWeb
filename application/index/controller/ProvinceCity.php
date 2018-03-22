<?php
/**
 * Created by PhpStorm.
 * User: a3625
 * Date: 2018/1/29
 * Time: 22:07
 */
namespace app\index\controller;
use app\UserBaseController;
use app\index\model\ProvinceCityModel;
use think\Db;
class ProvinceCity extends UserBaseController
{

    public function index(){

    }
    public function loadChildren($pid, $level) {

        $model = new ProvinceCityModel();

        $result = $model->loadChildren($pid,$level);

        return $result;
    }
}