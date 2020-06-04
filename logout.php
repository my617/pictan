<?php

 require('function.php');
 debug('===== ログアウト =====');
 debugLogStart();

 debug('ログアウトします');
 // セッションを削除（ログアウトする）
 session_destroy();
 debug('ログインページへ遷移します');
 header("Location:login.php");
?>