<?php
use Yaf\Controller_Abstract;
class CUser{
	// 表名
	private static $table_name = 'c_user';
	// 查询字段
	private static $columns = array('user_id', 'user_name','topic', 'alipay', 'coupon_code', 'user_type', 'password', 'real_name', 'mobile', 'email', 'user_desc', 'login_time', 'status', 'login_ip', 'user_group', 'template','shortcuts','show_quicknote');
	//状态定义
	const ACTIVE = 1;
	const DEACTIVE = 0;
 	public static function getTableName()
 	{
		return self::$table_name;
	}
	
	public static function getUserByName($user_name) 
	{
		$db=new \yii\db\Query;
		$list = $db -> select('u.*,g.group_name') -> from(self::getTableName() ." u,". CUserGroup::getTableName() ." g") -> where("u.user_name = '$user_name' and u.user_group=g.group_id") -> one();// self::getTableName(), self::$columns, $condition );
		if ($list) {
			$list['login_time'] = CCommon::getDateTime($list['login_time']);
			return $list;
		}
		
		return array ();
	}
	
	public static function getUserById($user_id) 
	{
		if (! $user_id || ! is_numeric ( $user_id )) {
			return false;
		}
		
		$db=new \yii\db\Query;
		$condition = array('user_id' => $user_id);
		$list = $db -> select ('*') -> from(self::getTableName()) -> where($condition) -> all();
		if ($list) {
			$list[0]['login_time'] = CCommon::getDateTime($list[0]['login_time']);
			return $list [0];
		}
		
		return array ();
	}
	
	public static function setCookieRemember($encrypted,$day=7)
	{
		setcookie("c_remember",$encrypted,time()+3600*24*$day);
	}
	
	public static function getCookieRemember()
	{
		$encrypted = @$_COOKIE["c_remember"];
		$base64 = urldecode($encrypted);
		return CEncrypt::decrypt($base64);
	}
	
	public static function logout()
	{
		setcookie("c_remember","",time()-3600);
		unset(Yii::$app->session[CUserSession::SESSION_NAME]);
		unset(Yii::$app->session['c_timezone']);
	}
	
	private static function makeSql($where, $limit)
	{
	    return  "select * ,coalesce(g.group_name,'已删除') from ".self::getTableName()." u left join ".CUserGroup::getTableName()." g on u.user_group = g.group_id  $where  order by u.user_id desc $limit";
	}
	
	public static function getAllUsers( $start ='' ,$page_size='', $target = NULL) 
	{
		$db=new \yii\db\Query;
		$limit ="";
		if($page_size){
			$limit =" limit $start,$page_size ";
		}
		if ($target) { //微商渠道
		  $where = "where u.parent_user_id = " . CUserSession::getUserId() . " or u.user_id = " . CUserSession::getUserId();
		  $whereSelf = "where u.user_id = " . CUserSession::getUserId();

		  $list = $db -> createCommand() -> setSql(self::makeSql($where, $limit)) -> queryAll();
		  if (empty($list)) { //普通
		      $list = $db -> createCommand() -> setSql(self::makeSql($whereSelf, $limit)) -> queryAll();
		  }
		} else {
		  $sql = "select * ,coalesce(g.group_name,'已删除') from ".self::getTableName()." u left join ".CUserGroup::getTableName()." g on u.user_group = g.group_id order by u.user_id desc $limit";
		  $list = $db -> createCommand() -> setSql($sql) -> queryAll();
		}

		
		if (!empty($list)) {
			foreach($list as &$item){
				$item['login_time'] = CCommon::getDateTime($item['login_time']);
			}
		}
		
		if ($list) {
			return $list;
		}
		
		return array ();
	}
	
	public static function search($userId, $user_group ,$user_name, $start ='' ,$page_size='', $condition = ' ')
	{
		$db=new \yii\db\Query;
		$limit ="";
		if($page_size){
			$limit =" limit $start,$page_size ";
		}
		
		if($user_group >0  && $user_name!=""){
			$where = " where u.user_group = $user_group and u.user_name like '%$user_name%' and parent_user_id = " . $userId;
		}else{
			if($user_group>0){
				$where = " where u.user_group=$user_group and parent_user_id = " . $userId;
			}
			if($user_name!=""){
				$where = " where u.user_name like '%$user_name%' and parent_user_id = " . $userId;
			}
		}
		if (!isset($where)) {
		    $where = "where u.parent_user_id = " . CUserSession::getUserId() . " or u.user_id = " . CUserSession::getUserId();
		    $whereSelf = "where u.user_id = " . $userId;
		    $list = $db -> createCommand() -> setSql(self::makeSql($where, $limit)) -> queryAll();
		    if (empty($list)) {
		        $list = $db -> createCommand() -> setSql(self::makeSql($whereSelf, $limit)) -> queryAll();
		    }
		} else {
		    $list = $db -> createCommand() -> setSql(self::makeSql($where, $limit)) -> queryAll();
		}
		if (!empty($list)) {
			foreach($list as &$item){
				$item['login_time'] = CCommon::getDateTime($item['login_time']);
			}
		}
		if ($list) {
			return $list;
		}
		
		return array ();
	}
	
	public static function getUsersByGroup( $group_id ) 
	{
		$db=new \yii\db\Query;
		$condition = array("user_group" => $group_id);
		$list = $db->select( self::$columns) -> from(self::getTableName()) -> where($condition) -> all();
		if ($list) {
			foreach($list as &$item){
				if($item['login_time']==null){
					;
				}else{
					$item['login_time'] = CCommon::getDateTime($item['login_time']);
				}
			}
			
			return $list;
		}
		
		return array ();
	}
	
	public static function checkLogin() 
	{
		$user_info = CUserSession::getSessionInfo ();
		if (empty ( $user_info )) {
			CCommon::jumpUrl("Console/User/login");
			
			return true;
		}
	}
	
	public static function checkActionAccess($this) 
	{
		$action_url = CCommon::getActionUrl();
// 		var_dump($action_url);
		$user_info = CUserSession::getSessionInfo();
		$role_menu_url = CMenuUrl::getMenuByRole ( $user_info['user_role']);
// 		var_dump($role_menu_url);
        foreach ($role_menu_url as &$v) {
            $v = trim($v);
        }
// 		return true;
		$search_result = in_array ( $action_url, $role_menu_url );
		if (!in_array ( $action_url, $role_menu_url )) {
		    header("Location:/Console/User/error");
			return;
		}
	}
	
	public static function checkPassword($user_name, $password) 
	{
		$md5_pwd = md5 ( $password );
		$db=new \yii\db\Query;
		$condition = array("user_name" => $user_name,
							"password" => $md5_pwd,
						);
		$list = $db->select( self::$columns) -> from( self::getTableName()) -> where($condition) -> all();
		if ($list) {
			return $list [0];
		} else {
			return false;
		}
	}
	
	public static function updateUser($user_id,$user_data) 
	{
		if (! $user_data || ! is_array ( $user_data )) {
			return false;
		}
		
		$db=new \yii\db\Query;
		$condition=array("user_id"=>$user_id);
		$id = $db -> createCommand() ->update ( self::getTableName(), $user_data, $condition ) -> execute();
		return $id;
	}
	
	/**
	* 批量修改用户，如批量修改用户分组
	* user_ids 可以为无key数组，也可以为1,2,3形势的字符串
	*/
	public static function batchUpdateUsers($user_ids,$user_data) 
	{
		if (! $user_data || ! is_array ( $user_data )) {
			return false;
		}
		if(!is_array($user_ids)){
			$user_ids=explode(',',$user_ids);
		}
		
		$db=new \yii\db\Query;
		$condition=array("user_id"=>$user_ids);
		$id = $db -> createCommand() ->update ( self::getTableName(), $user_data, $condition ) -> execute();
		return $id;
	}
	
	public static function addUser($user_data) 
	{
		if (! $user_data || ! is_array ( $user_data )) {
			return false;
		}
		return DataBase::doTableInsert(self::getTableName(), $user_data , 1);
	}
	
	public static function delUser($user_id) 
	{
		if (! $user_id || ! is_numeric ( $user_id )) {
			return false;
		}
		
		$db = new \yii\db\Query;
		$condition = array("user_id"=>$user_id);
		$result = $db-> createCommand() -> delete ( self::getTableName(), $condition ) -> execute();
		return $result;
	}
	
	public static function delUserByUserName($user_name) 
	{
		if (! $user_name ) {
			return false;
		}
		
		$db = new \yii\db\Query;
		$condition = array("user_name"=>$user_name);
		$result = $db-> createCommand() -> delete ( self::getTableName(), $condition ) -> execute();
		return $result;
	}
	
	public static function count($condition = '') 
	{
		$db = new \yii\db\Query;
		if (CUserSession::getUserType() == 1) {
		    $condition['parent_user_id'] = CUserSession::getUserId();
		}
		$num = $db -> from(self::getTableName()) -> where($condition) -> count ();
		return $num;
	}
	
	public static function countSearch($user_group,$user_name) 
	{
		$db = new \yii\db\Query;
		$condition = array();
		if($user_group > 0  && $user_name!= ""){
// 			$condition['user_group'] = $user_group;
// 			$condition = array('LIKE','user_name',$user_name);
            $condition[]="user_group=$user_group";
            $condition[]="user_name like '%$user_name%'";
		}else{
			if($user_group > 0){
// 				$condition['user_group'] = $user_group;
			    $condition[]="user_group=$user_group";
			}
			if($user_name!= ""){
// 				$condition = array('LIKE','user_name',$user_name);
			    $condition[]="user_name like '%$user_name%'";
			}
		}
		if (CUserSession::getUserType() == 1) {
		    $condition[] = "parent_user_id=" . CUserSession::getUserId();
		}
		if(empty($condition)){
		    $condition=array();
		}else{
		    $condition=implode(' AND ',$condition);
		}
		$num = $db -> from(self::getTableName()) -> where($condition) -> count ();
		return $num;
	}
	
	public static function setTemplate($user_id,$template)
	{
		$user_data = array("template"=>$template);
		$ret = self::updateUser($user_id,$user_data);
		return $ret;
	}
	
	public static function loginDoSomething($user_id, $obj){
		
		$user_info = CUser::getUserById($user_id);
        if($user_info['status']!= 1){ 
            $obj->forward('Console', 'User', 'login');
			return;
		}
		
		//读取该用户所属用户组将该组的权限保存在$_SESSION中
		$user_group = CUserGroup::getGroupById($user_info['user_group']);
		$user_info['group_id'] = $user_group['group_id'];
		$user_info['user_role'] = $user_group['group_role'];
		$user_info['shortcuts_arr'] = explode(',',$user_info['shortcuts']);
		$menu = CMenuUrl::getMenuByUrl('Console/system/setting');
		if(strpos($user_group['group_role'], $menu['menu_id'])){
			$user_info['setting']=1;
		}
		
		$login_time = time();
		$login_ip = CCommon::getIp ();
		$update_data = array ('login_ip' => $login_ip, 'login_time' => $login_time );
		CUser::updateUser ( $user_info['user_id'], $update_data );
		$user_info['login_ip']=$login_ip;
		$user_info['login_time'] = CCommon::getDateTime($login_time);
		CUserSession::setSessionInfo( $user_info);
	}
	
	public static function queryLowerAgent($userId, $topic = 1)
	{
	    $db = new \yii\db\Query;
	    return $db->from(self::getTableName())
	       ->where(['parent_user_id' => $userId])
	       ->andWhere('user_id !=' . $userId)
	       ->all();
	}
	
	public static function replacementProcess($files= '', $cosFileType)
	{
	    // 	    if ($files['error'] === 0) {
	    // 	        $delPic = CosFile::delFile(self::DEFAULT_PIC, $cosFileType, Us\Config\QCloud\BUCKET);  //删除原默认头像
	    // 	        if ($delPic['code'] == 0) {
	    $result=CosFile::uploadFile($files, 0, $cosFileType, 0, 0, 0, 0);  //上传新的默认头像
	    if (!empty($result['url'])) {
	        return $result['subUrl'];
	    } else {
	        throw new InvalidArgumentException(Console\ADMIN\UPLOAD_FAILED);
	    }
	    // 	        } else {
	    // 	            throw new InvalidArgumentException(Console\ADMIN\DEFAULT_PIC_DEL_FAILED);
	    // 	        }
	    // 	    } else {
	    // 	        throw new InvalidArgumentException(Console\ADMIN\NO_FILES_UPLOAD);
	    // 	    }
	    }
}
