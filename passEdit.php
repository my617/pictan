<?php

 ini_set('display_errors', "On");

 require('function.php');
 debug('===== アカウント編集 =====');
 debugLogStart();
 
 
  // DBからユーザーデータを取得
  $dbFormData = getUser($_SESSION['user_id']);
  debug('取得したユーザー情報'.print_r($dbFormData, true));

  //post送信されていた場合
  if(!empty($_POST)) {
    debug('POST送信があります');
    debug('POST情報：'.print_r($_POST, true));

    //変数にユーザー情報を代入
    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];

    //DBの情報と入力情報が異なる場合バリデーションを行う
    //パスワードチェック
    validRequired($pass_old, 'pass_old');
    validRequired($pass_new, 'pass_new');
    validRequired($pass_new_re, 'pass_new_re');

    validPass($pass_old, 'pass_old');
    validPass($pass_new, 'pass_new');

    //古いパスワードとDBパスワードを照合（DBに入っているデータと同じであれば、半角英数字チェックや最大文字チェックは行わなくても問題ない）
    if(!password_verify($pass_old, $dbFormData['pass'])) {
      $err_msg['pass_old'] = MSG10;
    }
    //新しいパスワードと古いパスワードが同じかチェック
    if($pass_old === $pass_new) {
      $err_msg['pass_new'] = MSG09;
    }
    //パスワードとパスワード再入力が合っているかチェック（ログイン画面では最大、最小チェックもしていたがパスワードの方でチェックしているので実は必要ない）
    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    if(empty($err_msg)){
    debug('バリデーションOK。');

    //例外処理
    try{
      $dbh = dbConnect();
      $sql = 'UPDATE user SET pass = :pass WHERE user_id = :u_id';
      $data = array(':pass' => password_hash($pass_new, PASSWORD_DEFAULT), ':u_id' => $dbFormData['user_id']);
      $stmt = queryPost($dbh, $sql, $data);
      var_dump($stmt);

      if($stmt) {
        $_SESSION['msg_suc'] = SUC02;
        debug('パスワード編集ページに戻ります。');
        header("Location:passEdit.php");
      }
    } catch(Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG01;
    }
  }
}
debug('>>>>>画面表示処理終了');

 $siteTitle = 'ぴくたん | パスワード編集';
 require('head.php');
 require('header.php');
?>

 <main>

  <section class="pass-edit">

  <div class="pass-edit__container">

   <h2 class="section__title">パスワード編集</h2>

   <form action="" method="post" class="pass-edit__outer">

   <div class="err-msg">
    <?php
      if(!empty($err_msg['common'])) echo $err_msg['common'];
    ?>
   </div>
    
    <div class="pass-edit__inner">
     <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
      <p class="pass-edit__label">現在のパスワード<span class="err-msg"><?php if(!empty($err_msg['pass_old'])) echo $err_msg['pass_old']; ?></span></p>
      <input type="password" name="pass_old" class="pass-edit__form" value="<?php if(!empty($_POST['pass_old'])) echo $_POST['pass_old']; ?>">
     </label>
    </div>

    <div class="pass-edit__inner">
     <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
      <p class="pass-edit__label">変更後のパスワード<span class="err-msg"><?php if(!empty($err_msg['pass_new'])) echo $err_msg['pass_new']; ?></span></p>
      <input type="password" name="pass_new" class="pass-edit__form" value="<?php if(!empty($_POST['pass_new'])) echo $_POST['pass_new']; ?>">
     </label>
    </div>

    <div class="pass-edit__inner">
     <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
      <p class="pass-edit__label">パスワード再入力<span class="err-msg"><?php if(!empty($err_msg['pass_new_re'])) echo $err_msg['pass_new_re']; ?></span></p>
      <input type="password" name="pass_new_re" class="pass-edit__form" value="<?php if(!empty($_POST['pass_new_re'])) echo $_POST['pass_new_re']; ?>">
     </label>
    </div>

    <input type="submit" class="btn__mid" value="変更する">

   </form>

   </div>

  </section>

  <?php
   require('sidebar.php');
  ?>

 </main>

<?php
 require('footer.php');
?>