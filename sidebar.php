<?php
 ini_set('display_errors', "On");

 //DBからユーザー情報を取得
 $u_id = $_SESSION['user_id'];
 //DBからユーザー情報を取得
 $dbUserData = getUser($_SESSION['user_id']);
 //カテゴリーのGETパラメータ取得
 $c_id = (!empty($_GET['cat_id'])) ? $_GET['cat_id'] : '';
 //カテゴリー取得
 $dbCategoryData = getCategory($_SESSION['user_id'], $c_id);
 //var_dump($dbFormData);
 debug('カテゴリの中身:'.print_r($dbCategoryData, true));

if (!empty($_POST['cat_sub'])) {

  // 変数にユーザー情報を代入
  $c_name = $_POST['c_name'];

  // 未入力チェック
  validRequired($c_name, 'c_name');

  if (empty($err_msg['c_name'])) {

      // 最大文字数チェック
      validMaxLen($c_name, 'c_name', 13);

      if (empty($err_msg['c_name'])) {

          // 例外処理
          try {
              // DBへ接続
              $dbh = dbConnect();
              // SQL文作成
              $sql = 'INSERT INTO category (cat_name,user_id,created_date) VALUES (:c_name, :u_id,:date)';
              $data = array(':c_name' => $c_name, ':u_id' => $u_id, ':date' => date('Y-m-d H:i:s'));

              // クエリ実行
              $stmt = queryPost($dbh, $sql, $data);

              // クエリ成功の場合
              if ($stmt) {
                  $_SESSION['cat_success'] = SUC05;
                  header("Location:mypage.php");
              }
          } catch (Exception $e) {
              error_log('エラー発生：' . $e->getMessage());
              $err_msg['common'] = MSG01;
          }
      }
  }
}
debug('>>>>>画面表示処理終了');
?>

<section class="aside">

   <p class="aside__hello">こんにちは、<?php echo $dbUserData['name']; ?>さん</p>

   <nav class="aside__menu">
     
     <p class="aside__subtitle">カテゴリー一覧</p>
     <form action="" method="post" class="cat__inner">

    <div class="aside__cat-list">
    <?php
     if(!empty($err_msg['common'])) echo $err_msg['common'];
    ?>
     <ul class="aside__cat-item">
     <?php
      foreach($dbCategoryData as $key => $val){
     ?>
      <li class="aside__cat-items" id="del_<?php echo $val['cat_id']; ?>" data-id="<?php echo $val['cat_id']; ?>">
       <?php echo $val['cat_name']; ?>
      </li>
      <?php
       }
      ?>
     </ul>
    </div>

    <div class="aside__cat-submit">
     <div class="err-msg">
      <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
     </div>
     <label class="<?php if(!empty($err_msg['cat_name'])) echo 'err'; ?>">
      <p class="err-msg"><?php if(!empty($err_msg['cat_name'])) echo $err_msg['cat_name']; ?></p>
      <input type="text" name="c_name" value="<?php if(!empty($_POST['cat_name'])) echo $_POST['cat_name']; ?>" class="aside__form" placeholder="カテゴリーを登録">
      <input type="submit" name="cat_sub" class="btn__sm" value="登録">
     </label>
    </div>

    <ul class="aside__items">
        <li class="aside__item"><a href="newPost.php" class="aside__link">新規単語登録</a></li>
        <li class="aside__item"><a href="accountEdit.php" class="aside__link">アカウント編集</a></li>
        <li class="aside__item"><a href="passEdit.php" class="aside__link">パスワード編集</a></li>
        <li class="aside__item"><a href="logout.php" class="aside__link">ログアウト</a></li>
    </ul>
    

    </form>
    

   </nav>

</section>