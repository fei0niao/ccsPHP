<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class OtherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * 文件上传
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * TODO 文件压缩、安全检测
     */
    public function uploadImg(Request $request)
    {
        if ($this->user->role_id != 1) {
            return failReturn("无权上传文件");
        }
        $file = $request->file('file');
        // 文件是否上传成功
        if(!$file || !$file->isValid()){
            return failReturn("上传失败,请检查上传的文件是否合法");
        }
        // 获取文件相关信息
        $originalName = $file->getClientOriginalName(); // 文件原名
        $ext = $file->getClientOriginalExtension();     // 扩展名
        $realPath = $file->getRealPath();   //临时文件的绝对路径
        $type = $file->getClientMimeType();     // image/jpeg
        if ($type != "image/jpeg") {
            return failReturn("请上传image/jpeg的图片格式");
        }
        $fileName = date('YmdHis') . uniqid() . ".$ext";
        $rs = ossUpload($fileName, file_get_contents($realPath), "jg-uploadImg");
        if(!$rs) return failReturn("服务器错误，上传失败");
        return jsonReturn($rs);
    }
}
