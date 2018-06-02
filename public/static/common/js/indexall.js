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

  /*
  * 删除所有 多选情况，使用
  * methods 方法指向 delete_row
  * model  数据库表 
  */
  function delete_all (methods,model) {

    //定义获取table id数据
    var table = layui.table;
    var checkStatus = table.checkStatus('id')
      ,data = checkStatus.data; //获取选中行的数据
      
      var datas = new Array();
      for(var i=0;i<data.length;i++){
        console.log(data[i].id)
        datas.push(data[i].id);
      }

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

  /*
  * 修改所有 多选情况，使用
  * currentstate 当前状态
  * methods 方法指向 status_row
  * model  数据库表 
  * statusname 需要修改的数据库字段
  */
 function status_all(currentstate,statusname,methods,model) {
  console.log(currentstate,methods,model);

  var table = layui.table;
  var checkStatus = table.checkStatus('id')
      ,data = checkStatus.data; //获取选中行的数据
      
      var datas = new Array();
      for(var i=0;i<data.length;i++){
        console.log(data[i].id)
        datas.push(data[i].id);
      }
 
      if(datas==''){
        layer.msg('请先选择数据...');
      }else{
        if(currentstate=='1'){
          var theshelves = '确定要下架吗？';
        }else{
          var theshelves = '确定要上架吗？';
        }

        layer.confirm(theshelves,{
          btn: ['确定','取消'] //按钮
        },function(){
          $.post(methods, {id:datas,status:currentstate,statusname:statusname,model:model}, function(data, textStatus, xhr) {
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

  /**
  * 修改状态，所有状态
  * id  修改行的id
  * currentstate 当前状态 
  * statusname 修改数据库表字段
  * model 数据库表名称
  * methods 方法 指向status_row
  */

  function status_row(id,currentstate,statusname,model,methods){
    console.log(id,currentstate,statusname,model,methods);
    $.post(methods, {id:id,status: currentstate,statusname:statusname,model:model}, function(data, textStatus, xhr) {
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
  *methods 方法 指向 delete_row
  *model 模型，数据库
  */
  function delete_row(id,model,methods){
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

  /*双击放大图片 2018年3月18日15:21:30*/
  function tClick(url){
    //页面层
    layer.open({
        type: 1,
        skin: '', //加上边框
        area: ['900px', '648px'], //宽高
        content: "<center style='margin-top:40px;'><img width='800' height='500' src="+url+"></center>"
    });
  }