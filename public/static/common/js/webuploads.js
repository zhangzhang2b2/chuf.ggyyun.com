    /*
        2018年1月5日15:46:49
        jh 基于webuploader.js 图片上传js
        */
    //赋值全局
    var filelistid = '';
    var inputid = '';
    var select0=new Array();
    var select1=new Array();
    var select2=new Array();
    var select3=new Array();
    var select4=new Array();
    var select5=new Array();
    var select6=new Array();
    var select7=new Array();
    var select8=new Array();
    var select9=new Array();
    var select10=new Array();

    function add(a,b,c){
       filelistid = a;  //给回上面全局
       inputid = b;
       var datas ={"a":filelistid,"b":inputid};
       return datas;
   };

$(function(){
    var $list = $('#thelist'), //返回的信息
    $btn = $('#ctlBtn');

    var uploader = WebUploader.create({
      resize: true, // 不压缩image  compress压缩       
      swf: '__common__/js/Uploader.swf', // swf文件路径
      server: 'uplodas', // 文件接收服务端。
      pick:'.picker', // 选择文件的按钮。可选
      chunked: true, //是否要分片处理大文件上传
      chunkSize:2*1024*1024, //分片上传，每片2M，默认是5M
      auto: false, //选择文件后是否自动上传
      chunkRetry : 2, //如果某个分片由于网络问题出错，允许自动重传次数
      threads:1,//上传并发数。允许同时最大上传进程数,为了保证文件上传顺序  
      runtimeOrder: 'html5,flash',
      accept: {
        title: 'file',
        extensions: 'gif,png,jpg,jpeg',//zip,rar,mp3,mp4,
        mimeTypes: 'image/*'
    }
    });
    //compress: null;//// 初始状态图片上传前不会压缩 compressSize : 0//单位字节，如果图片大小小于此值，不会采用压缩 fileSingleSizeLimit //单张大小 
     // fileSizeLimit总限制大小    // fileNumLimit:2, 上传张数

    // 当有文件被添加进队列的时候
    uploader.on( 'fileQueued', function( file ) {
        $list.append( '<div id="' + file.id + '" class="item">' +
            '<h4 class="info">' + file.name + '</h4>' +
            '<p class="state">等待上传...</p>' +
            '</div>' );
    });
    // 文件上传过程中创建进度条实时显示。
    uploader.on( 'uploadProgress', function( file, percentage ) {
        var $li = $( '#'+file.id ),
        $percent = $li.find('.progress .progress-bar');

        // 避免重复创建
        if ( !$percent.length ) {
            $percent = $('<div class="progress progress-striped active">' +
              '<div class="progress-bar" role="progressbar" style="width: 0%">' +
              '</div>' +
              '</div>').appendTo( $li ).find('.progress-bar');
        }

        $li.find('p.state').text('上传中');
        $percent.css( 'width', percentage * 100 + '%' );
    });
    // 文件上传成功
    uploader.on( 'uploadSuccess', function( file, response) {
        console.log(response); //这里可以得到后台返回的数据
        var fileid = add(filelistid,inputid); //调用上面的方法 //josn 格式

        $( '#'+file.id ).find('p.state').text('已上传');
        $( '#'+file.id ).find('div.state').text('已上传');
        switch(fileid.a)
        {
            case "fileList0":
            select0.push(response.getid);
            $('#'+fileid.b).val(select0);
            break;
            case "fileList1":
            select1.push(response.getid);
            $('#'+fileid.b).val(select1);
            break;
            case "fileList2":
            select2.push(response.getid);
            $('#'+fileid.b).val(select2);
            break;
            case "fileList3":
            select3.push(response.getid);
            $('#'+fileid.b).val(select3);
            break;
            case "fileList4":
            select4.push(response.getid);
            $('#'+fileid.b).val(select4);
            break;
            case "fileList5":
            select5.push(response.getid);
            $('#'+fileid.b).val(select5);
            break;
            case "fileList6":
            select6.push(response.getid);
            $('#'+fileid.b).val(select6);
            break;
            case "fileList7":
            select7.push(response.getid);
            $('#'+fileid.b).val(select7);
            break;
            case "fileList8":
            select8.push(response.getid);
            $('#'+fileid.b).val(select8);
            break;
            case "fileList9":
            select9.push(response.getid);
            $('#'+fileid.b).val(select9);
            break;
            case "fileList10":
            select10.push(response.getid);
            $('#'+fileid.b).val(select10);
            break;
        }
        
    });

    // 文件上传失败，显示上传出错
    uploader.on( 'uploadError', function( file ) {
        $( '#'+file.id ).find('p.state').text('上传出错');
        $( '#'+file.id ).find('div.state').text('上传出错');
        $( '#'+file.id ).addClass('stateerro');
    });
    // 完成上传完
    uploader.on( 'uploadComplete', function( file ) {
        $( '#'+file.id ).find('.progress').fadeOut();
    });

    $btn.on('click', function () {
        if ($(this).hasClass('disabled')) {
            return false;
        }
        uploader.upload();
        // if (state === 'ready') {
        //     uploader.upload();
        // } else if (state === 'paused') {
        //     uploader.upload();
        // } else if (state === 'uploading') {
        //     uploader.stop();
        // }
    });

    // 当有文件添加进来的时候
    uploader.on( 'fileQueued', function( file ) {

        var fileid = add(filelistid,inputid); //调用上面的方法
        //josn 格式
        var $list = $("#"+fileid.a), 
        
        $li = $(
            '<div id="' + file.id + '" class="file-item thumbnail">' +
            '<img class="img">' +
            '<div class="info">' + file.name + '</div>' +
            '<div class="state">等待上传...</div>' +
            '</div>'
            ),
        $img = $li.find('img');

        // $list为容器jQuery实例
        $list.append( $li );

        // 创建缩略图
        // 如果为非图片文件，可以不用调用此方法。
        // thumbnailWidth x thumbnailHeight 为 100 x 100
        uploader.makeThumb( file, function( error, src ) {
            if ( error ) {
                $img.replaceWith('<span>不能预览</span>');
                return;
            }

            $img.attr( 'src', src );
        }, 100, 100 );
    });


    // 文件上传过程中创建进度条实时显示。
    uploader.on( 'uploadProgress', function( file, percentage ) {
        var $li = $( '#'+file.id ),
        $percent = $li.find('.progress span');

        // 避免重复创建
        if ( !$percent.length ) {
            $percent = $('<p class="progress"><span></span></p>')
            .appendTo( $li )
            .find('span');
        }

        $percent.css( 'width', percentage * 100 + '%' );
    });

    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    uploader.on( 'uploadSuccess', function( file ) {
        $( '#'+file.id ).addClass('upload-state-done');
    });

    // 文件上传失败，显示上传出错。
    uploader.on( 'uploadError', function( file ) {
        var $li = $( '#'+file.id ),
        $error = $li.find('div.error');

        // 避免重复创建
        if ( !$error.length ) {
            $error = $('<div class="error"></div>').appendTo( $li );
        }

        $error.text('上传失败');
    });

    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on( 'uploadComplete', function( file ) {
        $( '#'+file.id ).find('.progress').remove();
    });


});