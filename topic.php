<?php

 require('function.php');

 debug('===== 単語詳細ページ =====');
 debugLogStart();

 //記事ID取得
 $t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
 //var_dump($t_id);
 //DBからユーザー情報を取得
 $dbFormData = getUser($_SESSION['user_id']);
 //var_dump($dbFormData);
 //記事詳細取得
 $dbTopicData = getTopicData($t_id);
 //var_dump($dbTopicData);
 debug('記事内容：'.print_r($dbTopicData, true));

 if(isset($_POST['topic_del'])) {
  debug('削除します');

  //例外処理
  try{
   $dbh = dbConnect();
   $sql = 'UPDATE topic SET delete_flg = 1 WHERE topic_id = :t_id AND user_id = :u_id';
   $data = array(':t_id' => $t_id, ':u_id' => $_SESSION['user_id']);
   $stmt = queryPost($dbh, $sql, $data);

   if($stmt) {
    $_SESSION['msg_success'] = SUC05;
    header("Location:mypage.php");
   }
  } catch(Exception $e) {
   error_log('エラー発生：' . $e->getMessage());
   $err_msg['common'] = MSG01;
  }
 }


 $siteTitle = '';
 require('head.php');
 require('header.php');
?>

 <main>

  <section class="topic">

   <div class="topic__outer">
     <div class="topic__heading">
      <h3 class="topic__word"><?php echo sanitize($dbTopicData['word']); ?></h3><span class="topic__label"><?php echo sanitize($dbTopicData['parts_name']); ?></span>
     </div>

    <div class="topic__inner">
     <div class="topic__img-outer">
      <img src="<?php echo sanitize($dbTopicData['pic']); ?>" alt="<?php echo sanitize($dbTopicData['mean']); ?>" class="topic__img">
     </div>

     <div class="topic__content">
      <p class="topic__mean">意味： <?php echo sanitize($dbTopicData['mean']); ?></p>
      <div class="topic__exam">
       <p class="topic__txt">例文： <?php echo sanitize($dbTopicData['text']); ?></p>
       <p class="topic__trans">訳： <?php echo sanitize($dbTopicData['trans']); ?></p>
      </div>
     </div>

    </div>

    <nav class="topic__memu">
      <ul class="topic__items">
       <li class="topic__item">言語： <?php echo sanitize($dbTopicData['cat_name']); ?></li>
       <li class="topic__item"><button class="btn__sm"><a href=<?php echo "newPost.php?t_id=" . $t_id; ?> class="btn__edit">編集</a></button></li>
       <form action="" method="post">
        <li class="topic__item"><input type="submit" value="削除" name="topic_del" class="btn__sm"></li>
       </form>
      </ul>
    </nav>
   
   </div>


  </section>

 <?php
  require('sidebar.php');
 ?>

 </main>

<?php
 require('footer.php');
?>