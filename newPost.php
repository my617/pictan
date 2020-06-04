<?php

 require('function.php');
 debug('===== 新規単語登録 =====');
 debugLogStart();

 // GETデータを格納
 $t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
 // DBから商品データを取得
 $dbFormData = (!empty($t_id)) ? getTopic($_SESSION['user_id'], $t_id) : '';
 // 新規登録画面か編集画面か判別用フラグ
 $edit_flg = (empty($dbFormData)) ? false : true;
 // DBからカテゴリデータを取得
 $dbCategoryData = getCategory($_SESSION['user_id']);
 debug('単語ID：'.$t_id);
 debug('フォーム用DBデータ：'.print_r($dbFormData, true));
 debug('カテゴリデータ：'.print_r($dbCategoryData, true));
 //DBデータから品詞データを取得
 $dbPartsData = getParts();
 debug('単語ID：'.$t_id);
 debug('フォーム用DBデータ：'.print_r($dbFormData, true));
 debug('品詞データ：'.print_r($dbPartsData, true));

 // GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
 //if(!empty($t_id) && empty($dbFormData)) {
  //debug('GETパラメータの商品IDが違います。トップページへ遷移します。');
  //header('Location:index.php');
 //}

 // POST送信時処理
 if(!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST, true));
  debug('FILE情報：'.print_r($_FILES, true));

  //変数にユーザー情報を代入
  //意味１
  $word = $_POST['word'];
  $mean = $_POST['mean'];
  $category = $_POST['cat_id'];
  $parts = $_POST['parts_id'];
  $text = $_POST['text'];
  $trans = $_POST['trans'];
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : ''; 
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

  // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  if(empty($dbFormData)) {
   validRequired($word, 'word');
   validRequired($mean, 'mean');
   validSelect($category, 'cat_id');
   validSelect($parts, 'parts_id');
   validRequired($text, 'text');
   validRequired($trans, 'trans');
  } else {
   if($dbFormData['word'] !== $word) {
    validRequired($word, 'word');
   }
   if($dbFormData['mean'] !== $mean) {
    validRequired($mean, 'mean');
   }
   if($dbFormData['cat_id'] !== $category) {
    validSelect($category, 'cat_id');
   }
   if($dbFormData['parts_id'] !== $parts) {
    validSelect($parts, 'parts_id');
   }
   if($dbFormData['text'] !== $text) {
    validRequired($text, 'text');
   }
   if($dbFormData['trans'] !== $trans) {
    validRequired($teans, 'trans');
   }
  }

  if(empty($err_msg)) {
   debug('バリデーションOKです。');

   //例外処理
   try{
    $dbh = dbConnect();
    if($edit_flg){
     $sql = 'UPDATE topic SET word = :word, mean = :mean, cat_id = :category, parts_id = :parts, text = :text, trans = :trans, pic = :pic WHERE user_id = :u_id AND topic_id = :t_id';
     $data = array(':word' => $word, ':mean' => $mean, ':category' => $category, ':parts' => $parts, ':text' => $text, ':trans' => $trans, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':t_id' => $t_id);
    } else {
     $sql = 'INSERT INTO topic (word, mean, cat_id, parts_id, text, trans, pic, user_id, created_date) VALUES (:word, :mean, :category, :parts, :text, :trans, :pic,:u_id, :date)';
     $data = array(':word' => $word, ':mean' => $mean, ':category' => $category, ':parts' => $parts, ':text' => $text, ':trans' => $trans, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
    }
   debug('SQL:'.$sql);
   debug('流し込みデータ：'.print_r($data, true));
   // クエリ実行
   $stmt = queryPost($dbh, $sql, $data);

   // クエリ成功の場合
   if($stmt) {
    $_SESSION['msg_success'] = SUC01;
    debug('トップページへ遷移します。');
    header("Location:mypage.php");
   }

  } catch (Exception $e) {
   error('エラー発生:' . $e->getMessage());
   $err_msg['common'] = MSG01;
  }
 }
}
debug('>>>>>画面表示処理終了');

 $siteTitle = (!$edit_flg) ? '新規単語登録' : '単語編集';
 require('head.php');
 require('header.php');
?>

 <main>

  <section class="new-post">

   <form action="" method="post" enctype="multipart/form-data" class="new-post__container">

   <h2 class="section__title"><?php echo (!$edit_flg) ? '新規単語登録' : '単語編集'; ?></h2>

   <?php
    if(!empty($err_msg['common'])) echo $err_msg['common'];
   ?>

  <!-- 意味1 -->
  <div class="new-post__outer">

    <div class="new-post__inner">
     <label class="<?php if(!empty($err_msg['word'])) echo 'err'; ?>">
      <p class="new-post__label">単語<span class="err-msg"><?php if(!empty($err_msg['word'])) echo $err_msg['word']; ?></span></p>
      <input type="text" name="word" value="<?php echo getFormData('word'); ?>" class="new-post__form">
     </label>
    </div>

    <div class="new-post__inner">
     <label class="<?php if(!empty($err_msg['mean'])) echo 'err'; ?>">
      <p class="new-post__label">意味<span class="err-msg"><?php if(!empty($err_msg['mean'])) echo $err_msg['mean']; ?></span></p>
      <input type="text" name="mean" value="<?php echo getFormData('mean'); ?>" class="new-post__form">
     </label>
    </div>

    <div class="new-post__inner">
     <label class="<?php if(!empty($err_msg['cat_id'])) echo 'err'; ?>">
      <p class="err-msg"><?php if(!empty($err_msg['cat_id'])) echo $err_msg['cat_id']; ?></p>
      <select name="cat_id" class="new-post__form">
       <option value="0" <?php if(getFormData('cat_id') == 0){ echo 'selected'; } ?>>言語を選択して下さい</option>
       <?php
        foreach($dbCategoryData as $key => $val){
       ?>
       <option value="<?php echo $val['cat_id']; ?>" <?php if(getFormData('cat_id') == $val['cat_id'] ){ echo 'selected'; } ?>>
         <?php echo $val['cat_name']; ?>
       </option>
       <?php
        }
       ?>
      </select>
     </label>
    </div>

    <div class="new-post__inner">
     <label class="<?php if(!empty($err_msg['parts_id'])) echo 'err'; ?>">
      <p class="err-msg"><?php if(!empty($err_msg['parts_id'])) echo $err_msg['parts_id']; ?></p>
      <select name="parts_id" class="new-post__form">
       <option value="0" <?php if(getFormData('parts_id') == 0){ echo 'selected'; } ?>>品詞または熟語を選択して下さい</option>
       <?php
        foreach($dbPartsData as $key => $val) {
       ?>
       <option value="<?php echo $val['parts_id']; ?>" <?php if(getFormData('parts_id') == $val['parts_id']){ echo 'selected'; } ?>>
         <?php echo $val['parts_name'] ?>
       </option>
       <?php
        }
       ?>
      </select>
     </label>
    </div>

    <div class="new-post__inner">
     <label class="<?php if(!empty($err_msg['text'])) echo 'err'; ?>">
      <p class="new-post__label">例文<span class="err-msg"><?php if(!empty($err_msg['text'])) echo $err_msg['text']; ?></span></p>
      <input type="text" name="text" value="<?php echo getFormData('text'); ?>" class="new-post__form">
     </label>
    </div>

    <div class="new-post__inner">
     <label class="<?php if(!empty($err_msg['trans'])) echo 'err'; ?>">
      <p class="new-post__label">訳文<span class="err-msg"><?php if(!empty($err_msg['trans'])) echo $err_msg['trans']; ?></span></p>
      <input type="text" name="trans" value="<?php echo getFormData('trans'); ?>" class="new-post__form">
     </label>
    </div>

    <div class="new-post__inner new-post__drop" id="js-img-drop">
     <label class="new-post__area <?php if(!empty($err_msg['pic'])) echo 'err'; ?>">
      <p class="err-msg"><?php if(!empty($err_msg['pic'])) echo $err_msg['pic']; ?></p>
      <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
      <input type="file" name="pic" class="new-post__input" id="js-input-file">
      <img src="<?php echo getFormData('pic'); ?>" class="new-post__prev" id="js-prev-img">
      <p id="js-input-text" class="new-post__input-text" <?php if(!empty(getFormData('pic'))) { echo 'style="display:none"'; } ?>>ファイルを選択する</p>
     </label>
    </div>

    <input type="submit" value="登録する" class="btn__mid">

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