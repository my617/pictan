<?php

 ini_set('display_errors', "On");
 require('function.php');

 debug('===== マイページ =====');
 debugLogStart();

 $dbFormData = getUser($_SESSION['user_id']);

 $currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
 if(!is_int((int)$currentPageNum)) {
  error_log('エラー発生: ページ指定に不正な値が入力されました');
  header("Location:mypage.php");
 }

 //表示件数
 $listSpan = 1;
 //このページで最初に表示する記事は何番目か
 $currentMinNum = (($currentPageNum - 1) * $listSpan);
 $sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '10';
 $dbCategoryData = getCategory($_SESSION['user_id']);
 $dbPartsData = getParts();
 //カテゴリ、デフォルトはなし
 $topicCategory = (!empty($_GET['cat_id'])) ? $_GET['cat_id'] : '';
 //品詞、デフォルトはなし
 $topicParts = (!empty($_GET['parts_id'])) ? $_GET['parts_id'] : '';
 //総記事数と総ページ数を取得
 $dbTopicCount = getTopicCount($dbFormData['user_id'], $topicCategory, $topicParts);
 //var_dump($dbCategoryData);
 $dbTopicList = getTopicList($dbFormData['user_id'], $currentMinNum, $topicCategory, $topicParts, $sort);
 debug('記事数：'.print_r($dbTopicList, true));

 debug('>>>>>画面表示処理終了');

 $siteTitle = 'Pictan';
 require('head.php');
 require('header.php');
?>

 <main>

  <section class="mypage">
   <h2 class="section__title">単語一覧</h2>

   <div class="mypage__search">

    <div class="mypage__search-inner">

    <form action="" method="get" id="js-submit-form">
     <select name="cat_id" class="mypage__select" id="js-submit-select">
      <option value="0">言語カテゴリーで探す</option>
      <?php
       foreach($dbCategoryData as $key => $val) {
      ?>
      <option value="<?php echo $val['cat_id']; ?>"
        <?php if(!empty($_GET['cat_id']) && $_GET['cat_id'] == $val['cat_id']){
          echo 'selected';
        } ?>>
        <?php echo $val['cat_name']; ?>
      </option>
     <?php 
      }
     ?>
     </select>
    </form>

    <form action="#" method="get">
     <input type="text" name="search" placeholder="単語検索" class="mypage__submit">
     <input type="submit" class="btn__sm" value="検索">
    </form>

    </div>

   </div>

   <div class="mypage__inner">

    <!-- <div class="mypage__total">
     <span class="mypage__num"><?php echo $currentMinNum+1; ?></span> / 
     <span class="mypage__num"><?php echo $currentMinNum+$listSpan; ?>件</span>
    </div> -->

    <ul class="mypage__items">
    <?php
     foreach((array)$dbTopicList as $key => $val):
      //var_dump($dbTopicList);
    ?>

     <li class="mypage__item">
      <a href="topic.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['topic_id'] : '?t_id='.$val['topic_id']; ?>" class="mypage__item-link">
       <span class="mypage__item-word"><?php echo sanitize($val['word']); ?></span>
       <span class="mypage__item-detail">カテゴリー： <?php echo sanitize($val['cat_name']); ?></span>
       <span class="mypage__item-detail">品詞： <?php echo sanitize($val['parts_name']); ?></span>
      </a>
     </li>
    <?php
     endforeach;
    ?>
    </ul>

   </div>

   <?php
    pagination($currentPageNum, $dbTopicCount['total_page']);
   ?>

  </section>

  <?php
   require('sidebar.php');
  ?>

 </main>

<?php
 require('footer.php');
?>