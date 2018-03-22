//layer插件弹出确认对话框
function Dialog(id,url,msg){
    layer.confirm('确定要'+msg+'吗？', {
        btn: ['确定','取消'] //按钮
    }, function(){
        //跳转到当前文件对应控制器下的delete方法
        location.href = url + "?id=" + id;
    }, function(){});
}
