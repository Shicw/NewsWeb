$(".ajax-submit").click(function() {
    var form = $("#form");
    //获取表单数据
    var formData = form.serializeArray();
    //console.log(formData);
    //验证表单数据是否全部填写
    for(var i=0;i<formData.length;i++){
        var value = formData[i].value;
        //若某个input未输入,则弹窗提示
        if(value === ''){
            //循环时,获取当前所对应的input标签的上一个同级标签的text值,即所对应的label的文本值
            var msg = $("input[name='"+formData[i].name+"']").prev().text();
            layer.msg('请输入'+msg,{time:2000});
            return false;
        }
    }
    //获取表单action和method,供ajax使用
    var url = form.attr('action');
    var type = form.attr('method');
    $.ajax({
        type:type,
        url:url,
        data:formData,
        dataType:"json",
        success:function (result) {
            layer.msg(result.msg,{time:1500});
            //如果返回值中有url,则跳转到该url
            if(result.url !== ''){
                setTimeout(function () {
                    window.location.href = result.url;
                },1600);
            }
        }
    });
});