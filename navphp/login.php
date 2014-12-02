<?php
#---------------------------
# PHP Navigator 4.43
# dated: June 2, 2011
# Coded by: Cyril Sebastian,
# Kerala,India
# Modified by: Paul Wratt
# web: navphp.sourceforge.net
#---------------------------


dcac_unlock();

  if (dcac_set_def_rdacl('.apps.navphp.common') < 0)
     nice_die('dcac_set_def_rdacl (dcac init)');
  if (dcac_set_def_wracl('.apps.navphp.common') < 0)
     nice_die('dcac_set_def_wracl (dcac init)');
  if (dcac_set_def_exacl('.apps.navphp.common') < 0)
     nice_die('dcac_set_def_exacl (dcac init)');

session_start();
include_once("config.php");
include_once("functions.php");

function _password_valid($user,$passwd){
      $descs = array(
                     0 => array("pipe", "r")
                     );

      $process = proc_open("unix_chkpwd $user", $descs, $pipes);

      fwrite($pipes[0], $passwd . "\0");

      $return_value = proc_close($process);

      if ($return_value == 0)
         return true;
      /* Note: this could be due to an error in accessing the password
         information, not necessarily an invalid password */
      return false;
   }

   function _tryLogon($user,$pass){
      $rc = false;

      /* This is where we have a mechanism in PHP for a lower-level
         login, this will cause the OS user to change */

      /* set uid to that of "nobody" if we fail? */
      $rc = _password_valid($user,$pass);
      if ($rc) {
         $pwnam = posix_getpwnam($user);
         $new_uid = $pwnam["uid"];

         /* see documentation about process supplementary group IDs
            for where a lot of this nonsense comes from */

         /* it seems you cannot "use the privileges of groups you (via
            getuid or geteuid) belong to" automatically */
         if (!posix_initgroups($pwnam['name'],$pwnam['gid'])) {
            $status = "fail";
            nice_die("Cannot set groups!");

            /* don't allow "partial success" in logging in, so act
               like the whole thing failed (the log will tell us the
               difference) */
            $rc = false;
            goto out;
         } else {
            $status = "success";
         }

         if (!posix_setuid($new_uid)) {
            $status = "fail";
            nice_die("Cannot set user!");

            /* see above */
            $rc = false;
            goto out;
         } else {
            $status = "success";
         }
      } else {
      }

   out:
      return $rc;
   }

   function checkPass($user,$pass){
      return _tryLogon($user,$pass);
   }

// verifies the login

if(@$_SESSION['loggedin']) 
{
header("Location:index.php"); 	// redirect if already logged in
exit;
}

#------Not logged in --------- 
// cleans up the users' input
$user_i = clean_input(@$_POST['user']);
$passwd_i = clean_input(@$_POST['passwd']);
$action = clean_input(@$_POST['action']);

/// user verification
if(!$multi_user&&!$enable_login) $login=true;

else if(!$multi_user&&($user_i==$user)&&($passwd_i==$passwd)&&$enable_login) 
{
	$login=true;
}
else if($multi_user)
{ 
    if(false) { # Use Mysql auth instead of OS user auth

        $login = checkPass($user_i, $passwd_i);
        $homedir = "/home/".$user_i;

    } else {
	mysql_connect($mysql_server,$mysql_user,$mysql_passwd) or die("MySQL authentication failure!");
	mysql_query("use $mysql_db");
	$result=mysql_query("select * from $mysql_table where user='$user_i' AND passwd='$passwd_i'");
	  
	if(mysql_num_rows($result)>0)
	   {
	   $login=true;
	   $row=mysql_fetch_array($result,MYSQL_NUM);
	   $homedir=$row[2];
	   $rdonly=$row[3];

	   # allow short paths in db
	     if(strpos($homedir,"/")===false)
		$homedir = $_SERVER['DOCUMENT_ROOT']."/".$homedir;
	     elseif(strpos($homedir,"/")===0)
		$homedir = $homedir; //no chnage
	     elseif(strstr($homedir,"/")!=$homedir || strstr($homedir,"./")!=$homedir || strstr($homedir,"../")!=$homedir)
		$homedir = $_SERVER['DOCUMENT_ROOT']."/".$homedir;
	   }
	else $login=false;
    }
	
}

	
if (!$login) {
		session_unset();
		session_destroy();
		$_SESSION['loggedin'] = 0;
		$_SESSION['homedir'] = "";
		$_SESSION['rdonly'] = "";
		if($action) $msg= "Authentication failed!";
} else {
		$_SESSION['loggedin'] = 1;
		$_SESSION['homedir'] = $homedir;
		$_SESSION['rdonly'] = $rdonly;
		header("Location:index.php"); 
		exit;
}
?><html>
<head>
<title>PHP Navigator- Login</title>
<link href="inc/windows.css" rel="stylesheet" type="text/css">
<link href="inc/skin.css" rel="stylesheet" type="text/css">
<style>
td{vertical-align:middle;}
</style>
</head>
</body>
<table cellspacing=0 cellpading=0 border=0 width=100% height=100% align=center valign=middle>
  <tr>
    <td width=100% height=100% align=center valign=middle><table height=100 width=300><tr>
      <td width=100% height=100% align=center valign=middle><form  method="post" action="">
<table width="300"  border="0" class="window">
  <tr>
    <td><table width="300"  border="0" align="center" cellpadding="0" cellspacing="0" class="lefthead">
      <tr>
        <td class="head" valign=middle><center class="title"><img src=images/logoff.gif align=left><strong>PHP Navigator - Login</strong></center></td>
      </tr>
    </table>
    <table width="300"  border="0" align="center" cellpadding="6" cellspacing="0">
      <tr>
        <td align="right" >User</td>
        <td align="left" ><input name="user" type="text" value="<?php print $user_i ?>" size="20" id=user></td>
      </tr>
      <tr>
       <td align="right">Password</td>
       <td align="left" ><input name="passwd" type="password" size="20"></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td align="left" ><input name="action" type="submit" id="action" value="Login">&nbsp;<?php print "<font color=red>$msg</font>"; ?></td>
      </tr></table></td></table></form></td>
    </tr></table></td>
  </tr>
</table>
<script>
document.forms[0].user.focus();
</script>
</body>
</html>
<?php if($patch_output) print $output_patch; ?>
