<?php
 ini_set('display_errors', "On");

 require('function.php');

 debug('===== トップページ =====');
 debugLogStart();

 // カレントページ。デフォルトページは１ページ
 $currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
 // カテゴリー
 $topicCategory = (!empty($_GET['cat_id'])) ? $_GET['cat_id'] : '';
 //品詞
 $topicParts = (!empty($_GET['parts_id'])) ? $_GET['parts_id'] : '';
 // ソート順
 $sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
 //DBからユーザーデータを取得
 if(!empty($_SESSION['user_id'])){
  $dbFormData = getUser($_SESSION['user_id']);
}
 
 // パラメータに不正な値が入っているかチェック
 //if(!is_int((int)$currentPageNum)) {
 // error_log('エラー発生:指定ページに不正な値が入りました');
 // header("Location:index.php");
 //}

 // 表示件数
 $listSpan = 20;
 // 現在の表示レコード先頭を算出
 $currentMinNum = (($currentPageNum - 1) * $listSpan);
 // DBから単語データを取得
 $dbTopicData = getTopicList($currentPageNum, $topicCategory, $topicParts, $sort);
 // DBからカテゴリデータを取得
 $dbCategoryData = getCategory($_SESSION['user_id']);
 // DBから品詞データを取得
 $dbPartsData = getParts();
 //日付、デフォルトは新しい記事から表示
 $sort = (!empty($_GET['sort'])) ? $_GET['sort'] : 2;
 //総記事数と総ページ数を取得
 $dbTopicData = getTopicCount($_SESSION['user_id'], $topicCategory, $topicParts);
 debug('記事数：'.print_r($dbTopicCount, true));

 debug('>>>>>画面表示処理終了');

 $siteTitle = 'ぴくたん';
 require('head.php');
 require('header.php');
?>

 <main>

  <section class="list">
   <h2 class="section__title">単語一覧</h2>

   <div class="list__search">
    <h3 class="section__subtitle">単語を探す</h3>

    <form action="" method="get" id="submit-form">
     <select name="cat_id" class="select__submit">
      <option value="0">言語カテゴリーで探す</option>
      <?php
       foreach($dbCategoryData as $key => $val) {
      ?>
      <option value="<?php echo $val['cat_id']; ?>"
        <?php
         if(getSubFormData('cat_id', true) == $val['cat_id']) { echo 'selected'; }
        ?>>
        <?php echo $val['cat_name']; ?>
      </option>
     <?php 
      }
     ?>
     </select>
    </form>

    <form action="" method="get" id="submit-form">
     <select name="parts_id" class="select__submit">
      <option value="0">品詞で探す</option>
      <?php
       foreach($dbPartsData as $key => $val) {
      ?>
      <option value="<?php echo $val['parts_id']; ?>"
        <?php if(getSubFormData('parts_id', true) == $val['parts_id']) { echo 'selected'; } ?>>
        <?php echo $val['parts_name']; ?>
      </option>
      <?php
       }
      ?>
     </select>
    </form>

    <form action="#" method="get">
     <input type="text" name="search" placeholder="フリーワード検索">
     <input type="submit" class="btn__sm" value="検索">
    </form>

   </div>

   <div class="list__inner">

    <div class="list__total">
     <span class="list__num"><?php echo $currentMinNum+1; ?></span> / 
     <span class="list__num"><?php echo $currentMinNum+$listSpan; ?>件</span>
    </div>

    <ul class="list__item">
    <?php
     foreach((array)$dbTopicData as $key => $val):
      var_dump($dbTopicData);
    ?>

     <li class="list__item">
      <a href="topic.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&t_id='.$val['topic_id'] : '?t_id='.$val['topic_id']; ?>">
       <?php echo sanitize($val['word']); ?>
       <?php echo sanitize($val['cat_name']); ?>
       <?php echo sanitize($val['parts_name']); ?>
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