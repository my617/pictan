<?php

 require('function.php');
 debug('===== 退会ページ =====');
 debugLogStart();

 $dbFormData = getUser($_SESSION['user_id']);

 //POST送信された時
 if(!empty($_POST)) {
  debug('POST送信があります');

  //例外処理
  try{
   $dbh = dbConnect();
   $sql = 'UPDATE user SET delete_flg = 1 WHERE user_id = :u_id';
   $data = array(':u_id' => $_SESSION['user_id']);
   $stmt = queryPost($dbh, $sql, $data);

   if($stmt) {
    session_destroy();
    debug('セッション変数の中身：'.print_r($_SESSION, true));
    debug('login.phpに遷移します');
    header('Location:login.php');
   } else {
    debug('クエリ失敗しました');
    $err_msg['common'] = MSG01;
   }
  } catch(Exception $e) {
   error_log('エラー：' . $e->getMessage());
   $err_msg['common'] = MSG01;
  }
 }
 debug('>>>>>画面表示処理終了');

 $siteTitle = '退会ページ | Pictan';
 require('head.php');
 require('header.php');
?>

 <main>

  <section class="withdraw">
   <h2 class="section__title">本当に退会しますか？</h2>
   <p>退会後も再登録可能ですが、データの引き継ぎはできません。</p>
   <form action="" method="post">
    <input type="submit" name="submit" class="btn__mid" value="退会する">
   </form>

  </section>

  <?php
   require('sidebar.php');
  ?>

 </main>

<?php
 require('footer.php');
?>