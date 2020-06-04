<?php

 require('function.php');

 debug('===== パスワード再発行用認証キー作成 =====');
 debugLogStart();

 //POST送信された時
 if(!empty($_POST)) {
  debug('POST送信あります');
  debug('POST情報：'.print_r($_POST, true));

  $email = $_POST['email'];

  //バリデーション
  validRequired($email, 'email');

  if(empty($err_msg)) {
   debug('未入力チェックOKです');

   validEmail($email, 'email');
   validMaxLen($email, 'email', 255);

   if(empty($err_msg)) {
    debug('バリデーションOKです');

    try{
     $dbh = dbConnect();
     $sql = 'SELECT count(*) FROM user WHERE email = :email';
     $data = array(':email' => $email);
     $stmt = queryPost($dbh, $sql, $data);
     $result = $stmt->fetch(PDO::FETCH_ASSOC);

     //登録済みのメールアドレスの場合
     if($stmt && array_shift($result)) {
      debug('クエリ成功。DB登録あり');
      $_SESSION['msg_success'] = SUC07;

      $auth_key = randomKey();

      //メール送信
      $from = 'inujmai@gmail.com';
      $to = $email;
      $subject = 'パスワード再発行認証  【 Pictan 】';
      $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。
                        
パスワード再発行認証キー入力ページ：http://localhost:8888/pictan/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります
                        
認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/pictan/passRemindSend.php
                    
////////////////////////////////////////
Pictan
URL  http://***
E-mail inujmai@gmail.com
////////////////////////////////////////
EOT;
       sendMail($from, $to, $subject, $comment);

       //認証に必要な情報をセッションへ保存
       $_SESSION['auth_key'] = $auth_key;
       $_SESSION['auth_email'] = $email;
       $_SESSION['auth_key_limit'] = time()+(60*30);
       debug('セッション変数の中身：'.print_r($_SESSION,true));

       header("Location:passRemindRecieve.php");

     } else {
      debug('クエリに失敗したかDBに登録のないEmailが入力されました');
      $err_msg['common'] = MSG01;
     }
    } catch(Exception $e) {
     error_log('エラー：' . $e->getMessage());
     $err_msg['common'] = MSG01;
    }
   }
  }
 }
 $siteTitle = '認証キー発行ページ';
 require('head.php');
?>

<section class="pass-remind">
 <h1 class="title">Pictan</h1>
 <h2 class="subtitle">パスワード再発行</h2>

 <p class="pass-remind__text">
  ご指定のメールアドレス宛にパスワード再発行用のURLと<br>
  認証キーをお送り致します。
 </p>

 <form action="" method="post" class="pass-remind__outer">
  <div class="err-msg">
   <?php
    if(!empty($err_msg['common'])) echo $err_msg['common'];
   ?>
  </div>

  <div class="pass-remind__inner">
  <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
   <p class="pass-remind__label">ご登録のメールアドレスを入力して下さい。<span class="err-msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></span></p>
   <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>" class="pass-remind__form">
  </label>
  <button type="submit" class="btn__mid">送信</button>
  
  </div>

 </form>

 <a href="login.php" class="pass-remind__link">&lt; ログインページに戻る</a>

</section>

<?php
 require('footer.php');
?>