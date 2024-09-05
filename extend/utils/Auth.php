<?php
namespace utils;


use app\common\model\AuthRule;
use think\facade\Db;
use think\facade\Config;

class Auth
{

    //默认配置
    protected $_config = array(
        'AUTH_ON'           => true, // 认证开关
        'AUTH_TYPE'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP'        => 'auth_group', // 用户组数据表名
        'AUTH_GROUP_ACCESS' => 'auth_group_access', // 用户-用户组关系表
        'AUTH_RULE'         => 'auth_rule', // 权限规则表
        'AUTH_USER'         => 'admin', // 用户信息表
    );

    public function __construct()
    {
        if (Config::get('AUTH_CONFIG')) {
            //可设置配置项 AUTH_CONFIG, 此配置项为数组。
            $this->_config = array_merge($this->_config, Config::get('AUTH_CONFIG'));
        }
    }

    public function check($name, $admin_id, $type = 1, $mode = 'url', $relation = 'or')
    {
        if (!$this->_config['AUTH_ON']) {
            return true;
        }
        //根据路径查询对应权限数据
        $authRule = (new AuthRule())->where('name', $name)->find();
//        $curPermissions = saveToCache($admin_id);//获取用户当前权限
//        if($curPermissions && in_array($authRule["id"], $curPermissions)){
//            return true;
//        }
//        $curPermissions = [];
        $authList = $this->getAuthList($admin_id, $type, "M,C,B"); //获取用户需要验证的所有有效规则列表
        if(!$authRule){
//            $isExist = false;
//            foreach ($authList as $auth) {
//                if($name == $auth['name']){
//                    $isExist = true;
//                    break;
//                }
//            }
//            if(!$isExist){
//                return true;
//            }
            return false;
        }

        foreach ($authList as $auth) {
            if($auth["id"] == $authRule["id"]){
//                array_push($curPermissions, $auth["id"]);
//                saveToCache($admin_id, $curPermissions);
                return true;
            }
        }
        return false;
    }


    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  admin_id int     用户id
     * @return array       用户所属的用户组 array(
     *     array('admin_id'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *     ...)
     */
    public function getGroups($admin_id)
    {
        static $groups = array();
        if (isset($groups[$admin_id])) {
            return $groups[$admin_id];
        }

        $user_groups = Db::name($this->_config['AUTH_GROUP_ACCESS'])->alias('a')
            ->where("a.admin_id='$admin_id' and g.status='1'")
            ->join($this->_config['AUTH_GROUP'] . " g", "a.group_id=g.id")
            ->field('admin_id,group_id,title,rules')->select();
        $groups[$admin_id] = $user_groups ?: [];
        return $groups[$admin_id];
    }

    /**
     * 获得权限列表
     * @param integer $admin_id  用户id
     * @param integer $type
     */
    public function getAuthList($admin_id, $type=1, $types="M,C")
    {
        static $_authList = array(); //保存用户验证通过的权限列表
        $t                = implode(',', (array) $type);
        if (isset($_authList[$admin_id . $t])) {
            return $_authList[$admin_id . $t];
        }
        if (2 == $this->_config['AUTH_TYPE'] && isset($_SESSION['_AUTH_LIST_' . $admin_id . $t])) {
            return $_SESSION['_AUTH_LIST_' . $admin_id . $t];
        }

        //读取用户所属用户组
        $groups = $this->getGroups($admin_id);
        $ids    = array(); //保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }

        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$admin_id . $t] = array();
            return array();
        }

        $typeArr = explode(",", $types);
        //读取用户组所有权限规则
        $rules = Db::name('auth_rule')->where('id','in',implode(',',$ids))
            ->where('status',1)->whereIn('type', $typeArr)->order('sort asc, id asc')->select()->toArray();

        $_authList[$admin_id . $t] = $rules;
        if (2 == $this->_config['AUTH_TYPE']) {
            //规则列表结果保存到session
            $_SESSION['_AUTH_LIST_' . $admin_id . $t] = $rules;
        }
        return $rules;
    }

    /**
     * 获得用户资料,根据自己的情况读取数据库
     */
    protected function getUserInfo($admin_id)
    {
        static $userinfo = array();
        if (!isset($userinfo[$admin_id])) {
            $userinfo[$admin_id] = Db::name($this->_config['AUTH_USER'])->where('admin_id', $admin_id)->find();
        }
        return $userinfo[$admin_id];
    }

}
