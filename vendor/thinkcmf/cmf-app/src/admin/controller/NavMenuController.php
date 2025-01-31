<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: kane <chengjin005@163.com> 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\NavMenuModel;
use cmf\controller\AdminBaseController;
use tree\Tree;

/**
 * Class NavMenuController 前台菜单管理控制器
 * @package app\admin\controller
 */
class NavMenuController extends AdminBaseController
{
    /**
     * 导航菜单
     * @adminMenu(
     *     'name'   => '导航菜单',
     *     'parent' => 'admin/Nav/index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '导航菜单',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $intNavId     = $this->request->param("nav_id", 0, 'intval');
        $navMenuModel = new NavMenuModel();

        if (empty($intNavId)) {
            $this->error("请指定导航!");
        }

        $objResult = $navMenuModel->where("nav_id", $intNavId)->order(["list_order" => "ASC"])->select();
        $arrResult = $objResult ? $objResult->toArray() : [];
        $this->assign('menus', $arrResult);

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $array = [];
        foreach ($arrResult as $r) {
            $r['str_manage'] = '<a class="btn btn-xs btn-primary" href="' . url("NavMenu/add", ["parent_id" => $r['id'],
                                                                                                "nav_id"    => $r['nav_id']]) . '">' . lang('ADD_SUB_MENU') . '</a>
            <a class="btn btn-xs btn-primary" href="' . url("NavMenu/edit", ["id"        => $r['id'],
                                                                             "parent_id" => $r['parent_id'],
                                                                             "nav_id"    => $r['nav_id']]) . '">' . lang('EDIT') . '</a> 
            <a class="btn btn-xs btn-danger js-ajax-delete" href="' . url("NavMenu/delete", ["id"     => $r['id'],
                                                                                             'nav_id' => $r['nav_id']]) . '">' . lang('DELETE') . '</a> ';
            $r['status']     = $r['status'] ? "显示" : "隐藏";
            $array[]         = $r;
        }

        $tree->init($array);
        $str = "<tr>
            <td><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input input-order'></td>
            <td>\$id</td>
            <td >\$spacer\$name</td>
            <td>\$status</td>
            <td>\$str_manage</td>
        </tr>";

        $categories = $tree->getTree(0, $str);

        $this->assign("categories", $categories);
        $this->assign('nav_id', $intNavId);

        return $this->fetch();
    }

    /**
     * 添加导航菜单
     * @adminMenu(
     *     'name'   => '添加导航菜单',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'order'  => 10000,
     *     'hasView'=> true,
     *     'icon'   => '',
     *     'remark' => '添加导航菜单',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $navMenuModel = new NavMenuModel();
        $intNavId     = $this->request->param('nav_id', 0, 'intval');
        $intParentId  = $this->request->param('parent_id', 0, 'intval');
        $objResult    = $navMenuModel->where('nav_id', $intNavId)->order(['list_order' => 'ASC'])->select();
        $arrResult    = $objResult ? $objResult->toArray() : [];

        $tree       = new Tree();
        $tree->icon = ['&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ '];
        $tree->nbsp = '&nbsp;';
        $array      = [];

        foreach ($arrResult as $r) {
            $r['str_manage'] = '<a href="' . url("NavMenu/add", ["parent_id" => $r['id']]) . '">' . lang('ADD_SUB_MENU') . '</a> | <a href="'
                . url("NavMenu/edit", ["id" => $r['id']]) . '">' . lang('EDIT') . '</a> | <a class="J_ajax_del" href="'
                . url("NavMenu/delete", ["id" => $r['id']]) . '">' . lang('DELETE') . '</a> ';
            $r['status']     = $r['status'] ? lang('DISPLAY') : lang('HIDDEN');
            $r['selected']   = $r['id'] == $intParentId ? 'selected' : '';
            $array[]         = $r;
        }

        $tree->init($array);
        $str      = "<option value='\$id' \$selected>\$spacer\$name</option>";
        $navTrees = $tree->getTree(0, $str);
        $this->assign('nav_trees', $navTrees);

        $navs = $navMenuModel->selectNavs();
        $this->assign('navs', $navs);

        $this->assign('nav_id', $intNavId);
        return $this->fetch();
    }

    /**
     * 添加导航菜单提交保存
     * @adminMenu(
     *     'name'   => '添加导航菜单提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加导航菜单提交保存',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $navMenuModel = new NavMenuModel();
            $arrData      = $this->request->post();

            if (isset($arrData['external_href'])) {
                $arrData['href'] = htmlspecialchars_decode($arrData['external_href']);
            } else {
                $arrData['href'] = htmlspecialchars_decode($arrData['href']);
                $arrData['href'] = base64_decode($arrData['href']);
            }

            unset($arrData['external_href']);

            $navMenuModel->save($arrData);

            $this->success(lang('ADD_SUCCESS'), url('NavMenu/index', ['nav_id' => $arrData['nav_id']]));
        }
    }

    /**
     * 编辑导航菜单
     * @adminMenu(
     *     'name'   => '编辑导航菜单',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑导航菜单',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $navMenuModel = new NavMenuModel();
        $intNavId     = $this->request->param('nav_id', 0, 'intval');
        $intId        = $this->request->param('id', 0, 'intval');
        $intParentId  = $this->request->param('parent_id', 0, 'intval');
        $objResult    = $navMenuModel
            ->where('nav_id', $intNavId)
            ->where('id', "<>", $intId)
            ->order(['list_order' => 'ASC'])
            ->select();
        $arrResult    = $objResult ? $objResult->toArray() : [];

        $tree       = new Tree();
        $tree->icon = ['&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $array      = [];
        foreach ($arrResult as $r) {
            $r['selected'] = $r['id'] == $intParentId ? 'selected' : '';
            $array[]       = $r;
        }

        $tree->init($array);
        $str       = "<option value='\$id' \$selected>\$spacer\$name</option>";
        $nav_trees = $tree->getTree(0, $str);
        $this->assign('nav_trees', $nav_trees);

        $objNav = $navMenuModel->where('id', $intId)->find();
        $arrNav = $objNav ? $objNav->toArray() : [];

        $arrNav['href_old'] = $arrNav['href'];

        if (strpos($arrNav['href'], '{') === 0 || $arrNav['href'] == 'home') {
            $arrNav['href'] = base64_encode($arrNav['href']);
        }

        $this->assign($arrNav);

        $navs = $navMenuModel->selectNavs();
        $this->assign('navs', $navs);

        $this->assign('nav_id', $intNavId);
        $this->assign('parent_id', $intParentId);

        return $this->fetch();
    }

    /**
     * 编辑导航菜单提交保存
     * @adminMenu(
     *     'name'   => '编辑导航菜单提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑导航菜单提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $navMenuModel = new NavMenuModel();
            $intId        = $this->request->param('id', 0, 'intval');
            $arrData      = $this->request->post();

            if (isset($arrData['external_href'])) {
                $arrData['href'] = htmlspecialchars_decode($arrData['external_href']);
            } else {
                $arrData['href'] = htmlspecialchars_decode($arrData['href']);
                $arrData['href'] = base64_decode($arrData['href']);
            }

            unset($arrData['external_href']);

            $navMenuModel->where('id', $intId)->update($arrData);

            $this->success(lang('EDIT_SUCCESS'), url('NavMenu/index', ['nav_id' => $arrData['nav_id']]));
        }
    }

    /**
     * 删除导航菜单
     * @adminMenu(
     *     'name'   => '删除导航菜单',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除导航菜单',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        if ($this->request->isPost()) {
            $navMenuModel = new NavMenuModel();

            $intId    = $this->request->param('id', 0, "intval");
            $intNavId = $this->request->param('nav_id', 0, "intval");

            if (empty($intId)) {
                $this->error(lang('NO_ID'));
            }

            $count = $navMenuModel->where('parent_id', $intId)->count();
            if ($count > 0) {
                $this->error('该菜单下还有子菜单，无法删除！');
            }

            $navMenuModel->where('id', $intId)->delete();
            $this->success(lang('DELETE_SUCCESS'), url('NavMenu/index', ['nav_id' => $intNavId]));
        }
    }

    /**
     * 导航菜单排序
     * @adminMenu(
     *     'name'   => '导航菜单排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '导航菜单排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $navMenuModel = new NavMenuModel();
        $status       = parent::listOrders($navMenuModel);
        if ($status) {
            $this->success(lang('Sort update successful'));
        } else {
            $this->error(lang('Sort update faild'));
        }
    }


}
