<?php

 require('function.php');

 debug(' ===== 新規登録 ===== ');
 debugLogStart();

 //POST送信されているとき
 if(!empty($_POST)) {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  //バリデーション
  //未入力チェック
  validRequired($name, 'name');
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');

  //ユーザーネーム文字数チェック
  validMaxLen($name, 'name', 50);

  //Emailチェック
  validEmail($email, 'email');
  validMaxLen($email, 'email', 255);
  validEmailDup($email, 'email');

  //パスワードチェック
  validPass($pass, 'pass');
  //パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxLen($pass, 'pass', 50);
  //パスワードの最小文字数チェック
  validMinLen($pass, 'pass', 6);
  //パスワード（再入力）の最大文字数チェック
  validMaxLen($pass_re, 'pass_re', 50);
  //パスワード（再入力）の最小文字数チェック
  validMinLen($pass_re, 'pass_re', 6);
  //同値チェック
  validMatch($pass, $pass_re, 'pass_re');

    if(empty($err_msg)) {
     debug('バリデーションOKです');

     try{
      $dbh = dbConnect();
      $sql = 'INSERT INTO user (name, email, pass, created_date, login_time) VALUES (:name, :email, :pass, :created_date, :login_time)';
      $data = array(':name' => $name, ':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':created_date' => date('Y-m-d H:i:s'), ':login_time' => date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh, $sql, $data);

      if($stmt) {
       debug('データ挿入成功');
       //ログイン有効期限を1時間に
       $sesLimit = 60*60;
       //セッションに内容を詰める
       $_SESSION['login_date'] = time();
       $_SESSION['login_limit'] = $sesLimit;
       //ユーザーID格納
       $_SESSION['user_id'] = $dbh->lastInsertId();

       debug('セッション変数の中身: '.print_r($_SESSION, true));

       header("Location:index.php");
      }
     } catch (Exception $e) {
      error_log('エラー:'.$e->getMessage());
      $err_msg['common'] = MSG01;
     }
  }
 }

debug('>>>>>画面表示処理終了');

 $siteTitle = '新規登録';
 require('head.php');
 require('header.php');
?>

<section class="signup">

<h2 class="subtitle">新規登録</h2>

 <form action="" method="post" class="signup__outer">
  <div class="err-msg">
   <?php 
    if(!empty($err_msg['common'])) {
     echo $err_msg['common'];
    }
   ?>
  </div>

  <div class="signup__inner">
  <label class="<?php if(!empty($err_msg['name'])) { echo 'err'; } ?>">
   <p class="signup__label">お名前 <span class="err-msg"><?php echo getErrMsg('name'); ?></span></p>
   <input type="text" name="name" value="<?php if(!empty($_POST['name'])) { echo $_POST['name']; } ?>" class="signup__form">
  </label>
  </div>

  <div class="signup__inner">
  <label class="<?php if(!empty($err_msg['email'])) { echo 'err'; } ?>">
   <p class="signup__label">メールアドレス <span class="err-msg"><?php echo getErrMsg('email'); ?></span></p>
   <input type="text" name="email" value="<?php if(!empty($_POST['email'])) { echo $_POST['email']; } ?>" class="signup__form">
  </label>
  </div>

  <div class="signup__inner">
  <label class="<?php if(!empty($err_msg['pass'])) { echo 'err'; } ?>">
   <p class="signup__label">パスワード（6文字以上）<span class="err-msg"><?php echo getErrMsg('pass'); ?></span></p>
   <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) { echo $_POST['pass']; }  ?>" class="signup__form">
  </label>
  </div>

  <div class="signup__inner">
  <label class="<?php if(!empty($err_msg)) { echo 'err'; } ?>">
   <p class="signup__label">パスワード再入力 <span class="err-msg"><?php echo getErrMsg('pass_re'); ?></span></p>
   <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) { echo $_POST['pass_re']; } ?>" class="signup__form">
  </label>
  </div>

  <button type="submit" class="btn__mid">新規登録</button>
  
 </form>

 <div class="btn__outer">
  <button type="button" class="btn__lg"><a href="login.php" class="signup__link">ログインはこちら</a></button>
  <button type="submit" class="btn__lg"><a href="passRemindSend.php" class="signup__link">パスワードを忘れた方はこちら</a></button>
 </div>

</section>

<?php
 require('footer.php');
?>
