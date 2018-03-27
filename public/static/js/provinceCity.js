function provinceChange(self) {
    var pid = self.value;
    //url路径中控制器名必须用匈牙利命名方式(下划线),不能用驼峰式(大小写);路径需要绝对路径
    $.post("/index/province_city_controller/loadChildren", {pid: pid, level: 2}, function (data) {

        if (data.code == 1) {

            reloadCity(data);
        }
    }, "json");
}

//当更改省份时,ajax显示城市,同时清除区县
function reloadCity(data) {
    //删除除第一条option以外的所有option(第一条option为"请选择")
    $("#input-city option:not(:first)").remove();

    $("#input-district option:not(:first)").remove();

    updateOptions(data, 'city');
}

function cityChange(self) {
    var pid = self.value;
    $.post("/index/province_city_controller/loadChildren", {pid: pid, level: 3}, function (data) {
        if (data.code == 1) {
            reloadDistrict(data);
        }
    }, "json");

}

function reloadDistrict(data) {
    //删除除第一条option以外的所有option(第一条option为"请选择")
    $("#input-district option:not(:first)").remove();

    updateOptions(data, 'district');
}

//更新option
function updateOptions(data, name) {
    var data = data['data'];
    var select = $("#input-" + name);
    for (var i = 0; i < data.length; i++) {
        select.append('<option  value="' + data[i]['id'] + '">' + data[i]['name'] + '</option>');
    }
}