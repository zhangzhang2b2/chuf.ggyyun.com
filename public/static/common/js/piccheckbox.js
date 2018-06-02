// 图片管理器start
/**
*  两个id 相同bug? 怎么处理 删除
* 给一个加 i 作为处理
*/
    //初始化加载
    $(function(){
        getMethodValue(); //多张
        single_getMethodValue(); //单张
    })

    //多张
    function getMethodValue(id){

        var url = $('#imgurl').val();
        var imgarr = new Array();
        var imgarrs = new Array();

        var fileidarr = new Array();
        var parentIframearr = new Array();
        var urls = $('#imgurls').val();

        // console.log(url,urls,'==+++++'); 

        var parentIframe = $('#parentIframe').val();
        var parentIframe1 = $('#parentIframe1').val(); 

        //url && urls && 
        if(parentIframe){
        
            imgarr = url.split(','); //拆分字符串

            imgarrs = urls.split(','); 
             
            parentIframearr= parentIframe.split(','); 

            //去数组空
            for(var i = 0 ;i<parentIframearr.length;i++)
            {
                if(parentIframearr[i] == "" || typeof(parentIframearr[i]) == "undefined")
                {
                      parentIframearr.splice(i,1);
                      i= i-1;
                }
            }
            //console.log('----',parentIframe,'====',parentIframearr);
            //console.log(parentIframearr,'??????',imgarrs);
            //单次添加
            fileidarr = parentIframe1.split(','); //拆分字符串

            //循环输出标签
            var $list = $('#thelist');
            if(id){
                //删除
                for(var i=0;i<imgarrs.length;i++){
                    if(parentIframearr[i]==id){
                        parentIframearr.splice(i,1); //从数组中删除
                        imgarrs.splice(i,1);
                       
                        $("#"+id).remove(); //移除div
                    }
                }
                var a = parentIframearr.join(','); //数组转回字符串
                var b = imgarrs.join(',');
                // console.log(a,'????',b);
                $('#parentIframe').val(a);
                $('#imgurls').val(b);

            }else{
                //默认加载
                for(var i=0;i<imgarr.length;i++){
                    $li = $(
                        '<a href="javaScript:void(0)" onClick="getMethodValue('+fileidarr[i]+')">' +
                        '<div id="' + fileidarr[i] + '" class="fileidarr">' +
                        '<div class="del">点击删除</div>'+
                        '<img src="'+imgarr[i]+'" width="100" height="100">' +
                        '</div>'+
                        '</a>'
                        );
                        // $list为容器jQuery实例
                        $list.append( $li );
                }
            }
        }

    }

    //单张
    function single_getMethodValue(single_id){

        var single_url = $('#single_imgurl').val();
        var single_imgarr = new Array();
        var single_imgarrs = new Array();

        var single_fileidarr = new Array();
        var single_parentIframearr = new Array();
        var single_urls = $('#single_imgurls').val();

        // console.log(url,urls,'==+++++'); 

        var single_parentIframe = $('#single_parentIframe').val();
        var single_parentIframe1 = $('#single_parentIframe1').val(); 
        //single_url && single_urls && 
        if(single_parentIframe){
        
            single_imgarr = single_url.split(','); //拆分字符串

            single_imgarrs = single_urls.split(','); 
             
            single_parentIframearr= single_parentIframe.split(','); 

            //去数组空
            for(var i = 0 ;i<single_parentIframearr.length;i++)
            {
                if(single_parentIframearr[i] == "" || typeof(single_parentIframearr[i]) == "undefined")
                {
                      single_parentIframearr.splice(i,1);
                      i= i-1;
                }
            }
            //console.log('----',parentIframe,'====',parentIframearr);
            //console.log(parentIframearr,'??????',imgarrs);
            //单次添加
            single_fileidarr = single_parentIframe1.split(','); //拆分字符串

            //循环输出标签
            var $single_list = $('#single_thelist');
            if(single_id){
                //删除
                for(var i=0;i<single_imgarrs.length;i++){
                    if(single_parentIframearr[i]==single_id){
                        single_parentIframearr.splice(i,1); //从数组中删除
                        single_imgarrs.splice(i,1);

                        $("#"+single_id+i).remove(); //移除div   single_id+i 防止单选和多选id相同，删除不了，仅作为删除使用
                    }
                }

                console.log(single_parentIframearr,'---',single_imgarrs);

                var c = single_parentIframearr.join(','); //数组转回字符串
                var d = single_imgarrs.join(',');
                // console.log(a,'????',b);
                $('#single_parentIframe').val(c);
                $('#single_imgurls').val(d);

            }else{
                console.log(single_imgarr,'kkk');
                //默认加载
                for(var i=0;i<single_imgarr.length;i++){

                    // single_fileidarr[i]+i 防止单选和多选id相同，删除不了，仅作为删除使用

                    $li = $(
                        '<a href="javaScript:void(0)" onClick="single_getMethodValue('+single_fileidarr[i]+')">' +
                        '<div id="' + single_fileidarr[i]+i+ '" class="fileidarr">' +
                        '<div class="del">点击删除</div>'+
                        '<img src="'+single_imgarr[i]+'" width="100" height="100">' +
                        '</div>'+
                        '</a>'
                        );
                        // $list为容器jQuery实例
                        $single_list.append( $li );
                }
            }
        }
    }

    //去重
    function unique5(array){
      var r = [];
      for(var i = 0, l = array.length; i < l; i++) {
        for(var j = i + 1; j < l; j++)
          if (array[i] === array[j]) j = ++i;
      r.push(array[i]);
      }
      return r;
    }

// 图片管理器end