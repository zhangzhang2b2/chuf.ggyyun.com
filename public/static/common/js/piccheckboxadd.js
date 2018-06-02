 /*
  add_file_list
 */

    //排序
    var count = 0;
    //我是子页面
    //注意：parent 是 JS 自带的全局对象，可用于操作父页面
    var index = parent.layer.getFrameIndex(window.name); //获取窗口索引

    //多图
    var fileid=parent.$("#parentIframe").val(); // 所有id
    var imgpath=parent.$("#imgurls").val(); //所有路径

    var files = new Array();  //所有
    var filesurl = new Array(); //单次选择路径
    var filesone = new Array();//单次 id
    var filesurls = new Array(); //所有路径

    //判断添加
    if(fileid && imgpath){ 
      files.push(fileid); //拿原来的id
      filesurls.push(imgpath); //所有路径
    }

    //单张选择
    var dshow = parent.$("#dshow").val();
    // console.log(dshow,'++-=');
    
    //单图
    var single_fileid=parent.$("#single_parentIframe").val(); // file_id
    var single_imgpath=parent.$("#single_imgurls").val(); // 路径

    var single_files = new Array();  //所有
    var single_filesurl = new Array(); //单次选择路径
    var single_filesone = new Array();//单次 id
    var single_filesurls = new Array(); //所有路径

    //判断添加
    if(single_fileid && single_imgpath){ 
      single_files.push(single_fileid); //拿原来的id
      single_filesurls.push(single_imgpath); //所有路径
    }

    //给父页面传值
    $('#transmit').on('click', function(){
      var datas =$('.tips');
      var list=new Array();
      var sortarr =new Array();

      for (var i = 0; i <datas.length; i++) {
        var types=$(datas[i]).attr('data-type');
         if(types==0){
           console.log(0,'没有图片')
         }else{
           var id=$(datas[i]).attr('data-id'); //文件id
           var sortid=$(datas[i]).attr('data-sort');  //排序id
           var fileurl=$(datas[i]).attr('data-fileurl');  //文件路径

           list.push(id); 
           var ok = {'sortid':sortid,'fileid':id,'fileurl':fileurl};
           sortarr.push(ok);
         }
      }

      if(list.length < 1){
        layer.msg('请选择图片...');
        return false;
      }

      var listfile = sortarr.sort(sequence); //排序
      var max = 0;
      //listfile 是一个数组
      for(var k = 0; k<listfile.length;k++){

        //取排序最大值做为下标
        if(max<listfile[k]["sortid"])max=listfile[k]["sortid"];
          
        if(dshow==''){          
          files.push(listfile[k]["fileid"]); 
          filesone.push(listfile[k]["fileid"]);
          filesurl.push(listfile[k]["fileurl"]);
          filesurls.push(listfile[k]["fileurl"]);
        }

      }

      //单张图片显示
      //console.log(max,listfile[max]["fileid"]);
      if(dshow==1){
        // console.log(dshow,'什么时候进来')
        //单图传输
        single_files.push(listfile[max]["fileid"]); 
        single_filesone.push(listfile[max]["fileid"]);
        single_filesurl.push(listfile[max]["fileurl"]);
        single_filesurls.push(listfile[max]["fileurl"]);

        parent.$('#single_parentIframe').val(single_files);
        parent.$('#single_parentIframe1').val(single_filesone);
        parent.$('#single_imgurl').val(single_filesurl);
        parent.$('#single_imgurls').val(single_filesurls);

        //调用父页面方法
        parent.single_getMethodValue();
      }

      //传值给父页面
      if(dshow==''){
        //多图传输
        parent.$('#parentIframe').val(files);
        parent.$('#parentIframe1').val(filesone);
        parent.$('#imgurl').val(filesurl);
        parent.$('#imgurls').val(filesurls);

        //调用父页面方法
        parent.getMethodValue();
      }
      // parent.layer.tips('Look here', '#parentIframe', {time: 5000});
    
       //关闭窗口
      parent.layer.close(index);

     });

  //写入路径并排序
  function addpush(obj){

      //console.log(obj,'*****',dshow);  

      var id = '#'+obj;
      var types=$(id).attr('data-type');
      //排序
      var i = count++;
      //console.log(types , ' ---','#'+obj);
      if(types==0){
        var types=$(id).attr('data-type',1);
        var types=$(id).attr('data-sort',i);
      }else{
       var types=$(id).attr('data-type',0);
       var types=$(id).attr('data-sort','');
      }
  }


     //测试 
    function addAll () {
      var datas =$('.tips');
      var list=new Array();
      var sortarr =new Array();

      for (var i = 0; i <datas.length; i++) {
       var types=$(datas[i]).attr('data-type');
       if(types==0){
         console.log(0)
       }else{
         var id=$(datas[i]).attr('data-id'); //文件id
         var sortid=$(datas[i]).attr('data-sort');  //排序id
         var fileurl=$(datas[i]).attr('data-fileurl');  //文件路径

         list.push(id); 
         var ok = {'sortid':sortid,'fileid':id,'fileurl':fileurl};
         sortarr.push(ok);
       }
       
    }

      var listfile = sortarr.sort(sequence); //排序

      var files = new Array();
      var filesurl = new Array();
      //listfile 是一个数组
      for(var k = 0; k<listfile.length;k++){
        files.push(listfile[k]["fileid"]);
        filesurl.push(listfile[k]["fileurl"]);
      }

      // console.log(files,'///',filesurl);
      
      // console.log(list,'....')
      
      // console.log(listfile,'...??')


    }

    //数字排序问题
    function sequence(a,b){
     return a.sortid - b.sortid;
   }
    //Array.union(a,b) 使用方法
    //并集
    // Array.union = function(a, b){ 
    //   return a.concat(b).uniquelize(); 
    // }; 