$(function(){
  var options = {
      type:'post',           //post提交
      //url:'http://ask.tongzhuo100.com/server/****.php?='+Math.random(),   //url
      dataType:"json",        //json格式
      data:{},    //如果需要提交附加参数，视情况添加
      clearForm: false,        //成功提交后，清除所有表单元素的值
      resetForm: false,        //成功提交后，重置所有表单元素的值
      cache:false,
      async:false,          //同步返回
      success:function(data){
        if(data.code==0){
          layer.msg(data.msg);
        }else{
          layer.msg(data.msg,{icon:1,time:800},function(){
            $("#reset").click();
            x_admin_close();
            parent.location.reload();
          });
        }
          //服务器端返回处理逻辑
        },
        error:function(XmlHttpRequest,textStatus,errorThrown){
          layer.msg('操作失败:服务器处理失败');
        }
      };
      layui.use('form', function(){
        var form = layui.form;
        $('#mainForm').ajaxForm(options).submit(function(data){});
    });

})
  