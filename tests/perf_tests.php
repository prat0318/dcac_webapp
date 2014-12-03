<?
// Usage: php perf_tests.php old/new login/ls/cat/create/shared
$navphp_old = "dcac:5566";
$navphp_new = "dcac:5555";

$arg = $argv[1];
$test = $argv[2];
$url = ($arg == "old" ? $navphp_old : $navphp_new);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$url/navphp/login.php");
curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/32.0.1700.107 Chrome/32.0.1700.107 Safari/537.36');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "user=user4&passwd=user4");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie-name');  //could be empty, but cause problems on some hosts
curl_setopt($ch, CURLOPT_COOKIEFILE, 'tmp0');  //could be empty, but cause problems on some hosts
$answer = curl_exec($ch);
//echo "completed the post call... $answer";
if (curl_error($ch)) {
    echo curl_error($ch);
    }

$sum = 0;
switch($test) {
case "login":
    // LOGIN:
    curl_setopt($ch, CURLOPT_URL, "http://$url/navphp/windows.php");
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "");
    for ($j = 1; $j <= 50; $j++) {
        $time = microtime(true); // time in Microseconds
        for ($i = 1; $i <= 10; $i++) {
            //curl_setopt($ch, CURLOPT_POSTFIELDS, "dir=L2hvbWUvdXNlcjQ=&ajax=1&change=".base64_encode("createm_$i.txt"));
            $answer = curl_exec($ch);
            if (curl_error($ch)) {
                echo curl_error($ch);
                }
        }
    //echo "completed the get call... $answer";
    echo (microtime(true) - $time) . ' elapsed \n'. $arg;
    $sum += microtime(true) - $time;
    }
    break;
case "cat":
    // CAT:
    curl_setopt($ch, CURLOPT_URL, "http://$url/navphp/windows.php?action=Open&dir=L2hvbWUvdXNlcjQ=&file=bmV3X2ZpbGUudHh0");
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "");
    for ($j = 1; $j <= 50; $j++) {
        $time = microtime(true); // time in Microseconds
        for ($i = 1; $i <= 10; $i++) {
            $answer = curl_exec($ch);
            if($j == 1 && $i == 1) echo "completed the get call... $answer";
        }
        echo (microtime(true) - $time) . ' elapsed \r\n'. $arg;
    $sum += microtime(true) - $time;
    }
    break;
case "create":
    // CREATE:
    curl_setopt($ch, CURLOPT_URL, "http://$url/navphp/newfile.php");
    curl_setopt($ch, CURLOPT_POST, true);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, "dir=L2hvbWUvdXNlcjQ=&ajax=1&change=".base64_encode("createn$j_$i.txt"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    for ($j = 1; $j <= 50; $j++) {
        $time = microtime(true); // time in Microseconds
        for ($i = 1; $i <= 10; $i++) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, "dir=L2hvbWUvdXNlcjQ=&ajax=1&change=".base64_encode("create_$j_$i.txt"));
            $answer = curl_exec($ch);
            //if($j == 1 && $i == 1) echo "completed the get call... $answer";
        }
        echo (microtime(true) - $time) . " elapsed \r\n". $arg;
    $sum += microtime(true) - $time;
    }
    break;
case "ls":
    // LS with 100 files present:
    curl_setopt($ch, CURLOPT_URL, "http://$url/navphp/windows.php");
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "");
    for ($j = 1; $j <= 50; $j++) {
        $time = microtime(true); // time in Microseconds
        for ($i = 1; $i <= 10; $i++) {
            $answer = curl_exec($ch);
        }
        echo (microtime(true) - $time) . " elapsed \r\n". $arg;
    $sum += microtime(true) - $time;
    }
    break;
case "shared":
    // OPEN SHARED FILE
    curl_setopt($ch, CURLOPT_URL, "http://$url/navphp/windows.php?action=Open&dir=L2hvbWUvdXNlcjMvbmV3X2ZvbGRlcg==&file=bmV3X2ZpbGUudHh0");
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "");
    for ($j = 1; $j <= 50; $j++) {
        $time = microtime(true); // time in Microseconds
        for ($i = 1; $i <= 10; $i++) {
            $answer = curl_exec($ch);
        }
        echo (microtime(true) - $time) . " elapsed \r\n". $arg;
    $sum += microtime(true) - $time;
    }
    break;
}

echo ("Average: ".($sum/50)."sum: $sum");

?>

