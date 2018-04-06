//layer插件弹出确认对话框
function Dialog(id,url,msg){
    layer.confirm('确定要'+msg+'吗？', {
        btn: ['确定','取消'] //按钮
    }, function(){
        //通过ajax请求对应控制器下的方法,接收返回值
        //location.href = url + "?id=" + id;
        //console.log(url);
        $.ajax({
            type: "get",
            url: url + "?id=" + id,
            dataType: "json",
            success:function (result) {
                layer.msg(result.msg,{time:1500});
                if(result.code===1) {
                    setTimeout(function () {
                        window.location.href = result.url;
                    },1600);
                }
            }
        });
    }, function(){});
}
