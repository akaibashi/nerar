$(function(){

  /* 吸い付くメニュー */
  var bt = $(".box").offset().top;
  var ds = 0;
  $(document).scroll(function(){
      ds = $(this).scrollTop();
      if (bt <= ds) {
          $(".box").addClass('follow');
      } else if (bt >= ds) {
          $(".box").removeClass('follow');
      }
  });


  /* hot tags クリック時 */
  function hot_tag_click(){
    // 現在の位置に背景を固定する
    $("body").removeClass('scroll');  
    scrollTop = $(window).scrollTop();
    $('body').addClass('noscroll').css('top', (-scrollTop) + 'px');
    // light box表示
    $("#tag_canvas_background").fadeIn("fast", function(){
    });        
    // 詳細画面は先頭から表示
    $("#tag_canvas_background").scrollTop(0); 
  }
  $("#hot_tags_btn_login").on('click', function () {  
    hot_tag_click();
  });  
  $("#hot_tags_btn_nologin").on('click', function () {     
    hot_tag_click();
  });  

  // hot tags light box背景クリック時
  $("#tag_canvas_background").click(function () {
    // light boxが表示されてたら非表示に
    if($("#tag_canvas_background").css("display") == 'block') {
      $("#tag_canvas_background").fadeOut("slow");    
      // 背景固定解除
      $("body").removeClass('noscroll');  
      // スクロール位置を元に戻す
      $(window).scrollTop(scrollTop);
      $("body").addClass('scroll');              
    }
  });    


  /* tagの検索関連 */
  // フォーカスが当たった場合
  $(".keywords").on('click', function () {     
    var self = $(this);
    var value = $(self).attr("value");    
    if(value == "Please input tag.."){        
      $(self).attr("value", "");
      $(self).removeClass('unfocus_search_text');  
      $(self).addClass("focus_search_text"); 
    }else{
      $(self).removeClass('unfocus_search_text');  
      $(self).addClass("focus_search_text");       
    }
  });  
  // フォーカスがはずれた場合
  $(".keywords").on('blur', function () {          
    var self = $(this);
    var value = $(self).attr("value");    
    if(value.length == 0){        
      $(self).attr("value", "Please input tag..");
      $(self).removeClass('focus_search_text');  
      $(self).addClass("unfocus_search_text"); 
    }
  });  

});


/* hot tags 関連処理 */
window.onload = function() {
  try {
    TagCanvas.Start('myCanvas','tags',{
      textColour: '#493c3c',
      outlineColour: '#ad9c9c',
      reverse: true,
      depth: 0.5,
      maxSpeed: 0.03,
      textHeight: 50,
      textFont: "corda_light",
      wheelZoom: false
    });
  } catch(e) {
    // something went wrong, hide the canvas container
    document.getElementById('myCanvasContainer').style.display = 'none';
  }
};
