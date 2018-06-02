/*
*index 专用js
*methods 方法
*model 模型
*/
  //显示页数 Bootstrap  自带
  // $(function (){
  //   $('#example1').DataTable({
  //     'paging'      : true,
  //     'lengthChange': true,
  //     'searching'   : true,
  //     'ordering'    : false,
  //     'info'        : true,
  //     'autoWidth'   : true
  //   });
  // })

  //删除所有
  function delAll (methods,model) {
    var datas = tableCheck.getData();
    // layer.confirm('确认要全部删除吗？',function(index){
    //     //捉到所有被选中的，发异步进行删除
    //     layer.msg('删除成功', {icon: 1});
    //     $(".layui-form-checked").not('.header').parents('tr').remove();
    // });
    if(datas==''){
      layer.msg('请先选择数据...');
    }else{
      layer.confirm('确定要删除吗?',{
        btn: ['确定','取消'] //按钮
      },function(){
        $.post(methods, {id:datas,model:model}, function(data, textStatus, xhr) {
          /*optional stuff to do after success */
          if(data.code==0){
            layer.msg(data.msg);
          }else{
            layer.msg(data.msg,{icon:1,time:800},function(){
              $("#reset").click();
              location.reload();
            });
          }
        });
      }); 
    } 
  }
 //上下架
  function the_shelves(status,methods,model) {
    console.log(status,methods,model);
      var datas = tableCheck.getData();
      // layer.confirm('确认要全部删除吗？',function(index){
      //     //捉到所有被选中的，发异步进行删除
      //     layer.msg('删除成功', {icon: 1});
      //     $(".layui-form-checked").not('.header').parents('tr').remove();
      // });
      if(datas==''){
        layer.msg('请先选择数据...');
      }else{
        if(status=='1'){
          var theshelves = '确定要下架吗？';
        }else{
          var theshelves = '确定要上架吗？';
        }

        layer.confirm(theshelves,{
          btn: ['确定','取消'] //按钮
        },function(){
          $.post(methods, {id:datas,status:status,model:model}, function(data, textStatus, xhr) {
            /*optional stuff to do after success */
            if(data.code==0){
              layer.msg(data.msg);
            }else{
              layer.msg(data.msg,{icon:1,time:800},function(){
                $("#reset").click();
                location.reload();
              });
            }
          });
        }); 
      } 
  }

  /*
  *修改状态
  *id 行的id
  *methods 方法
  *model 模型，数据库
  */
  function status_row(id,methods,status,model){
    console.log(id,methods,status,model);
    $.post(methods, {id:id,status: status,model:model}, function(data, textStatus, xhr) {
      if(data.code==0){
        layer.msg(data.msg);
      }else{
        layer.msg(data.msg,{icon:1,time:800},function(){
          $("#reset").click();
          location.reload();
        });
      }
    });
  }

  /*
  *删除
  *id 行的id
  *methods 方法
  *model 模型，数据库
  */
  function delete_row(id,methods,model){
    console.log(id,methods,model);
    layer.confirm('确定删除吗？', function(index){
      $.ajax({
        url: methods,
        type: 'post',
        dataType: 'json',
        data: {id:id,model:model},
      })
      .done(function(data){
          //回调提示
          if(data.code==0){
            layer.msg(data.msg,{icon:2,time:1000});
          }else{
            layer.msg(data.msg,{icon:1,time:1500},function(){
              location.reload();
            });
          }
        })
    })
  }

 
