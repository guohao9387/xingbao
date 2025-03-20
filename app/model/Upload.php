<?php



namespace app\model;

use Shopwwi\WebmanFilesystem\Facade\Storage;



use \Exception;


class Upload
{

    public static function upload($file, $fileCategory)
    {

        if (empty($file)) {
            return ['code' => 201, 'msg' => '上传文件不存在', 'data' => null];
        }
        if (is_array($file)) {
            return ['code' => 201, 'msg' => '请上传单文件', 'data' => null];
        }
        //单文件判断
        try {
            $result = Storage::path('storage/upload/' . $fileCategory)->upload($file);
        } catch (\Exception $e) {
            return ['code' => 201, 'msg' =>  $e->getMessage(), 'data' => $e];
        }
        return ['code' => 200, 'msg' => '上传成功', 'data' => $result];
    }
    public static function uploads($files, $fileCategory)
    {
        if (empty($files['file'])) {
            return ['code' => 201, 'msg' => '上传文件不存在1', 'data' => null];
        }
        if (!is_array($files['file'])) {
            return ['code' => 201, 'msg' => '请上传文件2', 'data' => null];
        }
        try {
            //uploads 第二个参数为限制文件数量 比如设置为10 则只允许上传10个文件 第三个参数为允许上传总大小 则本列表中文件总大小不得超过设定
            $result = Storage::path('storage/upload/' . $fileCategory)->uploads($files['file'], 10, 1024 * 1024 * 100);
        } catch (\Exception $e) {
            return ['code' => 201, 'msg' =>  $e->getMessage(), 'data' => $result];
        }
        return ['code' => 200, 'msg' => '上传成功', 'data' => $result];
    }



    //本地转网络地址
    public static  function storageNetworkAddress($file_path)
    {
        if (empty($file_path)) {
            return '';
        } elseif (strrpos($file_path, 'http') !== false) {
            return $file_path;
        } else {
            return  Storage::url($file_path);
        }
    }

    //网络地址转本地地址
    public static  function storageLocalAddress($file_path)
    {
        $systemConfigStorageData = config('plugin.shopwwi.filesystem.app.storage');
        if (empty($file_path)) {
            return '';
        } else {
            $file_path = str_replace($systemConfigStorageData[config('plugin.shopwwi.filesystem.app.default')]['url'], "", $file_path);
            $file_path = preg_replace('/\/+/', '/', $file_path);
            $file_path = str_replace("/storage", "storage", $file_path);
            return  $file_path;
        }
    }
}
