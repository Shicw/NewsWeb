function provinceChange(self) {
    var pid = self.value;
    //url路径中控制器名必须用匈牙利命名方式(下划线),不能用驼峰式(大小写);路径需要绝对路径
    $.post("/index/province_city_controller/loadChildren", {pid: pid, level: 2}, function (result) {

        reloadCity(result);
    }, "json");
}

//当更改省份时,ajax显示城市,同时清除区县
function reloadCity(result) {
    //删除除第一条option以外的所有option(第一条option为"请选择")
    $("#city option:not(:first)").remove();

    $("#district option:not(:first)").remove();

    updateOptions(result, 'city');
}

function cityChange(self) {
    var pid = self.value;
    $.post("/index/province_city_controller/loadChildren", {pid: pid, level: 3}, function (result) {
        reloadDistrict(result);
    }, "json");

}

function reloadDistrict(result) {
    //删除除第一条option以外的所有option(第一条option为"请选择")
    $("#district option:not(:first)").remove();

    updateOptions(result, 'district');
}

//更新option
function updateOptions(result, name) {
    var data = result['data'];
    var select = $("#" + name);
    for (var i = 0; i < data.length; i++) {
        select.append('<option  value="' + data[i]['id'] + '">' + data[i]['name'] + '</option>');
    }
}