<?php

 require('function.php');
 debug('===== ログイン =====');
 debugLogStart();

 //POST送信されているとき
 if(!empty($_POST)) {
  debug('POST送信が有ります。');

  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;

  //emailの形式チェック
  validEmail($email, 'email');
  //emailの最大文字数チェック
  validMaxLen($email, 'email', 255);

  //パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxLen($pass, 'pass', 255);
  //パスワードの最小文字数チェック
  validMinLen($pass, 'pass', 6);

  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

  if(empty($err_msg)) {
   debug('バリデーションOKです。');

  //例外処理
  try{
   $dbh = dbConnect();
   $sql = 'SELECT pass, user_id FROM user WHERE email = :email AND delete_flg = 0';
   $data = array(':email' => $email);
   // クエリ実行
   $stmt = queryPost($dbh, $sql, $data);
   // クエリ結果の値を取得
   $result = $stmt->fetch(PDO::FETCH_ASSOC);

   debug('クエリ結果の中身：'.print_r($result,true));
   //var_dump($result);

   // パスワード照合
   if(!empty($result) && password_verify($pass, array_shift($result))) {
    debug('パスワードがマッチしました。');
    //ログイン有効期限（デフォルトを１時間とする）
    $sesLimit = 60*60;
    // 最終ログイン日時を現在日時に
    $_SESSION['login_date'] = time();

    //ログイン保持にチェックがある場合
    if(!empty($_POST['pass_save'])) {
     debug('ログイン保持にチェックがあります。');
     // ログイン有効期限を30日にしてセット
     $_SESSION['login_limit'] = $sesLimit * 24 * 30;
    } else {
     debug('ログイン保持にチェックはありません。');
     // 次回からログイン保持しないので、ログイン有効期限を1時間後にセット
     $_SESSION['login_limit'] = $sesLimit;
    }
    // ユーザーIDを格納
    $_SESSION['user_id'] = $result['user_id'];

    debug('セッション変数の中身：'.print_r($_SESSION, true));
    debug('マイページに遷移します。');
    header("Location:mypage.php");
   } else {
    debug('パスワードがアンマッチです');
    $err_msg['common'] = MSG07;
   }

  } catch(Exception $e) {
   error_log('エラー発生:' . $e->getMessage());
   $err_msg['common'] = MSG01;
  }
 }
} 
debug('>>>>>画面表示処理終了');

$siteTitle = '新規登録';
require('head.php');
require('header.php');
?>

<section class="login">
 <h2 class="subtitle">ログイン</h2>

 <form action="" method="post" class="login__outer">
  <div class="err-msg"><?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?></div>

  <div class="login__inner">
  <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
   <p class="login__label">メールアドレス<span class="err-msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></span></p>
   <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>" class="login__form">
  </label>
  </div>

  <div class="login__inner">
  <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
   <p class="login__label">パスワード<span class="err-msg"><?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?></span></p>
   <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>" class="login__form">
  </label>
  </div>

  <div class="login__check">
   <input type="checkbox" name="pass_save" class="login__box"><p>次回から自動ログインする</p>
  </div>

  <button type="submit" class="btn__mid">ログイン</button>

 </form>

 <div class="btn__outer">
   <button type="submit" class="btn__lg"><a href="signup.php" class="login__link">新規登録はこちら</a></button>
   <button type="submit" class="btn__lg"><a href="passRemindSend.php" class="login__link">パスワードを忘れた方はこちら</a></button>
  </div>

</section>

<?php
 require('footer.php');
?>