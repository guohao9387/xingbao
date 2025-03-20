<?php

namespace app\api\controller;

use app\model\Upload as ModelUpload;
use support\Request;


class Upload
{
    //单文件上传
    public function upload(Request $request)
    {

        $file = $request->file('file');
        $fileCategory = $request->input('file_category', "user_upload");
        $result = ModelUpload::upload($file, $fileCategory);
        return encryptedJson($result);
    }
    //多文件上传
    public function uploads(Request $request)
    {
        $files = $request->file();
        $fileCategory = $request->input('file_category', "user_upload");
        $result = ModelUpload::uploads($files, $fileCategory);
        return encryptedJson($result);
    }
    //获取阿里云上传token
    public function getAlibabaCloudOssStsToken(Request $request)
    {
        $result = ModelUpload::getAlibabaCloudOssStsToken();
        return encryptedJson($result);
    }
}
