//当更换部门时，ajax异步加载岗位select
function updateDep(self){
    var did = self.value;
    $.post("/index/person_controller/loadJobs",{did:did},function(data){
        if(data.code == 1){
            reloadJobs(data);
        }
    },"json");
}
//当更改部门时，加载岗位
function reloadJobs(data) {
    //删除除第一条option以外的所有option(第一条option为"请选择")
    $("#job_id option:not(:first)").remove();

    var data = data['data'];
    var select = $("#job_id");
    for (var i = 0; i < data.length; i++) {
        select.append('<option  value="' + data[i]['id'] + '">' + data[i]['name'] + '</option>');
    }
}