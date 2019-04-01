<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/3
 * Time: 10:28
 */
namespace Backend\Services;
use Api\Models\GalaxySysPermissionNew;
use Api\Models\GalaxySysRole;

class AuthServer extends BaseServer
{
    public function listDataService(){
        $result = ['msg' => '菜单列表', 'code' => 0, 'data' => false];
        $galaxySysPermissionNewModel = new GalaxySysPermissionNew();
        $result['code'] = 200;
        $result['data'] = $galaxySysPermissionNewModel->listData();
        $data = $galaxySysPermissionNewModel->listData();
        if($data){
            foreach($data as $key => $value){
                $name = $value['name'];
                $title = $value['title'];
                $data[$key]['id'] = (int)$value['id'];
                $data[$key]['pid'] = (int)$value['pid'];
                $data[$key]['checked'] = $value['checked'] == "true" ? true : false;
                $data[$key]['open'] = $value['open'] == "true" ? true : false;
                $data[$key]['status'] = (int)$value['status'];
                $data[$key]['level'] = (int)$value['level'];
                $data[$key]['platform'] = (int)$value['platform'];
                $data[$key]['name'] = $title;
                $data[$key]['title'] = $name;
            }
        }

        return ['msg' => '菜单列表', 'code' => 200, 'data' => $this->dg($data,0)];
    }

    public function addDataService($params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];
        $name = isset($params['name']) ? $params['name'] : '';
        $title = isset($params['title']) ? $params['title'] : '';

        $galaxySysPermissionNewModel = new GalaxySysPermissionNew();
        $attributes = $galaxySysPermissionNewModel->getAttributes();

        foreach($attributes as $key => $value){
            if(isset($params[$value])){
                $params[$value] = isset($params[$value]) ? trim($params[$value]) : '';
            }

            if(isset($params['name'])){
                $params['name'] = $title;
            }

            if(isset($params['title'])){
                $params['title'] = $name;
            }

            if(!isset($params['pid'])){
                $params['pid'] = 0;
            }

            if(!isset($params['level']) ||empty($params["level"]) || !in_array($params["level"],array(1,2,3))){
                $result['msg'] = '等级格式异常';
                return $result;
            }
        }

        # source = 1 表来源公众号
        $params['platform'] = "1";

        $ret = $galaxySysPermissionNewModel->add($params);
        if($ret){
            $result = ['msg' => '菜单添加成功', 'code' => 200, 'data' => $ret];
        }else{
            $result['msg'] = '菜单添加失败';
        }

        return $result;
    }

    public function saveDataService($id, $params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];
        $name = isset($params['name']) ? $params['name'] : '';
        $title = isset($params['title']) ? $params['title'] : '';

        $where = array("id = $id ");
        $galaxySysPermissionNewModel = GalaxySysPermissionNew::findFirst($where);
        if($galaxySysPermissionNewModel){
            $attributes = $galaxySysPermissionNewModel->getAttributes();

            foreach($attributes as $key => $value){
                if(isset($params[$value])) {
                    $params[$value] =  $params[$value] ? trim($params[$value]) : '';
                }

                if(isset($params['name'])){
                    $params['name'] = $title;
                }

                if(isset($params['title'])){
                    $params['title'] = $name;
                }
            }

            $ret = $galaxySysPermissionNewModel->saveData($params);
            if($ret){
                $result = ['msg' => '菜单编辑成功', 'code' => 200, 'data' => $ret];
            }else{
                $result['msg'] = '菜单编辑失败';
            }
        }else{
            $result['msg'] = '菜单ID未找到';
        }

        return $result;
    }

    public function deleteDataService($id){
        $result = ['msg' => '', 'code' => 0, 'data' => false];

        $where = array("id = $id ");
        $galaxySysPermissionNewModel = GalaxySysPermissionNew::findFirst($where);
        if($galaxySysPermissionNewModel){
            $data = ['status'=>0];
            $ret = $galaxySysPermissionNewModel->saveData($data);
            if($ret){
                $result = ['msg' => '菜单删除成功', 'code' => 200, 'data' => $ret];
            }else{
                $result['msg'] = '菜单删除失败';
            }
        }else{
            $result['msg'] = '菜单ID未找到';
        }

        return $result;

    }

//    public function addAuthAssignmentService($params){
//        $result = ['msg' => '', 'code' => 0, 'data' => false];
//        $galaxySysPermissionNewModel = new GalaxySysPermissionNew();
//        $params['roleid'] = 100; //用户角色ID
//        $params['authid'] = trim($params['auth_ids']);
//        $params['platform'] = '1';
//
//        $ret = $galaxySysPermissionNewModel->addAuthAssignmentData($params);
//        if($ret){
//            $result = ['msg' => '分配权限成功', 'code' => 200, 'data' => $ret];
//        }else{
//            $result['msg'] = '分配权限失败';
//        }
//
//        return $result;
//    }

    public function getAuthnameServer($params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];

        $galaxySysPermissionNewModel = new GalaxySysPermissionNew();
        $role_id = (int)$params['role_id'];
        $ret = $galaxySysPermissionNewModel->getAuthAssignmentData($role_id);
        if($ret){
            return $ret->authname;
        }else{
            return '';
        }
    }

    public function getAuthTreesServer($params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];

        $galaxySysPermissionNewModel = new GalaxySysPermissionNew();
        $role_id = (int)$params['role_id'];
        $ret = $galaxySysPermissionNewModel->getAuthAssignmentData($role_id);
        if($ret){
            //$authnames = "'".trim($ret->authname)."'";
            $authids = $ret->authid;
            $authArray = $galaxySysPermissionNewModel->getAuthAssignmentTreeData($authids);
            if($authArray){
                foreach($authArray as $k => $v){
                    $name = $v['name'];
                    $title = $v['title'];
                    $authArray[$k]['id'] = (int)$v['id'];
                    $authArray[$k]['pid'] = (int)$v['pid'];
                    $authArray[$k]['level'] = (int)$v['level'];
                    $authArray[$k]['title'] = $name;
                    $authArray[$k]['name'] = $title;
                }
            }

            return $this->dg($authArray,0);
        }
    }

    public function getAuthAssignmentService($params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];
        #参数判断
        if(!isset($params['role_id']) || empty($params['role_id'])){
            $result['msg'] = '参数不能为空或不存在';
            return $result;
        }

        $galaxySysPermissionNewModel = new GalaxySysPermissionNew();
        $role_id = (int)$params['role_id'];


        $where['where'] = "platform = '1' AND status = '1'";
        $list = $galaxySysPermissionNewModel->findall($where);
        if($list){
            foreach($list as $k => $v){
                $name = $v['name'];
                $title = $v['title'];
                $list[$k]['id'] = (int)$v['id'];
                $list[$k]['pid'] = (int)$v['pid'];
                $list[$k]['checked'] = $v['checked'] == 'true' ? true : false;
                $list[$k]['open'] = $v['open'] == 'true' ? true : false;
                $list[$k]['status'] = (int)$v['status'];
                $list[$k]['level'] = (int)$v['level'];
                $list[$k]['platform'] = (int)$v['platform'];
                $list[$k]['title'] = $name;
                $list[$k]['name'] = $title;
            }
        }


        $ret = $galaxySysPermissionNewModel->getAuthAssignmentData($role_id);
        if($ret) {
            $authname_array = explode(',', $ret->authname);
            if ($authname_array && $list) {
                foreach ($authname_array as $k1 => $v1) {
                    foreach ($list as $k2 => $v2) {
                        if ($v2['name'] == $v1) {
                            $list[$k2]['checked'] = true;
                        }
                    }
                }
            }
        }

        #去掉第一级checked字段
//        if($list){
//            foreach($list as $k3 => $v3){
//                if($v3['level']==1){
//                    unset($list[$k3]['checked']);
//                }
////                $list[$k3]['selected'] = ($v3['checked'] == 'true' ? true : false);
////                unset($list[$k3]['checked']);
//            }
//        }

        $list = $this->dg($list, 0);
        $result = ['msg' => '分配权限详情', 'code' => 200, 'data' => $list];

        return $result;
    }

    public function saveAuthAssignmentService($params){
        $result = ['msg' => '', 'code' => 0, 'data' => false];
        #参数判断
        if(!isset($params['role_id']) || empty($params['role_id'])){
            $result['msg'] = '参数不能为空或不存在';
            return $result;
        }

        $galaxySysPermissionNewModel = new GalaxySysPermissionNew();
        $auth_names = isset($params['auth_names']) ? $params['auth_names'] : '';
        $authids = '';
        $authnames = '';

        if($auth_names){
            foreach($auth_names as $k => $v){
                $authids .= $v['id'].',';
                $authnames .= $v['name'].',';
            }
        }

        $role_id = (int)$params['role_id'];

        $ret = $galaxySysPermissionNewModel->getAuthAssignmentData($role_id);
        if($ret){
            $params['id'] = (int)$ret->id;
            $params['roleid'] = (int)$ret->roleid; //用户角色ID
            $params['authid'] = trim($authids,',');
            $params['authname'] = trim($authnames,',');
        }else{
            $params['roleid'] = $role_id; //用户角色ID
            $params['authid'] = trim($authids,',');
            $params['authname'] = trim($authnames,',');
            $params['platform'] = '1';
        }

        unset($params['role_id']);
        unset($params['auth_names']);

        $ret = $galaxySysPermissionNewModel->saveAuthAssignmentData($params);

        if($ret){
            $result = ['msg' => '分配权限成功', 'code' => 200, 'data' => $ret];
        }else{
            $result['msg'] = '分配权限失败';
        }

        return $result;
    }

    public function getRolesDataService(){
        $result = ['msg' => '角色列表', 'code' => 200, 'data' => []];
        $galaxySysRoleModel = new GalaxySysRole();

        $ret = $galaxySysRoleModel->findall($where='', $field="*", $order="id ASC");
        if($ret){
            foreach($ret as $k => $v){
                $ret[$k]['id'] = (int)$v['id'];
            }
        }
        $result['data'] = $ret;
        return $result;
    }
}