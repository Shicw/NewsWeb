//检查用户名是否存在ajax
function checkUsername(self) {
    var username= self.value;
    var Btn = $("#btn");
    $.post("/admin/staff_controller/checkUsername",{username:username},function(result){
        if (result.code==1){
            $("#error").removeAttr("hidden");
            Btn.addClass('unClick');
        }else{
            $("#error").attr("hidden","true");
            Btn.removeClass('unClick')
        }
    },"json");
}