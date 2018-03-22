<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script type="text/javascript" src="__STATIC__/admin/js/jquery-1.11.3.js"></script>
    <script type="text/javascript" src="__STATIC__/admin/layer/layer.js"></script>
    <title>jump</title>
</head>
<body>
<input type="text" id="url" value="{php}echo $url;{/php}" hidden>
<input type="text" id="msg" value="{php}echo $msg;{/php}" hidden>
<input type="text" id="wait" value="{php}echo $wait;{/php}" hidden>
<script>
    var url = $('#url').val();
    var msg = $('#msg').val();
    var wait = $('#wait').val();
    layer.msg(msg);
    setInterval(function(){
        var time = --wait;
        if(time <= 0) {
            location.href = url;
            clearInterval();
        }
    }, 1000);
</script>
</body>
</html>