(function($){
  $(window).load(function(){
    $("#navbar1 a,a[href='#top'],a[rel='m_PageScroll2id']").mPageScroll2id({
      highlightSelector:"#navbar1 a"
    });

    $("a[rel='next']").click(function(e){
      e.preventDefault();
      var to=$(this).parent().parent("section").next().attr("id");
      $.mPageScroll2id("scrollTo",to);
    });
  });
})(jQuery);
