<?php

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
    $name = $_POST['name'];
    $email = $_POST['email'];

    //DBの情報と入力情報が異なる場合バリデーションを行う
    //名前チェック
    if($dbFormData['name'] !== $name) {
      validMaxLen($name, 'name', 50);
    }
    //Emailチェック
    if($dbFormData['email'] !== $email) {
      validMaxLen($email, 'email', 255);
      if(empty($err_msg['email'])) {
        validEmailDup($email);
      }
      validEmail($email, 'email');
    }

    if(empty($err_msg)){

    //例外処理
    try{
      $dbh = dbConnect();
      $sql = 'UPDATE user SET name = :name, email = :email WHERE user_id = :u_id';
      $data = array(':name' => $name, ':email' => $email, ':u_id' => $dbFormData['user_id']);
      $stmt = queryPost($dbh, $sql, $data);
      var_dump($stmt);

      if($stmt) {
        $_SESSION['msg_success'] = SUC02;
        debug('アカウント編集ページに戻ります。');
        header("Location:accountEdit.php");
      }
    } catch(Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG01;
    }
  }
}
debug('>>>>>画面表示処理終了');

 $siteTitle = 'ぴくたん | アカウント編集';
 require('head.php');
 require('header.php');
?>

 <main>

  <section class="account-edit">

  <div class="account-edit__container">

   <h2 class="section__title">アカウント編集ページ</h2>

   <form action="" method="post" class="account-edit__outer">

   <div class="err-msg">
    <?php
      if(!empty($err_msg['common'])) echo $err_msg['common'];
    ?>
   </div>
    
    <div class="account-edit__inner">
     <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
      <p class="account-edit__label">名前<span class="err-msg"><?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?></span></p>
      <input type="text" name="name" class="account-edit__form" value="<?php echo getFormData('name') ?>">
     </label>
    </div>

    <div class="account-edit__inner">
     <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
      <p class="account-edit__label">メールアドレス<span class="err-msg"><?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?></span></p>
      <input type="text" name="email" class="account-edit__form" value="<?php echo getFormData('email'); ?>">
     </label>
    </div>

    <input type="submit" class="btn__mid" value="変更する">

   </form>

   <a href="withdraw.php" class="account-edit__link">退会はこちら</a>
  
  </div>

  </section>

  <?php
   require('sidebar.php');
  ?>

 </main>

<?php
 require('footer.php');
?>