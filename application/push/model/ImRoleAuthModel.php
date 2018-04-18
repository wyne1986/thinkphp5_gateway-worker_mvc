<?php
namespace app\push\model;
use think\Model;

class ImRoleAuthModel extends Model{

    /*获取用户对应用户组的权限接口地址列表*/
    public function getAuth($role_id,$role_type,$update=false){
        $result = cache('im_role_auth_'.$role_type.'_'.$role_id);
        if(empty($result) || $update){
            $result = $this->alias('imroleauth')->
            join(config('database.prefix').'im_auth imauth','imroleauth.im_id=imauth.im_id','LEFT')->
            where(['imroleauth.role_type'=>$role_type,'imroleauth.role_id'=>$role_id,'imauth.im_free'=>0,'imauth.status'=>0])->column('imauth.im_url');
            cache('im_role_auth_'.$role_type.'_'.$role_id,$result,600);
        }
        return $result;
    }

}