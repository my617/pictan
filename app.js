$(window.addEventListener('DOMContentLoaded', function(){

 //footerを下部に固定
 var $footer = $('footer');
 if(window.innerHeight > $footer.offset().top + $footer.outerHeight()) {
   $footer.attr({'style': 'position:fixed; top:' + (window.innerHeight - $footer.outerHeight()) + 'px;'});
 }

 //ライブプレビュー
 var $dropArea = $('#js-img-drop');
 var $fileInput = $('#js-input-file');

 //画像選択された時
 $fileInput.on('change', function(e) {
   var file = this.files[0];
   $pic = $(this).siblings('#js-prev-img');
   $pic.css('opacity', '1');
   $picText = $(this).siblings('#js-input-text');
   $picText.css('display', 'none');

   fileReader = new FileReader();

   fileReader.onload = function(event) {
     $pic.attr('src', event.target.result).show();
   };

   fileReader.readAsDataURL(file);
 });

 //セレクトメニュー
 $('#js-submit-select').on('change', function() {
   $('#js-submit-form').submit();
 });
 
}));