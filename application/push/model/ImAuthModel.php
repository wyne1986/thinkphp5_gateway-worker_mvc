<?php
namespace app\push\model;
use think\Model;

class ImAuthModel extends Model{

    /*获取即时通信 无验证权限 接口地址列表*/
    public function getFreeAuth($update=false){
        $result = cache('im_free_auth');
        if(empty($result) || $update){
            $result = $this->where(['im_free'=>1,'status'=>0])->column('im_url');
            cache('im_free_auth',$result,600);
        }
        return $result;
    }

}