<?php
//===== エラー出力 =====
ini_set('log_errors', 'on');
ini_set('error_log','pictan.log');

//===== デバック =====
$debug_flg = true;
function debug($str) {
 global $debug_flg;
 if(!empty($debug_flg)) {
  error_log('デバック：'.$str);
 }
}

//===== セッション準備 =====
//セッションがいるの置き場所
session_save_path("/var/tmp/");

//ガーベージコレクションが削除するセッションの有効期限
ini_set('session.gc_maxlifitime', 60*60*24*30);

//クッキーの有効期限を３０日
ini_set('session.cookie_lifetime', 60*60*24*30);

//セッションを使う
session_start();

//セッション再生成
session_regenerate_id();

//===== デバックログ開始 =====
function debugLogStart() {
 debug('>>>>>画面表示処理開始');
 debug('セッションID：'.session_id());
 debug('セッション変数の中身：'.print_r($_SESSION, true));
 debug('現在日時のタイムスタンプ：'.time());
 if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
  debug('ログイン期限日時タイムスタンプ：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
 }
}

//===== 定数 =====
//メッセージ定数
define('MSG01', 'エラーが発生しました。');
define('MSG02', '文字以内で入力してください。');
define('MSG03', 'メールアドレスの形式で入力してください。');
define('MSG04', '登録済みのメールアドレスです。');
define('MSG05', '半角英数字のみ入力可能です。');
define('MSG06', '文字以上で入力して下さい');
define('MSG07', 'パスワード（再入力）が一致しません。');
define('MSG08', 'メールアドレスとパスワードが一致しません。');
define('MSG09', '現在のパスワードと同じです。');
define('MSG10', '現在のパスワードと異なります。');
define('MSG11', '文字以内で入力して下さい。');
define('MSG12', '選択必須です。');
define('MSG13', 'JPEG形式・PNG形式・GIF形式で登録して下さい。');
define('MSG14', '入力必須です。');
define('MSG15', '文字で入力して下さい');
define('MSG16', '認証キーが無効です');
define('SUC01', '登録完了しました。');
define('SUC02', 'アカウント変更しました。');
define('SUC03', '編集完了しました。');
define('SUC04', 'カテゴリーを登録しました。');
define('SUC05', 'カテゴリーの編集完了しました。');
define('SUC06', 'カテゴリーを削除しました。');
define('SUC07', 'メール送信完了しました。');

//===== グローバル変数 ======
//エラーメッセージ用配列
$err_msg = array();

//===== バリデーション ======
//未入力チェック
function validRequired($str, $key) {
 if($str === '') {
  global $err_msg;
  $err_msg[$key] = MSG14;
 }
}

//Email形式チェック
function validEmail($str, $key) {
 if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
  global $err_msg;
  $err_msg[$key] = MSG03;
 }
}

//Email重複チェック
function validEmailDup($email) {
 global $err_msg;

 try{
  $dbh = dbConnect();
  $sql = 'SELECT count(*) FROM user WHERE email = :email AND delete_flg = 0';
  $data = array(':email' => $email);
  $stmt = queryPost($dbh, $sql, $data);
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  if(!empty(array_shift($result))) {
   $err_msg['email'] = MSG04;
  }

 } catch (Exception $e) {
  error_log('エラー：'.$e->getMessage());
  $err_msg['common'] = MSG01;
 }
}

//最小文字数
function validMinLen($str, $key, $min) {
 if(mb_strlen($str) < $min) {
  global $err_msg;
  $err_msg[$key] = $min.MSG06;
 }
}

//最大文字数
function validMaxLen($str, $key, $max) {
 if(mb_strlen($str) > $max) {
  global $err_msg;
  $err_msg[$key] = $max.MSG02;
 }
}

//半角英数字
function validHalf($str, $key) {
 if(!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
  global $err_msg;
  $err_msg[$key] = MSG05;
 }
}

//同値チェック
function validMatch($str1, $str2, $key) {
 if($str1 !== $str2) {
  global $err_msg;
  $err_msg[$key] = MSG07;
 }
}

//パスワードチェックまとめ
function validPass($str, $key) {
 validHalf($str, $key);
 validMinLen($str, $key, 6);
 validMaxLen($str, $key, 255);
}

//桁数チェック(8桁)
function validLength($str, $key, $length = 8) {
 if(mb_strlen($str) !== $length) {
  global $err_msg;
  $err_msg[$key] = $length.MSG14;
 }
}

//selectboxのチェック
//数字で無ければエラーを返す
function validSelect($str, $key) {
 if(!preg_match("/^[0-9]+$/", $str)) {
  global $err_msg;
  $err_msg[$key] = MSG01;
 }
}

//エラーメッセージ表示
function getErrMsg($key) {
 global $err_msg;
 if(!empty($err_msg[$key])) {
  return $err_msg[$key];
 }
}

//共通部分のエラーメッセージ
function errMsg($errStr, $errKey) {
  if(!empty($errStr[$errKey])) echo $errStr[$errKey];
}

//===== データベース =====
function dbConnect() {
 $dsn = 'mysql:dbname=pictan;host=localhost;charset=utf8';
 $user = 'root';
 $password = 'root';
 $options = array(
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
 );
 $dbh = new PDO($dsn, $user, $password, $options);
 return $dbh;
}

//SQL実行
function queryPost($dbh, $sql, $data) {
 $stmt = $dbh->prepare($sql);

 if(!$stmt->execute($data)) {
  debug('クエリに失敗しました');
  debug('失敗したSQL：'.print_r($stmt, true));
  debug('SQLエラー'.print_r($stmt->errorInfo(), true));
  global $err_msg;
  $err_msg['common'] = MSG01;
  return 0;
 }
 debug('クエリ成功');
 return $stmt;
}

//ユーザー情報取得
function getUser($u_id) {
 debug('ユーザー情報を取得します');
 try{
  $dbh = dbConnect();
  $sql = 'SELECT * FROM user WHERE user_id = :u_id AND delete_flg = 0';
  $data = array(':u_id' => $u_id);
  $stmt = queryPost($dbh, $sql, $data);

  if($stmt) {
   return $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
   return false;
  }
 } catch (Exception $e) {
  error_log('エラー：'.$e->getMessage());
  $err_msg['common'] = MSG01;
 }
}

//カテゴリー情報取得
function getCategory($u_id){
  debug('カテゴリー情報を取得します。');
 
  //例外処理
  try{
   $dbh = dbConnect();
   $sql = 'SELECT * FROM category WHERE user_id = :u_id AND delete_flg = 0';
   debug('SQLの中身：'.$sql);
   $data = array(':u_id' => $u_id);
   $stmt = queryPost($dbh, $sql, $data);
 
   if($stmt) {
    return $stmt->fetchAll();
   } else {
    return false;
   }
  } catch (Exception $e) {
   error_log('エラー発生:' . $e->getMessage());
  }
 }
 
 //品詞情報取得
 function getParts() {
  debug('品詞情報を取得します。');
 
   //例外情報
   try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM parts';
    debug('SQLの中身：'.$sql);
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);
 
    if($stmt) {
     return $stmt->fetchAll();
    } else {
     return false;
    }
   } catch(Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
   }
 }

//単語情報取得
function getTopic($u_id, $t_id) {
 debug('商品情報を取得します。');
 debug('ユーザーID：'.$u_id);
 debug('単語ID：'.$t_id);

 //例外処理
 try{
  $dbh = dbConnect();
  $sql = 'SELECT * FROM topic WHERE user_id = :u_id AND topic_id = :t_id AND delete_flg = 0';
  $data = array(':u_id' => $u_id, ':t_id' => $t_id);
  $stmt = queryPost($dbh, $sql, $data);

  if($stmt){
   return $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
   return false;
  }
 } catch(Exception $e) {
  error_log('エラー発生：' . $e->getMessage());
 }
}

//自分の総記事数と総ページ数を取得
function getTopicCount($u_id, $topicCategory, $topicParts, $span = 1) {
  debug('総記事数と総ページ数を取得します');

  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT topic_id, word, c.cat_name, p.parts_name 
    FROM topic AS t 
    LEFT JOIN category AS c ON t.cat_id = c.cat_id 
    LEFT JOIN parts AS p ON t.parts_id = p.parts_id 
    WHERE t.user_id = :u_id AND t.delete_flg = 0';
    //検索とソートの関係
    if(!empty($topicCategory)) {
      $sql .= ' AND t.cat_id = '.$topicCategory;
      //var_dump($topicCategory);
    }
    if(!empty($topicParts)) {
      $sql .= ' AND t.parts_id = '.$topicParts;
    }
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);
    //総件数を変数に入れる
    $result['total'] = $stmt->rowCount();
    $result['total_page'] = ceil($result['total']/$span);
    return $result;
    if(!$stmt) {
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
 }

//自分の記事一覧取得
function getTopicList($u_id, $currentMinNum=1, $topicCategory, $sort, $span = 1) {
  debug('記事一覧を取得します');

  try{
    $dbh = dbConnect();
    $sql = 'SELECT topic_id, word, c.cat_name, p.parts_name 
    FROM topic AS t 
    LEFT JOIN category AS c ON t.cat_id = c.cat_id 
    LEFT JOIN parts AS p ON t.parts_id = p.parts_id 
    WHERE t.user_id = :u_id AND t.delete_flg = 0';
    //検索とソートの関係
    if(!empty($topicCategory)) {
      $sql .= ' AND t.cat_id = '.$topicCategory;
    }
    if(!empty($topicParts)) {
      $sql .= ' AND t.parts_id = '.$topicParts;
    }
    //var_dump($sql);
    if (!empty($sort)) {
      switch ($sort) {
        //記事古い順
        case 1:
        $sql .= ' ORDER BY topic_id ASC';
        break;
        //記事新しい順
        case 2:
        $sql .= ' ORDER BY topic_id DESC';
        break;
      }
    }
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array(':u_id' => $u_id);
    debug('SQL:'.$sql);

    $stmt = queryPost($dbh, $sql, $data);
    if($stmt) {
      $result = $stmt -> fetchAll();
      return $result;
    } else {
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}

//カテゴリー名取得
function getCategoryDel($c_id) {
  debug('削除したいカテゴリー');

  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category WHERE cat_id = :c_id AND delete_flg = 0';
    $data = array(':c_id' => $c_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debug('削除したいカテゴリーを選択しました');
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      debug('削除したいカテゴリーを選択できませんでした');
      return false;
    }
  } catch(Exception $e) {
    error_log('エラー：' . $e->getMessage());
    $err_log['common'] = MSG01;
  }
}

//単語の詳細取得
function getTopicData($t_id) {
  debug('単語詳細を取得します。');

  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT t.word, t.mean, t.text, t.trans, t.pic, t.created_date, cat_name, parts_name 
    FROM topic AS t 
    LEFT JOIN category AS c ON t.cat_id = c.cat_id 
    LEFT JOIN parts AS p ON t.parts_id = p.parts_id 
    LEFT JOIN user AS u ON t.user_id = u.user_id 
    WHERE t.topic_id = :t_id AND t.delete_flg = 0';
    //var_dump($sql);
    $data = array(':t_id' => $t_id);
    //var_dump($data);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch(Exception $e) {
    debug('エラー：' . $e->getMessage());
    $err_msg['common'] = MSG01;
  }
}

//===== その他 =====
//サニタイズ
function sanitize($str) {
 return htmlspecialchars($str, ENT_QUOTES);
}

//フォーム入力保持
function getFormData($str, $flg = false) {

 //フラグが立っていたらGET送信
 //デフォルトはフラグ無し＝POST送信
 if($flg) {
  $method = $_GET;
 } else {
  $method = $_POST;
 }
 //グローバル変数
 //内容は各ページで定義
 global $dbFormData;
 //DBにユーザー情報がある場合
 if(!empty($dbFormData)) {
  //入力フォームにエラーがある場合
  if(!empty($err_msg[$str])) {
   //POSTないしGETにデータがあればサニタイズ
   if(isset($method[$str])) {
    return sanitize($method[$str]);
   } else {
    //データが無い場合はDBの情報を表示
    return $dbFormData[$str];
   }
  } else {
   //POSTに情報がアリかつDBとPOSTの情報が違うときはPOSTの情報をサニタイズして返す
   if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
    return sanitize($method[$str]);
   } else {
    //POSTに情報が無い、もしくはDBと同じ情報が入っているときはDBの情報をサニタイズして渡す
    return sanitize($dbFormData[$str]);
   }
  }
 } else {
  //DBにユーザー情報が無い場合
  //GETやPOSTに情報があればそれをサニタイズして表示
  if(isset($method[$str])) {
   return sanitize($method[$str]);
  }
 }
}

//フォーム入力保持$dbFormDataがユーザ情報で使われていた時版
function getSubFormData($str, $flg = false){
  //フラグが立っていたらGET送信
  //デフォルトはフラグ無し＝POST送信
  if($flg){
      $method = $_GET;
  } else {
      $method = $_POST;
  }
  //グローバル変数
  //内容は各ページで定義
  global $dbFormData;
  //DBにユーザー情報がある場合
  if(!empty($dbSubFormData)){
      //入力フォームにエラーがある場合
      if(!empty($err_msg[$str])){
          //POSTないしGETにデータがあればサニタイズ
          if(isset($method[$str])){
              return sanitize($method[$str]);
          } else {
              //データが無い場合はDBの情報を表示
              return $dbSubFormData[$str];
          }
      } else {
          //POSTに情報がアリかつDBとPOSTの情報が違うときは
          //POSTの情報をサニタイズして返す
          if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
              return sanitize($method[$str]);
          } else {
              //POSTに情報が無い、もしくはDBと同じ情報が入っているときは
              //DBの情報をサニタイズして渡す
              return sanitize($dbSubFormData[$str]);
          }
      }
  } else {
      //DBにユーザー情報が無い場合
      //GETやPOSTに情報があればそれをサニタイズして表示
      if(isset($method[$str])){
          return sanitize($method[$str]);
      }
  }
}

//セッションを1回だけ取得
function getSessionFlash($key) {
 if(!empty($_SESSION[$key])) {
  $msgData = $_SESSION[$key];
  $_SESSION[$key] = '';
  return $msgData;
 }
}

//GETパラメータ生成
function appendGetParam($_arr_del_key = array()) {
 //GETパラメータがあるとき
 if(!empty($_GET)) {
  //最初に?をつける
  $str = '?';
  foreach($_GET as $key => $val) {
   if(!in_array($key, $_arr_del_key, true)) {
    $str .= $key.'='.$val.'&';
   }
  }
  $str = mb_substr($str, 0, -1, "UTF-8");
  return $str;
 }
}

//画像処理
function uploadImg($file, $key) {
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file, true));

  if(isset($file['error']) && is_int($file['error'])) {
    try{
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch($file['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:    // ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INT_SIZE:   // php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FROM_SIZE:    // フォーム定義の最大サイズ超過した場合
          throw new RuntimeExeption('ファイルサイズが大きすぎます');
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'upload/'.sha1_file($file['tmp_name']);
      if(!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();

    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
      
    }
  }
}

//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination($currentPageNum, $totalPageNum, $link = '', $c_id = '' , $p_id = '', $search = '', $pageColNum = 5) {
  if($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  } elseif($currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  } elseif($currentPageNum == 2 && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  } elseif($currentPageNum == 1 && $totalPageNum > $pageColNum) {
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  } elseif($totalPageNum < $pageColNum) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  } else {
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination__items">';
    if(!empty($search)){
      if($currentPageNum != 1) {
        echo '<li class="pagination__item"><a href="mypage.php?p=1' . $link . '&c_id='.$topicCategory.'&p_id='.$p_id.'&search='.$search.'" class="pagination__link">&lt;</a></li>';
      }
        for($i = $minpageNum; $i <= $maxPageNum; $i++) {
          echo '<li class="pagination__item ';
          if($currentPageNum == $i) {
            echo 'active';
          }
          echo '"><a href="mypage.php?p=' . $i . $link . '&c_id='.$topicCategory.'&p_id'.$p_id.'&search'.$search.'" class="pagination__link">' . $i . '</a></li>';
        }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1) {
        echo '<li class="pagination__item"><a href="mypage.php?p=' . $totalPageNum . $link . '&c_id='.$topicCategory.'&p_id='.$p_id.'&search='.$search.'" class="pagination__link">&gt;</a></li>';
      }
    } else {
      if($currentPageNum != 1) {
        echo '<li class="pagination__item"><a href="mypage.php?p=1' . $link . '" class="pagination__link">&lt;</a></li>';
      }
        for($i = $minPageNum; $i <= $maxPageNum; $i++) {
          echo '<li class="pagination__item ';
          if($currentPageNum == $i) {
            echo 'active';
          }
          echo '"><a href="mypage.php?p=' . $i . $link . '" class="pagination__link">' . $i . '</a></li>';
        }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1) {
        echo '<li class="pagination__item"><a href="mypage.php?p=' . $totalPageNum . $link . '" class="pagination__link">&gt;</a></li>';
      }
    }
      echo '</ul>';
    echo '</div>';
  }

//8桁の認証キーを作成する
function maleRandKey($length = 8) {
  $chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $str = '';
  for($i = 0; $i < $length; ++$i) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}

//メール送信
function sendMail($form, $to, $subject, $comment) {
  if(!empty($to) && !empty($subject) && !empty($comment)) {

    //文字化け対策
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    //メール送信
    $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
    //送信結果判定
    if($result) {
      debug('メールを送信しました');
    } else {
      debug('メールの送信に失敗しました');
    }
  }
}

?>

