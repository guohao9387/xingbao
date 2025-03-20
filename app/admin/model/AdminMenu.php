<?php

namespace app\admin\model;

use app\model\Admin;
use app\model\AdminMenu as ModelAdminMenu;
use app\model\AdminRole;
use think\Model;


class AdminMenu extends Model
{
    //根据用户角色获取菜单
    public static function getMenuData($data)
    {
        $list = ModelAdminMenu::field("mid,pid,title,icon,type,page_href,open_type,sort")->where('show_status', 1)->order('sort desc')->select();
        $checked_data = [];
        if (!empty($data['authInfo']['role_id'])) {
            if ($data['authInfo']['role_id'] == 1) {
                $checked_data = array_column($list->toArray(), 'mid');
            } else {
                $admin_role_info = AdminRole::cache(10)->getByRid($data['authInfo']['role_id']);
                $checked_data = explode(',', $admin_role_info['bind_mid_list']);
            }
        }
        $data = AdminMenu::MenuChild(0, $checked_data, $list);
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    //根据用户角色获取菜单
    public static function getAuthMenuData($data)
    {
        $list = ModelAdminMenu::field("mid,pid,title,icon,type,page_href,open_type,sort")->order('sort desc')->select();
        $checked_data = [];
        if (!empty($data['rid'])) {
            $checked_string = AdminRole::where('rid', $data['rid'])->value('bind_mid_list');
            $checked_data = explode(',', $checked_string);
        }
        $data = AdminMenu::MenuAuthChild(0, $checked_data, $list);
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    //根据所有菜单
    public static function getAllMenuData()
    {
        $list = ModelAdminMenu::field("mid,pid,title,icon,type,page_href,open_type,sort")->order('sort desc')->select();
        $checked_data = [];
        $data = AdminMenu::MenuAuthChild(0, $checked_data, $list);
        return ['code' => 200, 'msg' => '获取成功', 'data' => $data];
    }
    //递归组装菜单数据结构
    private  static function MenuChild($pid, $checked_data, $menuList)
    {
        $treeList = [];
        foreach ($menuList as $k => $v) {
            if ($pid == $v['pid']) {
                unset($menuList[$k]);
                if (!empty($menuList)) {
                    $child = AdminMenu::MenuChild($v['mid'], $checked_data, $menuList);
                    if (!empty($child)) {
                        $v['children'] = $child;
                    }
                }
                // todo 后续此处加上用户的权限判断
                $node = [];
                $node['id'] = $v['mid'];
                $node['title'] = $v['title'];
                $node['icon'] = $v['icon'];
                $node['type'] =  $v->getData('type');
                $node['openType'] = $v['open_type'];
                $node['href'] = $v['page_href'];
                if (!empty($v['children'])) {
                    $node['children'] = $v['children'];
                }
                if (in_array($v['mid'], $checked_data)) {
                    $treeList[] = (object)$node;
                }
            }
        }
        return $treeList;
    }

    private  static function MenuAuthChild($pid, $checked_data, $menuList)
    {
        $treeList = [];
        foreach ($menuList as $k => $v) {
            if ($pid == $v['pid']) {
                unset($menuList[$k]);
                if (!empty($menuList)) {
                    $child = AdminMenu::MenuAuthChild($v['mid'], $checked_data, $menuList);
                    if (!empty($child)) {
                        $v['children'] = $child;
                    }
                }
                // todo 后续此处加上用户的权限判断
                $node = [];
                $node['id'] = $v['mid'];
                $node['title'] = $v['title'];
                $node['field'] = 'bind_mid_list[]';
                if (!empty($v['children'])) {
                    $node['children'] = $v['children'];
                }
                $node['href'] = '';
                $node['spread'] = false;
                $node['checked'] = false;
                if (in_array($v['mid'], $checked_data)) {
                    $node['checked'] = true;
                }
                $node['disabled'] = false;
                $treeList[] = (object)$node;
            }
        }
        return $treeList;
    }



    public static function getAdminMenuList($data)
    {

        $list =  ModelAdminMenu::order('sort desc')->select();
        return ['code' => 200, 'msg' => '获取成功', 'data' => $list];
    }

    public static function getAdminMenu($data)
    {
        $validate = new \app\validate\AdminMenu;
        if (!$validate->scene('adminGet')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }

        $info = ModelAdminMenu::where('rid', $data['rid'])->findOrEmpty();
        if ($info->isEmpty()) {
            return ['code' => 201, 'msg' => '信息不存在', 'data' => null];
        }
        return ['code' => 200, 'msg' => '获取成功', 'data' => $info];
    }
    public static function createAdminMenu($data)
    {
        $validate = new \app\validate\AdminMenu;
        if (!$validate->scene('adminCreate')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelAdminMenu::create($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '创建失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '创建成功', 'data' => null];
    }
    public static function modifyAdminMenu($data)
    {
        $validate = new \app\validate\AdminMenu;
        if (!$validate->scene('adminModify')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        $result = ModelAdminMenu::update($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '更新失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '更新成功', 'data' => null];
    }
    public static function deleteAdminMenu($data)
    {
        $validate = new \app\validate\AdminMenu;
        if (!$validate->scene('adminDelete')->check($data)) {
            return ['code' => 201, 'msg' => $validate->getError(), 'data' => null];
        }
        if ($data['rid'] == 1) {
            return ['code' => 201, 'msg' => '该角色无法删除', 'data' => null];
        }
        $result = ModelAdminMenu::destroy($data);
        if (!$result) {
            return ['code' => 201, 'msg' => '删除失败', 'data' => null];
        }
        return ['code' => 200, 'msg' => '删除成功', 'data' => null];
    }
}
