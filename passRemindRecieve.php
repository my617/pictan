<?php

 require('function.php');
 debug('===== パスワード再発行用認証キー入力ページ =====');
 debugLogStart();

 if(!empty($_POST)) {
  debug('POST送信あります');
  debug('POST情報：'.print_r($_POST, true));

  //変数に認証キーを代入
  $auth_key = $_POST['token'];

  //バリデーション
  if(empty($err_msg)) {
   debug('未入力チェックOK');

   validLength($auth_key, 'token');
   validHalf($auth_key, 'token');

   if(empty($err_msg)) {
    debug('バリデーションOK');

    if($auth_key !== $_SESSION['auth_key']) {
     $err_msg['common'] = MSG16;
    }
    if(time() > $_SESSION['auth_key_limit']) {
     $err_msg['common'] = MSG16;
    }
    if($stmt) {
     debug('クエリ成功');

     //メール送信
     $form = 'inujmai@gmail.com';
     $to = $_SESSION['auth_email'];
     $subject = 'パスワード再発行  【 Pictan 】';
     $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。
                
ログインページ：http://localhost:8888/pictan/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します
                
////////////////////////////////////////
Pictan
URL  http://***
E-mail inujmai@gmail.com
////////////////////////////////////////
EOT;
     sendMail($from, $to, $subject, $comment);

     //セッション削除
     $_SESSION['msg_success'] = SUC07;

     return;
    }
   }
  }
 }

 $siteTitle = 'パスワード再発行用認証キー入力ページ';
 require('head.php');
?>

<section class="pass-remind">
 <h1 class="title">Pictan</h1>
 <h2 class="subtitle">パスワード再発行</h2>

 <p class="pass-remind__text">
  ご指定のメールアドレスお送りした<br>
  【パスワード再発行認証】メール内にある<br>
  「認証キー」をご入力ください。
 </p>

 <form action="" method="post" class="pass-remind__outer">
  <div class="err-msg"></div>

  <div class="pass-remind__inner">
  <label class="<?php if(!empty($err_msg['authkey'])) echo 'err'; ?>">
   <p class="pass-remind__label">認証キーを入力<span class="err-msg"><?php if(!empty($err_msg['auth_key'])) echo $err_msg['auth_key']; ?></span></p>
   <input type="text" name="token" value="<?php if(!empty($_POST['authkey'])) echo $_POST['authkey']; ?>" class="pass-remind__form">
   <button type="submit" class="btn__mid">送信</button>
  </label>
  </div>

 </form>

 <a href="passRemindSend.php" class="pass-remind__link">&lt; パスワード再発行メールを再送信する</a>

</section>

<?php
 require('footer.php');
?>