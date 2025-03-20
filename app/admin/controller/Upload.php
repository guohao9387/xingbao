<?php

namespace app\admin\controller;

use app\model\Upload as ModelUpload;
use support\Request;


class Upload
{
    //单文件上传
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $fileCategory = $request->input('file_category');
        $result = ModelUpload::upload($file, $fileCategory);
        return json($result);
    }
    //多文件上传
    public function uploads(Request $request)
    {
        $files = $request->file();
        $fileCategory = $request->input('file_category');
        $result = ModelUpload::uploads($files, $fileCategory);
        return json($result);
    }

    public function tinymceFileUpload(Request $request)
    {
        $file = $request->file('edit');
        $reslut = ModelUpload::upload($file, "tinymce");
        if ($reslut['code'] != 200) {
            return json($reslut);
        }

        return json(['code' => 0, 'msg' => $reslut['msg'], 'data' => $reslut['data']->file_url]);
    }
}
