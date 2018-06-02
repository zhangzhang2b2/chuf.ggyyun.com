<?php
/**
图片管理
*/
namespace app\admin\controller;

use think\Db;
use app\common\model\FileList;
use think\Request;
use think\Validate;

class picmanagement extends Main
{

	// 图片列表
	public function index(){
		$filelist =Db::name("file")->order('id', 'desc')->paginate('48');
		$page = $filelist->render();
		$total = $filelist->total(); //总数

		// cookie("imgUrl", $this->request->url());
		
		$this->assign('filelist', $filelist);
		$this->assign('page', $page);
		$this->assign('total',$total);
		// 临时关闭当前模板的布局功能
        // $this->view->engine->layout(false);
        //标题传值
		$this->assign('item',['item1'=>'商品','item2'=>'商品列表']);
		return view('file_index');
	}

	//上传图片
	public function upload(){ 
		// 获取表单上传文件
        $files = $this->request->file('image');
  
        $data = array();
        foreach($files as $file){

        	// 移动到框架应用根目录/public/uploads/ 目录下
	        $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');

	        if ($info) {
							$item = array();
							
            	$item['url'] = date("Ymd") .'/'.$info->getFilename();

            	//缩略图？2018年1月2日11:50:43  
            	$image_root=ROOT_PATH . 'public' . DS . 'uploads/'.$item['url'];

            	$image=Image::open($image_root)->thumb(800,400)->save($image_root);
            	array_push($data,$item);
	        } else {
	            // 上传失败获取错误信息
	            $this->error($file->getError());
	        }
        }   
        model('File')->saveAll($data);
        $this->success('文件上传成功',cookie("imgUrl"));     
	}

	//添加修改
	public function showAddfile(){
		return view('add_file');
	}

	public function showAddfilelist(){
		$filelist = Db::name('file')->order('id', 'desc')->paginate('48');
		$page = $filelist->render();
		$this->assign('filelist', $filelist);
		$this->assign('page', $page);
		return view('add_file_list');
	}

	public function delalls(){
		//$id=$_GET['id'];
		$datas = input('post.');
		foreach ($datas as $k => $v) {
			foreach ($v as $ks => $vs) {
				$data = Db::name('file')->where('id','eq',$vs)->find();
				$img=ROOT_PATH . 'public' . DS .$data['url'];
				// var_dump($img);exit;die;
				if(@unlink($img)){
					Db::name('file')->where('id','eq',$vs)->delete();
				}
			}
		}
		$this->success('删除成功',cookie("imgUrl"));
	}

	public function mp3(){
        $list=Db::table('addon_shop_orders')->where('type','eq',0)->where('status','eq',1)->select();
        $count=count($list);
        return $count;
    }

    ///图片上传
		public function uplodas(){
			
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		    header("Cache-Control: no-store, no-cache, must-revalidate");
		    header("Cache-Control: post-check=0, pre-check=0", false);
		    header("Pragma: no-cache");
		 
		    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		      exit; // finish preflight CORS requests here
		    }
		    if ( !empty($_REQUEST[ 'debug' ]) ) {
		      $random = rand(0, intval($_REQUEST[ 'debug' ]) );
		      if ( $random === 0 ) {
		        header("HTTP/1.0 500 Internal Server Error");
		        exit;
		      }
		    }
		 
		    // header("HTTP/1.0 500 Internal Server Error");
		    // exit;
		    // 5 minutes execution time
		    @set_time_limit(5 * 60);
		    // Uncomment this one to fake upload time
		    // usleep(5000);
		    // Settings  //DIRECTORY_SEPARATOR /分隔符
		    // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
		    $targetDir = ROOT_PATH . 'public/uploads'.DIRECTORY_SEPARATOR;
		    $uploadDir = ROOT_PATH . 'public/uploads'.DIRECTORY_SEPARATOR.date('Ymd');
		    
		    $cleanupTargetDir = true; // Remove old files
		    $maxFileAge = 5 * 3600; // Temp file age in seconds
		    // Create target dir
		    if (!file_exists($targetDir)) {
		      @mkdir($targetDir);
		    }
		    // Create target dir
		    if (!file_exists($uploadDir)) {
		      @mkdir($uploadDir);
		    }
		    // Get a file name
		    if (isset($_REQUEST["name"])) {
		      $fileName = $_REQUEST["name"];
			 
		    } elseif (!empty($_FILES)) {
		      $fileName = $_FILES["file"]["name"];
			
		    } else {
		      $fileName = uniqid("file_");
		    }

		   
		    $oldName = $fileName;

		    $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
 			
		    // $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
		    // Chunking might be enabled
		    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
		    // Remove old temp files
		    if ($cleanupTargetDir) {
		      if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
		        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
		      }
		      while (($file = readdir($dir)) !== false) {
		        $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
		        // If temp file is current file proceed to the next
		        if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
		          continue;
		        }
		        // Remove temp file if it is older than the max age and is not the current file
		        if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
		          @unlink($tmpfilePath);
		        }
		      }
		      closedir($dir);
		    }
		 
		    // Open temp file
		    if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
		      die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		    }
		  

		    if (!empty($_FILES)) {
		      if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
		        die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		      }
		      // Read binary input stream and append it to temp file
		      if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
		        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
		      }
		    } else {
		      if (!$in = @fopen("php://input", "rb")) {
		        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
		      }
		    }
		    while ($buff = fread($in, 4096)) {
		      fwrite($out, $buff);
		    }
		    @fclose($out);
		    @fclose($in);
		    rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
		    $index = 0;
		    $done = true;
		    for( $index = 0; $index < $chunks; $index++ ) {
		      if ( !file_exists("{$filePath}_{$index}.part") ) {
		        $done = false;
		        break;
		      }
		    }

		 
		    if ( $done ) {
		      $pathInfo = pathinfo($fileName);
			 
		     // $hashStr = substr(md5($pathInfo['basename']),8,16);
		    //  $hashName = time() . $hashStr . '.' .$pathInfo['extension'];
		    $rand = md5(time() . mt_rand(0,1000));
				$hashName = $rand . '.' .$pathInfo['extension']; //$pathInfo['extension']图片格式

		       $uploadPath = $uploadDir . DIRECTORY_SEPARATOR .$hashName;
		 	//插入数据库
		  		$data = array();
		  		$type = 'image/'.$pathInfo['extension'];
		  		$savepath = date("Ymd") .'/';
		  		$datas = ['url'=>"/uploads/".$savepath.$hashName];
		  		$getid  = Db::name('file')->insertGetId($datas);
		  		
				//$sql = "INSERT INTO `add` (title) VALUES ('".$hashName."');";
				//$ok = $pdo->query($sql);   //第二种写法
		   			        
				// 打开文件写入
		     	if (!$out = @fopen($uploadPath, "wb")) {
		        	die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		      	}

		      if ( flock($out, LOCK_EX) ) {
		        for( $index = 0; $index < $chunks; $index++ ) {
		          if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
		            break;
		          }
		          while ($buff = fread($in, 4096)) {
		            fwrite($out, $buff);
		          }
		          @fclose($in);
		          @unlink("{$filePath}_{$index}.part");
		        }
		        flock($out, LOCK_UN);
		      }
		      @fclose($out);
		      $response = [
		        'success'=>true,
		        'oldName'=>$oldName,
		        'filePaht'=>$uploadPath,
		        //'fileSize'=>$data['size'],
		        'fileSuffixes'=>$pathInfo['extension'],
		        //'file_id'=>$data['id'],
		       	'getid'=>$getid,
		        ];
		
		      die(json_encode($response));
		    }
		 
		   // Return Success JSON-RPC response
		    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
		}

}
