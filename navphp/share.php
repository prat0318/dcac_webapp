<?php
$dir = @$_REQUEST['dir'];
$ajax=@$_REQUEST['ajax'];
$file=@$_REQUEST['file'];
$grpuser = @$_REQUEST['grpuser'];
$perm_changed = @$_REQUEST['perm_changed'];
$loggeduser = @$_SESSION['loggeduser'];
$grpuser=base64_decode($grpuser);
$perm_changed = base64_decode($perm_changed);

include_once("config.php");
include_once("functions.php");

$reply=0;

authenticate(); //user login
if($GLOBALS['rdonly']) die("|0|Warning: Working in read-only mode!|");
chdir($dir);


if(!$multi_user&&!$enable_login) $msg="Share Feature Not supported in Non Multi User and No login settings!";
else if(!$multi_user&&($user_i==$user)&&$enable_login){
        $msg="Share Feature Not supported in Non Multi User settings!";
}
else if($multi_user){
        mysql_connect($mysql_server,$mysql_user,$mysql_passwd,false) or die("MySQL authentication failure!");
        mysql_query("use $mysql_db");

        $length = 10;
        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        $group_name = $randomString;
        $members=explode( ',', $grpuser);
        $memberAttrs = array();

        foreach ($members as $member) {
            $memberAttrs[] = ".apps.navphp.u.{$member}";
            $result=mysql_query("select * from $mysql_table where user='$member' ");
		    if(mysql_num_rows($result)>0){
                /*TODO: Add into ta for loop to be able to insert multiple values in the DB in thr group*/
                $result=mysql_query("insert ignore into $group_table (`user`, `group`, `owner`) values ('$member', '$group_name', '$user') ") or die(mysql_error());
            }else{
                goto invalid;
            }
        }
        chdir($groups_path);

        if (!posix_access($user, POSIX_F_OK)) {
            if (!mkdir($user, 0)) {
                goto out;
            }
            $user_realpath = realpath($user);
            $groupDirFD = dcac_open($user_realpath, 0); // O_RDONLY: 0
            if ($groupDirFD < 0) {
                goto out;
            }
                
            if (dcac_set_file_exacl($user_realpath, '.apps.navphp.common') < 0) {
                goto out;
            }
                
        }
            $userGroupDir = realpath($user);
            /* now the group file (gateway) itself */
            $userGroupFile = $userGroupDir."/{$group_name}";
            @unlink($userGroupFile);
            
            $userAttr = ".apps.navphp.u.{$user}";
            $groupAttr = "{$userAttr}.g.{$group_name}";
            if (dcac_add_any_attr($groupAttr, DCAC_ADDMOD) < 0) {
                goto out;
            }

            $attrFD = dcac_get_attr_fd($groupAttr);
            $memberAttrs = array();
            foreach ($members as $member) {
                $memberAttrs[] = ".apps.navphp.u.{$member}";
            }
            $memberAttrs[] = ".apps.navphp.u.{$user}";
            $groupACL = join('|',$memberAttrs);
            
            /* create the file for the group */
            ini_set('track_errors', 1);
            if (!($f = fopen($userGroupFile, "w"))) {
                goto out;
            }
            fclose($f);
            if (!($f = dcac_open($userGroupFile, 0))) {
                goto out;
            }
            if (dcac_set_attr_acl($attrFD,$f, $groupACL, $userAttr) < 0) {
                goto out_drop;
            }
            /* Need to be able to read file to open it */
            if (dcac_set_file_rdacl($userGroupFile, $groupACL) < 0){
                goto out_drop;
            }

            $filepath=$dir.'/'.$file;
            if($perm_changed[0]=='1'){
                if(dcac_set_file_rdacl($filepath, $groupACL)<0){
                    goto out;
                }
            }
            if($perm_changed[1]=='1'){
                if(dcac_set_file_wracl($filepath, $groupACL)<0){
                    goto out;
                }
            }
            if($perm_changed[2]=='1'){
                if(dcac_set_file_exacl($filepath, $groupACL)<0){
                    goto out;
                }
            }
            if($perm_changed[3]=='1'){
                if(dcac_set_file_mdacl($filepath, $groupACL)<0){
                    goto out;
                }
            }
            $msg ="Group Created successfully";
            $reply=1;
            goto success;
            out_drop:
            out:
                if (dcac_drop_attr($groupAttr) < 0) {
                }
                  if ($f >= 0) {
                     dcac_close($f);
                  }
                  if ($groupDirFD >= 0) {
                     dcac_close($groupDirFD);
                  }
                if(!$reply) {
                    $reply=-1;
                    $msg="Error while setting group permissions the group";
                }
            goto success;
            invalid:
            $reply=-1;
			$msg="Invalid User!!";
}
success:
if($ajax){
    expired();
    print"|$reply|$msg|";
    if($reply) filestatus($file)."|";
}
?>
