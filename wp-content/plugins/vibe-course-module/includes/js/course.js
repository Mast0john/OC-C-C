;(function($) {
(function(e){"use strict";function t(t){var n=e("");try{n=e(t).clone()}catch(r){n=e("<span />").html(t)}return n}function n(e){return!!(typeof Node==="object"?e instanceof Node:e&&typeof e==="object"&&typeof e.nodeType==="number"&&typeof e.nodeName==="string")}e.print=e.fn.print=function(){var r,i,s=this;if(s instanceof e){s=s.get(0)}if(n(s)){i=e(s);if(arguments.length>0){r=arguments[0]}}else{if(arguments.length>0){i=e(arguments[0]);if(n(i[0])){if(arguments.length>1){r=arguments[1]}}else{r=arguments[0];i=e("html")}}else{i=e("html")}}var o={globalStyles:true,mediaPrint:false,stylesheet:null,noPrintSelector:".no-print",iframe:true,append:null,prepend:null};r=e.extend({},o,r||{});var u=e("");if(r.globalStyles){u=e("style, link, meta, title")}else if(r.mediaPrint){u=e("link[media=print]")}if(r.stylesheet){u=e.merge(u,e('<link rel="stylesheet" href="'+r.stylesheet+'">'))}var a=i.clone();a=e("<span/>").append(a);a.find(r.noPrintSelector).remove();a.append(u.clone());a.append(t(r.append));a.prepend(t(r.prepend));var f=a.html();a.remove();var l,c;if(r.iframe){try{var h=e(r.iframe+"");var p=h.length;if(p===0){h=e('<iframe height="0" width="0" border="0" wmode="Opaque"/>').prependTo("body").css({position:"absolute",top:-999,left:-999})}l=h.get(0);l=l.contentWindow||l.contentDocument||l;c=l.document||l.contentDocument||l;c.open();c.write(f);c.close();setTimeout(function(){l.focus();l.print();setTimeout(function(){if(p===0){h.remove()}},100)},250)}catch(d){console.error("Failed to print from iframe",d.stack,d.message);l=window.open();l.document.write(f);l.document.close();l.focus();l.print();l.close()}}else{l=window.open();l.document.write(f);l.document.close();l.focus();l.print();l.close()}return this}})(jQuery);
$.fn.timer = function( useroptions ){ 
    var $this = $(this), opt,newVal, count = 0; 

    opt = $.extend( { 
        // Config 
        'timer' : 300, // 300 second default
        'width' : 24 ,
        'height' : 24 ,
        'fgColor' : "#ED7A53" ,
        'bgColor' : "#232323" 
        }, useroptions 
    ); 
    $this.knob({ 
        'min':0, 
        'max': opt.timer, 
        'readOnly': true, 
        'width': opt.width, 
        'height': opt.height, 
        'fgColor': opt.fgColor, 
        'bgColor': opt.bgColor,                 
        'displayInput' : false, 
        'dynamicDraw': false, 
        'ticks': 0, 
        'thickness': 0.1 
    }); 
    setInterval(function(){ 
        newVal = ++count; 
        $this.val(newVal).trigger('change'); 
    }, 1000); 
};
$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return results[1] || 0;
    }
}
// Necessary functions
function runnecessaryfunctions(){
  
  jQuery('.fitvids').fitVids();
  jQuery('.tip').tooltip();
  jQuery('.nav-tabs li:first a').tab('show');
  jQuery('.nav-tabs li a').click(function(event){
    event.preventDefault();
    $(this).tab('show');
  });
  $('audio,video').mediaelementplayer();
  jQuery('.gallery').magnificPopup({
  delegate: 'a',
  type: 'image',
  tLoading: 'Loading image #%curr%...',
  mainClass: 'mfp-img-mobile',
  gallery: {
    enabled: true,
    navigateByImgClick: true,
    preload: [0,1] // Will preload 0 - before current, and 1 after the current image
  },
  image: {
    tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
    titleSrc: function(item) {
      return item.el.attr('title');
    }
  }
});
$('.ajax-popup-link').magnificPopup({
    type: 'ajax',
    alignTop: true,
    fixedContentPos: true,
    fixedBgPos: true,
    overflowY: 'auto',
    closeBtnInside: true,
    preloader: false,
    midClick: true,
    removalDelay: 300,
    mainClass: 'my-mfp-zoom-in'
});
$('.quiz_results_popup').magnificPopup({
    type: 'ajax',
    alignTop: true,
    fixedContentPos: true,
    fixedBgPos: true,
    overflowY: 'auto',
    closeBtnInside: true,
    preloader: false,
    midClick: true,
    removalDelay: 300,
    mainClass: 'my-mfp-zoom-in',
    callbacks: {
             parseAjax: function( mfpResponse ) {
              mfpResponse.data = $(mfpResponse.data).find('#item-body');
            }
          }
});

if ( typeof vc_js == 'function' ) { 
    window.vc_js();
  }

}

//AJAX Comments
function ajaxsubmit_comments(){
  $('#question').each(function(){

   var $this=$(this);
  $('#submit').click(function(event){
    event.preventDefault();
    var value = '';

    $('#ajaxloader').removeClass('disabled');
    $('#question').css('opacity',0.2);

    if($this.find('input[type="radio"]:checked').length)
    $this.find('input[type="radio"]:checked').each(function(){
      value = $(this).val();
    });
    if($this.find('input[type="checkbox"]:checked').length)
    $this.find('input[type="checkbox"]:checked').each(function(){
      value= $(this).val()+','+value;
    });

    if($this.find('.vibe_fillblank').length)
    $this.find('.vibe_fillblank').each(function(){
      value += $(this).text();  
    });
    if($this.find('#vibe_select_dropdown').length)
    value = $this.find('#vibe_select_dropdown').val();

    if($this.find('.matchgrid_options li.match_option').length){
        $('.matchgrid_options li.match_option').each(function(){
        var id = $(this).attr('id');
        if( jQuery.isNumeric(id))
          value +=id+',';
      });  
    }

    if($('#comment').hasClass('option_value'))
      $('#comment.option_value').val(value);

    $('#commentform').submit();
  });
    
  var commentform=$('#commentform'); // find the comment form
  var statusdiv=$('#comment-status'); // define the infopanel
  var qid = statusdiv.attr('data-quesid');
  
  commentform.submit(function(){

    var formdata=commentform.serialize();

    statusdiv.html('<p>'+vibe_course_module_strings.processing+'</p>');

    var formurl=commentform.attr('action');

    $.ajax({
      type: 'post',
      url: formurl,
      data: formdata,
      error: function(XMLHttpRequest, textStatus, errorThrown){
        $('#ajaxloader').addClass('disabled');
        $('#question').css('opacity',1);
        statusdiv.html('<p class="wdpajax-error">'+vibe_course_module_strings.too_fast_answer+'</p>');
        setTimeout(function(){statusdiv.hide(300).html('').show();}, 2000);
      },
      success: function(data, textStatus){
        $('#question').css('opacity',1);
        $('#ajaxloader').addClass('disabled');
        if(data=="success"){
          statusdiv.html('<p class="ajax-success" >'+vibe_course_module_strings.answer_saved+'</p>');
          setTimeout(function(){statusdiv.hide(300).html('').show();}, 2000);
          $('#ques'+qid).addClass('done');
          $('.reset_answer').removeClass('hide');
        }
        else{
          statusdiv.html('<p class="ajax-error" >'+vibe_course_module_strings.saving_answer+'</p>'); 
          setTimeout(function(){statusdiv.hide(300).html('').show();}, 2000);
        }
      }
    });
    return false;
    });
  }); 
} // END Function

//Cookie evaluation
jQuery(document).ready( function($) {
  $('.item-list').each(function(){
    var cookie_name = $('.item-list').attr('id');
    var cookieValue = $.cookie(cookie_name);
    if ((cookieValue !== null) && cookieValue == 'list') {      
      $('.item-list').removeClass('grid');
      $('#list_view').addClass('active');
      $('#grid_view').removeClass('active');
    }
  });

function bp_course_extras_cookies(){
  $('.category_filter .bp-course-category-filter,.type_filter .bp-course-free-filter,.level_filter .bp-course-level-filter,.instructor_filter .bp-course-instructor-filter').on('click',function(){
     var category_filter=[];
     $('.bp-course-category-filter:checked').each(function(){
        var category={'type':'course-cat','value':$(this).val()};
        category_filter.push(category);
     });
     $('.bp-course-free-filter:checked').each(function(){
      var free={'type':'free','value':$(this).val()};
        category_filter.push(free);
     });
     $('.bp-course-level-filter:checked').each(function(){
      var level={'type':'level','value':$(this).val()};
        category_filter.push(level);
     });
     $('.bp-course-instructor-filter:checked').each(function(){
      var level={'type':'instructor','value':$(this).val()};
        category_filter.push(level);
     });
     $.cookie('bp-course-extras', JSON.stringify(category_filter), { expires: 1 ,path: '/'});
  });
}

function bp_course_category_filter_cookie(){
    var category_filter_cookie =  $.cookie("bp-course-extras");
    if ((category_filter_cookie !== null)) {      
        var category_filter = JSON.parse(category_filter_cookie);

        if($('#active_filters').length){
          $('#active_filters').fadeIn(200);
        }else{
          $('#course-dir-list').before('<ul id="active_filters"><li>'+vibe_course_module_strings.active_filters+'</li></ul>');
        }

        //Detect and activate specific filters
        jQuery.each(category_filter, function(index, item) {
            $('input[value="'+item['value']+'"]').prop('checked', true);
            var id = $('input[value="'+item['value']+'"]').attr('id');
            var text = $('label[for="'+id+'"]').text();
            if(!$('#active_filters span[data-id="'+id+'"]').length)
              $('#active_filters').append('<li><span data-id="'+id+'">'+text+'</span></li>');
        });
        // Delete a specific filter
        $('#active_filters li span').on('click',function(){
           var id = $(this).attr('data-id');console.log(id);
           $(this).parent().fadeOut(200,function(){
            $(this).remove();
            if($('#active_filters li').length < 3)
              $('#active_filters').fadeOut(200);
            else    
              $('#active_filters').fadeIn(200);
          });
           $('#'+id).prop('checked',false);
           /*===== */ 
           var category_filter=[];
           $('.bp-course-category-filter:checked').each(function(){
              var category={'type':'course-cat','value':$(this).val()};
              category_filter.push(category);
           });
           $('.bp-course-free-filter:checked').each(function(){
            var free={'type':'free','value':$(this).val()};
              category_filter.push(free);
           });
           $('.bp-course-level-filter:checked').each(function(){
            var level={'type':'level','value':$(this).val()};
              category_filter.push(level);
           });
           $('.bp-course-instructor-filter:checked').each(function(){
            var level={'type':'instructor','value':$(this).val()};
              category_filter.push(level);
           });
           $.cookie('bp-course-extras', JSON.stringify(category_filter), { expires: 1 ,path: '/'});
           $('#submit_filters').trigger('click');
           /* ==== */
        });

        if(!$('#active_filters .all-filter-clear').length)
            $('#active_filters').append('<li class="all-filter-clear">'+vibe_course_module_strings.clear_filters+'</li>');

        // Clear all Filters link
        $('#active_filters li.all-filter-clear').click(function(){
            $('#active_filters li').each(function(){
              var span = $(this).find('span');
               var id = span.attr('data-id');
               span.parent().fadeOut(200,function(){
                  $(this).remove(); });
                 $('#'+id).prop('checked',false);
              $('#active_filters').fadeOut(200,function(){
                $(this).remove();
              });   
              $.removeCookie('bp-course-extras', { path: '/' });
              console.log('check');
              $('#submit_filters').trigger('click');
            });
        });
        // End Clear All
           // Hide is no filter active
        if($('#active_filters li').length < 3)
          $('#active_filters').fadeOut(200);
        else    
          $('#active_filters').fadeIn(200);
    }
}


bp_course_category_filter_cookie();
bp_course_extras_cookies();

/*=========================================================================*/

  $('.category_filter li > label').click(function(event){
    var parent= $(this).parent();
    parent.find('ul.sub_categories').toggle(300);
  });
  
  $('#submit_filters').on('click',function(){ 
      if ( jq('.item-list-tabs li.selected').length )
      var el = jq('.item-list-tabs li.selected');
      else
        var el = jq(this);

      var css_id = el.attr('id').split('-');
      var object = css_id[0];
      var scope = css_id[1];
      var filter = jq(this).val();
      var search_terms = false;

      if ( jq('.dir-search input').length )
        search_terms = jq('.dir-search input').val();

      if ( 'friends' == object )
        object = 'members';

      bp_course_extras_cookies();
      bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jq.cookie('bp-' + object + '-extras') );
      bp_course_category_filter_cookie();
      return false;
  });

  $('.quiz_results_popup').magnificPopup({
      type: 'ajax',
      alignTop: true,
      fixedContentPos: true,
      fixedBgPos: true,
      overflowY: 'auto',
      closeBtnInside: true,
      preloader: false,
      midClick: true,
      removalDelay: 300,
      mainClass: 'my-mfp-zoom-in',
      callbacks: {
          parseAjax: function( mfpResponse ) {
                mfpResponse.data = $(mfpResponse.data).find('#item-body');
              },
          ajaxContentAdded: function() {        {
                $('#prev_results a').on('click',function(event){
                    event.preventDefault();
                    $(this).toggleClass('show');
                    $('.prev_quiz_results').toggleClass('show');
                });
                $('.print_results').click(function(event){
                    event.preventDefault();
                    $('.quiz_result').print();
                });
              }
            }
      }      
  });    
  $('#grid_view').click(function(){
    if(!$('.item-list').hasClass('grid')){
      $('.item-list').addClass('grid');
    }
    var cookie_name = $('.item-list').attr('id');
    $.cookie(cookie_name, 'grid', { expires: 2 ,path: '/'});
    $('#list_view').removeClass('active');
    $(this).addClass('active');
  });
  $('#list_view').click(function(){
    $('.item-list').removeClass('grid');
    var cookie_name = $('.item-list').attr('id');
    $.cookie(cookie_name, 'list', { expires: 2 ,path: '/'});
    $('#grid_view').removeClass('active');
    $(this).addClass('active');
  });
  $("#average .dial").knob({
      'readOnly': true, 
      'width': 120, 
      'height': 120, 
      'fgColor': vibe_course_module_strings.theme_color, 
      'bgColor': '#f6f6f6',   
      'thickness': 0.1
  });
  $("#pass .dial").knob({
      'readOnly': true, 
      'width': 120, 
      'height': 120, 
      'fgColor': vibe_course_module_strings.theme_color, 
      'bgColor': '#f6f6f6',   
      'thickness': 0.1
  });
  $("#badge .dial").knob({
      'readOnly': true, 
      'width': 120, 
      'height': 120, 
      'fgColor': vibe_course_module_strings.theme_color, 
      'bgColor': '#f6f6f6',   
      'thickness': 0.1
  });

  $(".course_quiz .dial").knob({
      'readOnly': true, 
      'width': 120, 
      'height': 120, 
      'fgColor': vibe_course_module_strings.theme_color, 
      'bgColor': '#f6f6f6',   
      'thickness': 0.1 
  });

  //RESET Ajx
$( 'body' ).delegate( '.remove_user_course','click',function(event){
      event.preventDefault();
      var course_id=$(this).attr('data-course');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      var $this = $(this);
      $.confirm({
          text: vibe_course_module_strings.remove_user_text,
          confirm: function() {
             $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: { action: 'remove_user_course', 
                            security: $('#security').val(),
                            id: course_id,
                            user: user_id
                          },
                    cache: false,
                    success: function (html) {
                        $(this).removeClass('animated');
                        $(this).removeClass('spin');
                        runnecessaryfunctions();
                        $('#message').html(html);
                        $('#s'+user_id).fadeOut('fast');
                    }
            });
          },
          cancel: function() {
              $this.removeClass('animated');
              $this.removeClass('spin');
          },
          confirmButton: vibe_course_module_strings.remove_user_button,
          cancelButton: vibe_course_module_strings.cancel
      });
  });

$( 'body' ).delegate( '.reset_course_user','click',function(event){
      event.preventDefault();
      var course_id=$(this).attr('data-course');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      var $this = $(this);
      $.confirm({
        text: vibe_course_module_strings.reset_user_text,
          confirm: function() {
          $.ajax({
                  type: "POST",
                  url: ajaxurl,
                  data: { action: 'reset_course_user', 
                          security: $('#security').val(),
                          id: course_id,
                          user: user_id
                        },
                  cache: false,
                  success: function (html) {
                      $this.removeClass('animated');
                      $this.removeClass('spin');

                      var cookie_id = 'course_progress'+course_id;
                      $.removeCookie(cookie_id,{ path: '/' });

                      $('#message').html(html);
                  }
          });
         }, 
         cancel: function() {
              $this.removeClass('animated');
              $this.removeClass('spin');
          },
          confirmButton: vibe_course_module_strings.reset_user_button,
          cancelButton: vibe_course_module_strings.cancel
        });
  });

  
$( 'body' ).delegate( '.course_stats_user', 'click', function(event){
      event.preventDefault();
      var $this=$(this);
      var course_id=$this.attr('data-course');
      var user_id=$this.attr('data-user');
      
      if($this.hasClass('already')){
        $('#s'+user_id).find('.course_stats_user').fadeIn('fast');
      }else{
          $this.addClass('animated spin');    
        $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'course_stats_user', 
                        security: $('#security').val(),
                        id: course_id,
                        user: user_id
                      },
                cache: false,
                success: function (html) {
                    $this.removeClass('animated');
                    $this.removeClass('spin');
                    $this.addClass('already');
                    $('#s'+user_id).append(html);
                    $(".dial").knob({
                      'readOnly': true, 
                  'width': 160, 
                  'height': 160, 
                  'fgColor': vibe_course_module_strings.theme_color, 
                  'bgColor': '#f6f6f6',   
                  'thickness': 0.3 
                    });
                }
        });
      }
  });
  
  
  $('.data_stats li').click(function(event){
    event.preventDefault();
    var defaultxt = $(this).html();
    var content = $('.content');
    var $this = $(this);
    var id = $(this).attr('id');

    if(id == 'desc'){
      $('.main_content').show();
      $('.stats_content').hide();
    }else{
      if($(this).hasClass('loaded')){
        $('.main_content').hide();
        $('.stats_content').show();
      }else{
         $this.addClass('loaded');  
         $('.main_content').hide();
         $(this).html('<i class="icon-sun-stroke"></i>');
         var quiz_id = $this.parent().attr('data-id');
         var cpttype = $this.parent().attr('data-type');
         $.ajax({
                type: "POST",
                url: ajaxurl,
                data: { action: 'load_stats', 
                        cpttype: cpttype,
                        id: quiz_id
                      },
                cache: false,
                success: function (html) {
                    $('.main_content').after(html);
                    setTimeout(function(){$this.html(defaultxt); }, 1000);
                }
        });
      }
    }
    $this.parent().find('.active').removeClass('active');
    $this.addClass('active');
  });

  $('#calculate_avg_course').click(function(event){
      event.preventDefault();
      var course_id=$(this).attr('data-courseid');
      $(this).addClass('animated spin');

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'calculate_stats_course', 
                      security: $('#security').val(),
                      id: course_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('#message').html(html);
                   setTimeout(function(){location.reload();}, 3000);
              }
      });

  });

  $('.reset_quiz_user').click(function(event){
      event.preventDefault();
      var course_id=$(this).attr('data-quiz');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');
      var $this = $(this);
      $.confirm({
          text: vibe_course_module_strings.quiz_rest,
          confirm: function() {

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'reset_quiz', 
                      security: $('#qsecurity').val(),
                      id: course_id,
                      user: user_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('#message').html(html);
                  $('#qs'+user_id).fadeOut('fast');
              }
      });
      }, 
       cancel: function() {
            $this.removeClass('animated');
            $this.removeClass('spin');
        },
        confirmButton: vibe_course_module_strings.quiz_rest_button,
        cancelButton: vibe_course_module_strings.cancel
      });
  });

  $('.evaluate_quiz_user').click(function(event){
      event.preventDefault();
      var quiz_id=$(this).attr('data-quiz');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'evaluate_quiz', 
                      security: $('#qsecurity').val(),
                      id: quiz_id,
                      user: user_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('.quiz_students').html(html);
                  calculate_total_marks();
              }
      });
  });


 $('.evaluate_course_user').click(function(event){
      event.preventDefault();
      var course_id=$(this).attr('data-course');
      var user_id=$(this).attr('data-user');
      $(this).addClass('animated spin');

      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'evaluate_course', 
                      security: $('#security').val(),
                      id: course_id,
                      user: user_id
                    },
              cache: false,
              success: function (html) {
                  $(this).removeClass('animated');
                  $(this).removeClass('spin');
                  $('.course_students').html(html);
                  calculate_total_marks();
              }
      });
  });

$( 'body' ).delegate( '.reset_answer', 'click', function(event){
       event.preventDefault();
      var ques_id=$('#comment-status').attr('data-quesid');
      var $this = $(this);
      var qid = $('#comment-status').attr('data-quesid');
      $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'reset_question_answer', 
                      security: $this.attr('data-security'),
                      ques_id: ques_id,
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                   $('#comment-status').html(html);
                   $('#ques'+qid).removeClass('done');
                   setTimeout(function(){ $this.addClass('hide');}, 500);
              }
      });
});

$( 'body' ).delegate( '#course_complete', 'click', function(event){
      event.preventDefault();
      var $this=$(this);
      var user_id=$this.attr('data-user');
      var course = $this.attr('data-course');
      var marks = parseInt($('#course_marks_field').val());
      if(marks <= 0){
        alert('Enter Marks for User');
        return;
      }

      $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'complete_course_marks', 
                      course: course,
                      user: user_id,
                      marks:marks
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                  $this.html(html);
              }
      });
});

  // Registeration BuddyPress
  $('.register-section h4').click(function(){
      $(this).toggleClass('show');
      $(this).parent().find('.editfield').toggle('fast');
  });

});

$( 'body' ).delegate( '.hide_parent', 'click', function(event){
  $(this).parent().fadeOut('fast');
});


$( 'body' ).delegate( '.give_marks', 'click', function(event){
      event.preventDefault();
      var $this=$(this);
      var ansid=$this.attr('data-ans-id');
      var aval = $('#'+ansid).val();
      $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'give_marks', 
                      aid: ansid,
                      aval: aval
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                  $this.html(vibe_course_module_strings.marks_saved);
              }
      });
});

$( 'body' ).delegate( '#mark_complete', 'click', function(event){
    event.preventDefault();
    var $this=$(this);
    var quiz_id=$this.attr('data-quiz');
    var user_id = $this.attr('data-user');
    var marks = parseInt($('#total_marks strong > span').text());
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'save_quiz_marks', 
                    quiz_id: quiz_id,
                    user_id: user_id,
                    marks: marks,
                  },
            cache: false,
            success: function (html) {
                $this.find('i').remove();
                $this.html(vibe_course_module_strings.quiz_marks_saved);
            }
    });
});

function calculate_total_marks(){
  $('.question_marks').blur(function(){
      var marks=parseInt(0);
      var $this = $('#total_marks strong > span');
      $('.question_marks').each(function(){
          if($(this).val())
            marks = marks + parseInt($(this).val());
        });
      $this.html(marks);
  });
}


$( 'body' ).delegate( '.submit_quiz', 'click', function(event){
    event.preventDefault();
    $('#ajaxloader').removeClass('disabled');
    if($(this).hasClass('disabled')){
      return false;
    }
     
    var $this = $(this);
    var quiz_id=$(this).attr('data-quiz');
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $('#question').addClass('quiz_submitted_fade');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'submit_quiz', 
                    start_quiz: $('#start_quiz').val(),
                    id: quiz_id
                  },
            cache: false,
            success: function (html) {
                $('#question').css('opacity',0.2);
                $this.find('i').remove();
                window.location.assign(document.URL);
                //location.reload();
            }
    });
});

// QUIZ RELATED FUCNTIONS
// START QUIZ AJAX
jQuery(document).ready( function($) {
  $('.begin_quiz').click(function(event){
      event.preventDefault();
      var $this = $(this);
      var quiz_id=$(this).attr('data-quiz');
      $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'begin_quiz', 
                      start_quiz: $('#start_quiz').val(),
                      id: quiz_id
                    },
              cache: false,
              success: function (html) {
                  $this.find('i').remove();
                  $('.content').fadeOut("fast");
                  $('.content').html(html);
                  $('.content').fadeIn("fast");
                  ajaxsubmit_comments();
                  var ques=$($.parseHTML(html)).filter("#question");
                  var q='#ques'+ques.attr('data-ques');

                  $('.quiz_timeline').find('.active').removeClass('active');
                  $(q).addClass('active');
                  $('#question').trigger('question_loaded');
                  if(ques != 'undefined'){
                    $('.quiz_timer').trigger('activate');
                  }
                  runnecessaryfunctions();
                  $('.begin_quiz').each(function(){
                      $(this).removeClass('begin_quiz');
                      $(this).addClass('submit_quiz');
                      $(this).text(vibe_course_module_strings.submit_quiz);
                  });
            }
        });
  });
});

$( 'body' ).delegate( '.show_hint', 'click', function(event){
  event.preventDefault();
  $(this).toggleClass('active');
  $('.hint').toggle(400);
});

$('.show_explaination').click(function(event){
    event.preventDefault();
    var $this = $(this);
    $this.toggleClass('active');
    $this.closest('li').find('.explaination').toggle();
});

$( 'body' ).delegate( '.quiz_question', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var quiz_id=$(this).attr('data-quiz');
    var ques_id=$(this).attr('data-qid');
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $('#ajaxloader').removeClass('disabled');
    $('#question').css('opacity',0.2);
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'quiz_question', 
                    start_quiz: $('#start_quiz').val(),
                    quiz_id: quiz_id,
                    ques_id: ques_id
                  },
            cache: false,
            success: function (html) {
                $this.find('i').remove();
                $('.content').html(html);
                $('#ajaxloader').addClass('disabled');
                $('#question').css('opacity',1);
                ajaxsubmit_comments();
                var ques=$($.parseHTML(html)).filter("#question");
                var q='#ques'+ques.attr('data-ques');
                $('.quiz_timeline').find('.active').removeClass('active');
                $(q).addClass('active');
                $('#question').trigger('question_loaded');
                $('.tip').tooltip();
                //if(ques != 'undefined')
                  //$('.quiz_timer').trigger('activate'); 
                $('audio,video').mediaelementplayer();
                //END Match question type
                //
                if($('.timeline_wrapper').height() > $('.quiz_timeline').height()){
                     $('.quiz_timeline').animate({scrollTop: $(q).position().top}, 'slow');
                }
            }
      });
});

$( 'body' ).delegate( '#question', 'question_loaded',function(){
    
  jQuery('.question_options.sort').each(function(){

    var defaultanswer='1';
    var lastindex = $('ul.question_options li').size();
    if(lastindex>1)
    for(var i=2;i<=lastindex;i++){
      defaultanswer = defaultanswer+','+i;
    }
    $('#comment').val(defaultanswer);
    $('#comment').trigger('change');
    jQuery('.question_options.sort').sortable({
      revert: true,
      cursor: 'move',
      refreshPositions: true, 
      opacity: 0.6,
      scroll:true,
      containment: 'parent',
      placeholder: 'placeholder',
      tolerance: 'pointer',
      update: function( event, ui ) {
          var order = $('.question_options.sort').sortable('toArray').toString();
          $('#comment').val(order);
          $('#comment').trigger('change');
      }
    }).disableSelection();
  });
  //Fill in the Blank Live EDIT
  $(".live-edit").liveEdit({
      afterSaveAll: function(params) {
        return false;
      }
  });

  //Match question type
  $('.question_options.match').droppable({
    drop: function( event, ui ){
      $(ui.draggable).removeAttr('style');
      $( this )
            .addClass( "ui-state-highlight" )
            .append($(ui.draggable))
    }
  });
  $('.question_options.match li').draggable({
    revert: "invalid",
    containment:'#question'
  });
  $( ".matchgrid_options li" ).droppable({
      activeClass: "ui-state-default",
      hoverClass: "ui-state-hover",
      drop: function( event, ui ){
        childCount = $(this).find('li').length;
        $(ui.draggable).removeAttr('style');
        if (childCount !=0){
            return;
        }  
        
         $( this )
            .addClass( "ui-state-highlight" )
            .append($(ui.draggable))
      }
    });
  if($('.matchgrid_options').hasClass('saved_answer')){
      var id;
      $('.matchgrid_options li').each(function(index,value){
          id = $('.matchgrid_options').attr('data-match'+index);
          $(this).append($('#'+id));
      });
  }
});



jQuery(document).ready( function($) {
 

  $('.quiz_timer').one('activate',function(){

    var qtime = parseInt($(this).attr('data-time'));

    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.timer({
      'timer': qtime,
      'width' : 200 ,
      'height' : 200 ,
      'fgColor' : vibe_course_module_strings.theme_color ,
      'bgColor' : vibe_course_module_strings.single_dark_color 
    });

    var $timer =$(this).find('.timer');

    $timer.on('change',function(){
        var countdown= $this.find('.countdown');
        var val = parseInt($timer.attr('data-timer'));
        if(val > 0){
          val--;
          $timer.attr('data-timer',val);
          var $text='';
          if(val > 60){
            $text = Math.floor(val/60) + ':' + ((parseInt(val%60) < 10)?'0'+parseInt(val%60):parseInt(val%60)) + '';
          }else{
            $text = '00:'+ ((val < 10)?'0'+val:val);
          }

          countdown.html($text);
        }else{
            countdown.html('Timeout');
            if(!$('.submit_quiz').hasClass('triggerred')){
                $('.submit_quiz').trigger('click');
                $('.submit_quiz').addClass('triggerred');
            } 

            $('.quiz_timer').trigger('end');
        }  
    });
    
  });

  $('.quiz_timer').one('deactivate',function(){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : vibe_course_module_strings.theme_color ,
        'bgColor' : vibe_course_module_strings.single_dark_color,
        'thickness': 0.2 ,
        'readonly':true 
      });
    event.stopPropagation();
  });

  $('.quiz_timer').one('end',function(event){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    var $this=$(this);
    
    $timer.knob({
        'readonly':true,
        'max': qtime,
        'width' : 200 ,
        'height' : 200 ,
        'fgColor' : vibe_course_module_strings.theme_color ,
        'bgColor' : vibe_course_module_strings.single_dark_color,
        'thickness': 0.2 ,
        'readonly':true 
      });
    event.stopPropagation();
  });
// Timer function runs after Trigger event definition
$('.quiz_timer').each(function(){
    var qtime = parseInt($(this).attr('data-time'));
    var $timer =$(this).find('.timer');
    $timer.knob({
      'readonly':true,
      'max': qtime,
      'width' : 200 ,
      'height' : 200 ,
      'fgColor' : vibe_course_module_strings.theme_color ,
      'bgColor' : vibe_course_module_strings.single_dark_color,
      'thickness': 0.2 ,
      'readonly':true 
    });
    if($(this).hasClass('start')){
      $('.quiz_timer').trigger('activate');
    }
});

jQuery('.question_options.sort').each(function(){
    var defaultanswer='1';
    var lastindex = $('ul.question_options li').size();
    if(lastindex>1)
    for(var i=2;i<=lastindex;i++){
      defaultanswer = defaultanswer+','+i;
    }
    $('#comment').val(defaultanswer);
    $('#comment').trigger('change');
    jQuery('.question_options.sort').sortable({
      revert: true,
      cursor: 'move',
      refreshPositions: true, 
      opacity: 0.6,
      scroll:true,
      containment: 'parent',
      placeholder: 'placeholder',
      tolerance: 'pointer',
      update: function( event, ui ) {
          var order = $('.question_options.sort').sortable('toArray').toString();
          $('#comment').val(order);
          $('#comment').trigger('change');
      }
    }).disableSelection();
  });
}); 

$( 'body' ).delegate( '.expand_message', 'click', function(event){
  event.preventDefault();
  $('.bulk_message').toggle('slow');
});

$( 'body' ).delegate( '.expand_add_students', 'click', function(event){
  event.preventDefault();
  $('.bulk_add_students').toggle('slow');
});

$( 'body' ).delegate( '.expand_assign_students', 'click', function(event){
  event.preventDefault();
  $('.bulk_assign_students').toggle('slow');
});

$( 'body' ).delegate( '.extend_subscription_students', 'click', function(event){
  event.preventDefault();
  $('.bulk_extend_subscription_students').toggle('slow');
});


$( 'body' ).delegate( '#send_course_message', 'click', function(event){
  event.preventDefault();
  var members=[];

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="icon-sun-stroke animated spin"></i> '+vibe_course_module_strings.sending_messages);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });
  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'send_bulk_message', 
                security: $('#buk_action').val(),
                course:$this.attr('data-course'),
                sender: $('#sender').val(),
                members: JSON.stringify(members),
                subject: $('#bulk_subject').val(),
                message: $('#bulk_message').val(),
              },
        cache: false,
        success: function (html) {
            $('#send_course_message').html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});

$( 'body' ).delegate( '#add_student_to_course', 'click', function(event){
  event.preventDefault();
  var $this = $(this);
  var defaultxt=$this.html();
  var students = $('#student_usernames').val();

  if(students.length <= 0){ 
    $('#add_student_to_course').html(vibe_course_module_strings.unable_add_students);
    setTimeout(function(){$this.html(defaultxt);}, 2000);
    return;
  }

  $this.html('<i class="icon-sun-stroke animated spin"></i>'+vibe_course_module_strings.adding_students);
  var i=0;
  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'add_bulk_students', 
                security: $('#buk_action').val(),
                course:$this.attr('data-course'),
                members: students,
              },
        cache: false,
        success: function (html) {
          if(html.length && html !== '0'){
            $('#add_student_to_course').html(vibe_course_module_strings.successfuly_added_students);
            $('ul.course_students').append(html);
          }else{
            $('#add_student_to_course').html(vibe_course_module_strings.unable_add_students);
          }
            
            setTimeout(function(){$this.html(defaultxt);}, 3000);
        }
    });    
});

$( 'body' ).delegate( '#download_stats', 'click', function(event){
  event.preventDefault();
  var $this = $(this);
  var defaultxt=$this.html();
  var i=0;
  var fields=[]; 
  $('.field:checked').each(function(){
      fields[i]=$(this).attr('id');//$(this).val();
      i++;
  });
  
  if(i==0){
    $this.html(vibe_course_module_strings.select_fields);
    setTimeout(function(){$this.html(defaultxt);}, 13000);
    return false;
  }else{
    $this.html('<i class="icon-sun-stroke animated spin"></i> '+vibe_course_module_strings.processing);
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'download_stats', 
                security: $('#stats_security').val(),
                course:$this.attr('data-course'),
                fields: JSON.stringify(fields),
                type:$('#stats_students').val()
              },
        cache: false,
        success: function (html) {
            $this.attr('href',html);
            $this.attr('id','download');
            $this.html(vibe_course_module_strings.download)
            //setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });  
  }
});

$('body').delegate('#download_mod_stats','click',function(event){
  event.preventDefault();
  var $this = $(this);
  var defaultxt=$this.html();
  var i=0;
  var fields=[]; 
  $('.field:checked').each(function(){
      fields[i]=$(this).attr('id');//$(this).val();
      i++;
  });
  
  if(i==0){
    $this.html(vibe_course_module_strings.select_fields);
    setTimeout(function(){$this.html(defaultxt);}, 13000);
    return false;
  }else{
    $this.html('<i class="icon-sun-stroke animated spin"></i> '+vibe_course_module_strings.processing);
    $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'download_mod_stats', 
                security: $('#stats_security').val(),
                type:$this.attr('data-type'),
                id:$this.attr('data-id'),
                fields: JSON.stringify(fields),
                select:$('#stats_students').val()
              },
        cache: false,
        success: function (html) {
            $this.attr('href',html);
            $this.attr('id','download');
            $this.html(vibe_course_module_strings.download)
            //setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });  
  }
});

$( 'body' ).delegate( '#assign_course_badge_certificate', 'click', function(event){
  event.preventDefault();
  var members=[]; 

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="icon-sun-stroke animated spin"></i> '+vibe_course_module_strings.processing);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });

  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'assign_badge_certificates', 
                security: $('#buk_action').val(),
                course: $this.attr('data-course'),
                members: JSON.stringify(members),
                assign_action: $('#assign_action').val(),
              },
        cache: false,
        success: function (html) {
            $this.html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});

$( 'body' ).delegate( '#extend_course_subscription', 'click', function(event){
  event.preventDefault();
  var members=[];

  var $this = $(this);
  var defaultxt=$this.html();
  $this.html('<i class="icon-sun-stroke animated spin"></i> '+vibe_course_module_strings.processing);
  var i=0;
  $('.member').each(function(){
    if($(this).is(':checked')){
      members[i]=$(this).val();
      i++;
    }
  });

  $.ajax({
        type: "POST",
        url: ajaxurl,
        data: { action: 'extend_course_subscription', 
                security: $('#buk_action').val(),
                course: $this.attr('data-course'),
                members: JSON.stringify(members),
                extend_amount: $('#extend_amount').val(),
              },
        cache: false,
        success: function (html) {
          console.log(html);
            $this.html(html);
            setTimeout(function(){$this.html(defaultxt);}, 5000);
        }
    });    
});



$( 'body' ).delegate( '#mark-complete', 'media_loaded', function(event){
  event.preventDefault();
  if($(this).hasClass('tip')){
      $(this).addClass('disabled');
  }
});

$( 'body' ).delegate( '#mark-complete', 'media_complete', function(event){ 
  event.preventDefault();
  if($(this).hasClass('tip')){
    $(this).removeClass('disabled');
    $(this).removeClass('tip');
    $(this).tooltip('destroy');
    jQuery('.tip').tooltip();
  }  
});


$( 'body' ).delegate( '#mark-complete', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }

    var $this = $(this);
    var unit_id=$(this).attr('data-unit');
    $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
    $('body').find('.course_progressbar').removeClass('increment_complete');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'complete_unit', 
                    security: $('#hash').val(),
                    course_id: $('#course_id').val(),
                    id: unit_id
                  },
            cache: false,
            success: function (html) {
              console.log(html);
                $this.find('i').remove();
                $this.html('<i class="icon-check"></i>');
                $('.course_timeline').find('.active').addClass('done');
                $('body').find('.course_progressbar').trigger('increment');
                $('#mark-complete').addClass('disabled');
                
                var cookie_id = 'course_progress'+$('#course_id').val();
                var value= $('.course_progressbar').attr('data-value');
                $.cookie(cookie_id,value, { expires: 1 ,path: '/'});

                if(html.length > 0){
                    $('#next_unit').removeClass('hide');
                    $('#next_unit').attr('data-unit',html);  
                    $('#unit'+html).find('a').addClass('unit');
                    $('#unit'+html).find('a').attr('data-unit',html);
                }
                if(typeof unit != 'undefined')
                  $('.unit_timer').trigger('finish');
            }
    });
});


$('.course_progressbar').on('increment',function(event){

  if($(this).hasClass('increment_complete')){
    event.stopPropagation();
    return false;
  }else{
    var iunit = parseInt($(this).attr('data-increase-unit'));
    var per = parseInt($(this).attr('data-value'));
    newper = iunit + per;
    $(this).find('.bar').css('width',newper+'%');
    $(this).find('.bar span').html(newper + '%');
    $(this).addClass('increment_complete');
    $(this).attr('data-value',newper);
  }
  event.stopPropagation();
  return false;
  
});



jQuery(document).ready(function($){
  $('.showhide_indetails').click(function(event){
    event.preventDefault();
    $(this).find('i').toggleClass('icon-minus');
    $(this).parent().find('.in_details').toggle();
  });


$('.ajax-certificate').each(function(){
    $(this).magnificPopup({
          type: 'ajax',
          fixedContentPos: true,
          alignTop:true,
          preloader: false,
          midClick: true,
          removalDelay: 300,
          showCloseBtn:false,
          mainClass: 'mfp-with-zoom',
          callbacks: {
             parseAjax: function( mfpResponse ) {
              mfpResponse.data = $(mfpResponse.data).find('#certificate');
            },
            ajaxContentAdded: function() {
              html2canvas($('#certificate'), {
                  onrendered: function(canvas) {
                      var data = canvas.toDataURL();
                      $('#certificate .certificate_content').html('<img src="'+data+'" />');
                      $('#certificate').trigger('generate_certificate');
                  }
              });
            }
          }
      });
});

$('.ajax-badge').each(function(){
  var $this=$(this);
  var img=$this.find('img');
  $(this).magnificPopup({
        items: {
            src: '<div class="badge-popup"><img src="'+img.attr('src')+'" /><h3>'+$this.attr('title')+'</h3><strong>'+vibe_course_module_strings.for_course+' '+$this.attr('data-course')+'</strong></div>',
            type: 'inline'
        },
        fixedContentPos: false,
        alignTop:false,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        showCloseBtn:false,
        mainClass: 'mfp-with-zoom center-aligned'
    });
});

$( 'body' ).delegate( '.print_unit', 'click', function(event){
    $('.unit_content').print();
});

$( 'body' ).delegate( '.printthis', 'click', function(event){
    $(this).parent().print();
});

$( 'body' ).delegate( '#certificate', 'generate_certificate', function(event){
    $(this).addClass('certificate_generated');
});

$( 'body' ).delegate( '.certificate_print', 'click', function(event){
    event.preventDefault();
    $(this).parent().parent().print();
});

$('.widget_carousel').flexslider({
  animation: "slide",
  controlNav: false,
  directionNav: true,
  animationLoop: true,
  slideshow: false,
  prevText: "<i class='icon-arrow-1-left'></i>",
  nextText: "<i class='icon-arrow-1-right'></i>",
});

  /*=== Quick tags ===*/
  $( 'body' ).delegate( '.unit-page-links a', 'click', function(event){
        if($('body').hasClass('single-unit'))
          return;

        event.preventDefault();
        
        var $this=$(this);
        $this.prepend('<i class="icon-sun-stroke animated spin"></i>');
        $( ".main_unit_content" ).load( $this.attr('href') +" .single_unit_content" );
        runnecessaryfunctions();
        $('body').trigger('unit_loaded');
        $this.find('i').remove();
        $( ".main_unit_content" ).trigger('unit_reload');
    });
  
   $( 'body' ).delegate('.pricing_course', 'click', function(event){
    $(this).toggleClass('active');
  });
   $( 'body' ).delegate( '.pricing_course li', 'click', function(event){
    var parent = $(this).parent();
    var $this = $(this);
    parent.find('.active').removeClass('active');
    $this.addClass('active');
    if($('.course_button').length){
      var value = $(this).attr('data-value');
      $('.course_button').attr('href',value);
    }
  });
}); 



// Course Unit Traverse
$( 'body' ).delegate( '.unit', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }
    
    var $this = $(this);
    var unit_id=$(this).attr('data-unit');
    if($this.prev().is('span')){
      $this.prev().addClass('loading');
    }else{
      $this.prepend('<i class="icon-sun-stroke animated spin"></i>');  
    }
    
    $('#ajaxloader').removeClass('disabled');
    $('.unit_content').addClass("loading");
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'unit_traverse', 
                    security: $('#hash').val(),
                    course_id: $('#course_id').val(),
                    id: unit_id
                  },
            cache: false,
            success: function (html) {
                 $('body,html').animate({
                    scrollTop: 0
                  }, 1200);
                if($this.prev().is('span')){
                  $this.prev().removeClass('loading');
                }else{
                  $this.find('i').remove();
                } 
                $('#ajaxloader').addClass('disabled');
                $('.unit_content').removeClass("loading");
                $('.unit_content').html(html);

                var unit=$($.parseHTML(html)).filter("#unit");
                var u='#unit'+unit.attr('data-unit');
                $('.course_timeline').find('.active').removeClass('active');
                $(u).addClass('active');

                $('.unit_content').trigger('unit_traverse');

                $('audio,video').mediaelementplayer({
                    success: function(media,node,player) { 
                      $('#mark-complete').trigger('media_loaded');
                      $('.mejs-container').each(function(){
                        $(this).addClass('mejs-mejskin');
                      });
                      media.addEventListener('ended', function (e) {
                        $('#mark-complete').trigger('media_complete');
                      });
                    }
                });
                runnecessaryfunctions();
                /*=== UNIT COMMENTS ======*/
                if($('.unit_wrap').hasClass('enable_comments')){ 
                  $('.unit_content').trigger('load_comments');
                }

                if(typeof unit != 'undefined')
                  $('.unit_timer').trigger('activate');
            }
    });
});


/*==============================================================*/
/*======================= UNIT COMMENTS ========================*/
/*==============================================================*/

$( 'body' ).delegate( '.unit_content', 'load_comments', function(event){ 
        if($(this).find('.main_unit_content').hasClass('stop_notes'))
          return;

       var unit_id=$('#unit').attr('data-unit');
      $('.unit_content p').each(function(index){
            $(this).attr('data-section-id',index);
            $(this).append('<span id="c'+index+'" class="side_comment">+</span>');
        });
        unitComments();
         $.ajax({
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: { action: 'get_unit_comment_count', 
                    security: $('#hash').val(),
                    unit_id: unit_id,
                  },
            cache: false,
            success: function (response) {
              $.each(response, function(idx, obj) {
                $('#'+obj.id).text(obj.count);
              });
              }
            });
        $('.side_comment').on('click',function(){ 
          if($(this).hasClass('active'))
            return false;
          var $this = $(this);
          var section = $('.side_comment.active').attr('id');
          $('.side_comment').removeClass('active');
          $('.side_comments .main_comments>li:not(".hide")').remove();
          $(this).addClass('active');
          var id = $(this).attr('id');
          $('.add-comment').fadeIn();
          $('.add-comment').next().fadeOut();
          var check = $(this).text();
          var href='#';
          $('.side_comments .main_comments').find('.loaded').remove();
          if( jQuery.isNumeric(check)){
            var comment_html ='';
            var cookie_id='unit_comments'+unit_id;
            //var unit_comments = $.cookie(cookie_id);
            var unit_comments = sessionStorage.getItem(cookie_id);
            //CHeck cookie
            if (unit_comments !== null){ console.log('no-ajax');
               unit_comments = JSON.parse(unit_comments);
               $.each(unit_comments, function(idxx, objStr) { 
               $.each(objStr, function(idx, obj){ 
                  if(id == idx){ 
                    comment_html += '<li class="loaded"><div class="'+obj.type+'" data-id="'+obj.ID+'"><img src="'+obj.author.img+'"><a href="'+obj.author.link+'" class="unit_comment_author">'+obj.author.name+'</a><div class="unit_comment_content">'+obj.content+'</div><ul class="actions" data-pid="'+$this.attr('id')+'">';
                    
                      jQuery.each(obj.controls, function(i,o) { 
                        if(o>1){
                         jQuery('.side_comments li.hide').find('.'+i).addClass('meta_info').attr('data-meta',o);
                        }
                        var control = jQuery('.side_comments li.hide').find('.'+i).parent()[0].outerHTML;
                        if(o>1){
                          jQuery('.side_comments li.hide').find('.'+i).removeClass('meta_info').removeAttr('data-meta');
                        }
                        comment_html +=control;
                      });
                    
                    comment_html +='</ul></div></li>';
                    href=$(comment_html).find('.popup_unit_comment').removeClass('meta_info').attr('data-href');
                    href +='?unit_id='+unit_id+'&section='+idx;
                  } 
                });
               });
               $('.side_comments .main_comments').append(comment_html);
               $('.side_comments .main_comments .popup_unit_comment').attr('href',href);
               jQuery('.tip').tooltip();
               $('.popup_unit_comment').magnificPopup({
                    type: 'ajax',
                    alignTop: true,
                    fixedContentPos: true,
                    fixedBgPos: true,
                    overflowY: 'auto',
                    closeBtnInside: true,
                    preloader: false,
                    midClick: true,
                    removalDelay: 300,
                    mainClass: 'my-mfp-zoom-in',
                    callbacks: {
                             parseAjax: function( mfpResponse ) {
                              mfpResponse.data = $(mfpResponse.data).find('.content');
                            }
                          }
                });
            }else{ //ajax request and grab the json from ajax
                section =$('.side_comment.active').attr('id');
                console.log('ajax');
                $.ajax({
                  type: "POST",
                  dataType: "json",
                  url: ajaxurl,
                  data: { action: 'unit_section_comments', 
                          security: $('#hash').val(),
                          unit_id: unit_id,
                          section: section,
                          num:$('.side_comment').length
                        },
                  cache: false,
                  success: function (jsonStr){
                     var cookie_value =JSON.stringify(jsonStr);
                     sessionStorage.setItem(cookie_id,cookie_value);
                      $.each(jsonStr, function(idxx, objStr){ 
                         $.each(objStr, function(idx, obj){ 
                          if(id == idx){
                            comment_html += '<li class="loaded"><div class="'+obj.type+' user'+obj.author.user_id+'" data-id="'+obj.ID+'"><img src="'+obj.author.img+'"><a href="'+obj.author.link+'" class="unit_comment_author">'+obj.author.name+'</a><div class="unit_comment_content">'+obj.content+'</div><ul class="actions" data-pid="'+$this.attr('id')+'">';

                              jQuery.each(obj.controls, function(i,o) { 
                                if(o>1){
                                 jQuery('.side_comments li.hide').find('.'+i).addClass('meta_info').attr('data-meta',o);
                                }
                                var control = jQuery('.side_comments li.hide').find('.'+i).parent()[0].outerHTML;
                                if(o>1){
                                  jQuery('.side_comments li.hide').find('.'+i).removeClass('meta_info').removeAttr('data-meta');
                                }
                                comment_html +=control;
                              });

                            comment_html +='</ul></div></li>';
                            var href=$(comment_html).find('.popup_unit_comment').attr('href');
                            href +='?unit_id='+unit_id+'&section='+idx;
                            $(comment_html).find('.popup_unit_comment').attr('href',href);
                          }
                        });
                      });   
                      $('.side_comments .main_comments').append(comment_html);
                      jQuery('.tip').tooltip();
                      $('.popup_unit_comment').magnificPopup({
                          type: 'ajax',
                          alignTop: true,
                          fixedContentPos: true,
                          fixedBgPos: true,
                          overflowY: 'auto',
                          closeBtnInside: true,
                          preloader: false,
                          midClick: true,
                          removalDelay: 300,
                          mainClass: 'my-mfp-zoom-in',
                          callbacks: {
                                   parseAjax: function( mfpResponse ) { console.log(mfpResponse);
                                    mfpResponse.data = $(mfpResponse.data).find('.content');
                                  }
                                }
                      });
                  }
              });
            } //end else 
          } // end if numeric check
          var all_href=$('#all_comments_link').attr('data-href');
          all_href +='?unit_id='+unit_id+'&section='+$('.side_comment.active').attr('id');
          $('#all_comments_link').attr('href',all_href);

          var top = $(this).offset().top; console.log($(this).attr('id'));
          var content_top=$('#unit_content').offset().top; 
          var height = $('.side_comments').height();
          var limit = $('.unit_prevnext').offset().top;
          if((top+height) > limit){
            top = limit - content_top - height;
          }else{
            top = top - content_top;
          }
          if(top >0){
            $('.side_comments').css('top',top+'px');
            $('.side_comments').removeClass('scroll');
          }else{
            $('.side_comments').addClass('scroll');
            var h=$('.main_unit_content').height();
            $('.side_comments').css('height',h+'px');
          }
        }); 
      /*=== END UNIT COMMENTS ======*/
  });
/* ===== UNIT COMMENTS =====*/
jQuery(document).ready(function($){

  

  $('.add-comment').on('click',function(){
      $(this).fadeOut(0);
      $(this).next('.comment-form').fadeIn(100);
  });

  $('.new_side_comment').on('click',function(){ 
    if(!$(this).hasClass('cleared')){
      $(this).html('');$(this).addClass('cleared');
      $(this).parent().parent().addClass('active');
      $(this).parent().parent().parent().find('.add-comment').addClass('deactive');
    }
  });

  $('.remove_side_comment').on('click',function(){
    $(this).closest('.side_comments').find('.add-comment').fadeIn(100);
    $(this).closest('.comment-form').fadeOut();
    $('.new_side_comment').removeClass('cleared');
    $('.new_side_comment').text(vibe_course_module_strings.add_comment);
  });
});

$( 'body' ).delegate( '.public_unit_comment', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var id =$this.closest('li.loaded>div').attr('data-id');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'public_user_comment', 
                    security: $('#hash').val(),
                    id: id
                  },
            cache: false,
            success: function (html) {
                $this.removeClass('public_unit_comment');
                $this.addClass('private_unit_comment');
                $this.find('i').removeClass().addClass('icon-fontawesome-webfont-4');
                $this.attr('data-original-title',vibe_course_module_strings.private_comment);
                var unit_id = $('#unit').attr('data-unit');
                var cookie_id='unit_comments'+unit_id;
                sessionStorage.removeItem(cookie_id);
            }
    });
});
$( 'body' ).delegate( '.private_unit_comment', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var id =$this.closest('li.loaded>div').attr('data-id');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'private_user_comment', 
                    security: $('#hash').val(),
                    id: id
                  },
            cache: false,
            success: function (html) {
                $this.removeClass('private_unit_comment');
                $this.addClass('public_unit_comment');
                $this.find('i').removeClass().addClass('icon-fontawesome-webfont-3');
                $this.attr('data-original-title',vibe_course_module_strings.public_comment);
                var unit_id = $('#unit').attr('data-unit');
                var cookie_id='unit_comments'+unit_id;
                sessionStorage.removeItem(cookie_id);
            }
    });
});
$( 'body' ).delegate( '.edit_unit_comment', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var content = $this.parent().parent().parent();
    var form = $('.comment-form').first().clone();
    var img = content.find('img').clone();
    var unit_comment_author = content.find('.unit_comment_author').clone();
    var id = content.attr('data-id');
    form.find('img').replaceWith(function(){return img;});
    form.find('span').replaceWith(function(){return unit_comment_author;});
    var new_content = content.find('.unit_comment_content');
    form.find('.new_side_comment').html(new_content.html());
    //console.log(id+'#');
    form.find('.post_unit_comment').removeClass().addClass('edit_form_unit_comment').attr('data-id',id);
    form.find('.remove_side_comment').removeClass().addClass('remove_form_edit_unit_comment');
    content.parent().append(form);    
    content.hide();
    content.parent().find('.comment-form').show();
});

$( 'body' ).delegate( '.remove_form_edit_unit_comment', 'click', function(event){
   $(this).parent().parent().parent().parent().find('.note,.public').show();
   $(this).closest('.comment-form').remove();
   $('.new_side_comment').removeClass('cleared');
});
$( 'body' ).delegate( '.reply_unit_comment', 'click', function(event){
    event.preventDefault();
    var parent_li = $(this).parent().parent().parent().parent();
    if($(this).hasClass('meta_info')){  
      var id =$(this).attr('data-meta');
      $.ajax({
              type: "POST",
              url: ajaxurl,
              data: { action: 'get_user_reply', 
                      security: $('#hash').val(),
                      id: id
                    },
              cache: false,
              success: function (html) {
                if(!jQuery.isNumeric(html)){
                  parent_li.after(html);
                }
              }
      });
    }else{
      $('.add-comment').trigger('click');
      $('.comment-form').addClass('creply');
      $('.comment-form').attr('data-cid',$(this).closest('.actions').parent().attr('data-id'));
    }
});

$( 'body' ).delegate( '.instructor_reply_unit_comment', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('call'))
      return false;

    var $ithis=$(this);
    var message = $ithis.parent().parent().parent().find('.unit_comment_content').html();
    var unit_id =$('#unit').attr('data-unit');
    //console.log(unit_id);
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'instructor_reply_user_comment', 
                    security: $('#hash').val(),
                    message:message,
                    id: unit_id,
                    section:$('.side_comment.active').attr('id')
                  },
            cache: false,
            success: function (html) {
              console.log(html);
              $ithis.addClass('call');
            }
    });
});
$( 'body' ).delegate( '.edit_form_unit_comment', 'click', function(event){
    event.preventDefault();
    var $new_this = $(this);
    var id =$new_this.attr('data-id');
    var new_content = $('.new_side_comment').html();
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'edit_user_comment', 
                    security: $('#hash').val(),
                    content:new_content,
                    id: id
                  },
            cache: false,
            success: function (html) {
              var unit_id = $('#unit').attr('data-unit');
              var cookie_id='unit_comments'+unit_id;
              sessionStorage.removeItem(cookie_id);
              var new_parent =$new_this.closest('.comment-form').prev().parent();
              new_parent.find('.unit_comment_content').html(new_content);
              $new_this.closest('.comment-form').remove();
              new_parent.find('.note,.public').show();
            }
    });
});

$( 'body' ).delegate( '.remove_unit_comment', 'click', function(event){
    event.preventDefault();
    var $this = $(this);
    var id =$this.parent().parent().closest('li>div').attr('data-id');
    $this.addClass('animated spin');
    $.confirm({
        text: vibe_course_module_strings.remove_comment,
        confirm: function() {
           $.ajax({
                  type: "POST",
                  url: ajaxurl,
                  data: { action: 'remove_user_comment', 
                          security: $('#hash').val(),
                          id: id
                        },
                  cache: false,
                  success: function (html) {
                      $this.removeClass('animated');
                      $this.removeClass('spin');
                      var cid = $this.closest('.actions').attr('data-pid');
                      var count=parseInt($('#'+cid).text());
                      count--;
                      $('#'+cid).text(count);
                      $this.closest('li.loaded').fadeOut(200,function(){$(this).remove();});
                      var unit_id = $('#unit').attr('data-unit');
                      var cookie_id='unit_comments'+unit_id;
                      $('.new_side_comment').removeClass('cleared');
                      sessionStorage.removeItem(cookie_id);
                  }
          });
        },
        cancel: function() {
            $this.removeClass('animated');
            $this.removeClass('spin');
        },
        confirmButton: vibe_course_module_strings.remove_comment_button,
        cancelButton: vibe_course_module_strings.cancel
    });
})
$( 'body' ).delegate( '.post_unit_comment', 'click', function(event){
    event.preventDefault();
    if($(this).hasClass('disabled')){
      return false;
    }
    var reply =0;
    if($(this).closest('.comment-form').hasClass('creply')){
      reply = $(this).closest('.comment-form').attr('data-cid');
    }

    var $this = $(this);
    var section = $('.side_comment.active').attr('id');
    var unit_id = $('#unit').attr('data-unit');
    var list =$this.closest('.side_comments').find('ul.main_comments');
    var list_html = list.find('li.hide').clone();
    var content = $(this).closest('.comment-form').find('.new_side_comment').html();
    var cookie_id='unit_comments'+unit_id;

    $this.addClass('disabled');
    $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'post_unit_comment', 
                    security: $('#hash').val(),
                    course_id: $('#course_id').val(),
                    unit_id: unit_id,
                    content:content,
                    section:section,
                    reply:reply
                  },
            cache: false,
            success: function (id) {
              $this.removeClass('disabled');
               if( jQuery.isNumeric(id)){
                 var cookie_id='unit_comments'+unit_id;
                 var unit_comments = $.cookie(cookie_id);
                 var comment={
                  section:{
                    'content':content,
                    'type':'note',
                    'author':{
                      'img':list_html.find('img').attr('src'),
                      'name':list_html.find('.unit_comment_author').text(),                    
                      'link':list_html.find('.unit_comment_author').attr('href'),
                    },
                    'controls':{
                      'edit_unit_comment':1,
                      'public_unit_comment':1,
                      'instructor_reply_unit_comment':1,
                      'popup_unit_comment':1,
                      'remove_unit_comment':1
                    }
                  }
                };
                 sessionStorage.removeItem(cookie_id);
                 list_html.find('.unit_comment_content').html(content);
                 list_html.removeClass();
                 list_html.find('.actions .private_unit_comment').parent().remove();
                 list.append(list_html);
                 var href=$(list_html).find('.popup_unit_comment').attr('data-href');
                 href +='?unit_id='+unit_id+'&section='+$('.side_comment.active').attr('id');
                 $(list_html).find('.popup_unit_comment').attr('href',href);
                 jQuery('.tip').tooltip();
                 var count=$('#'+section).text();
                 if( jQuery.isNumeric(count)){
                    count=parseInt(count)+1;
                 }else{
                   count=1;
                 }
                 $('.new_side_comment').removeClass('cleared');
                 $('#'+section).text(count);
                 $('.add-comment').fadeIn();
                 $('.comment-form').removeClass('active').fadeOut();
                 $('.new_side_comment').text(vibe_course_module_strings.add_comment);
                 $('.popup_unit_comment').magnificPopup({
                    type: 'ajax',
                    alignTop: true,
                    fixedContentPos: true,
                    fixedBgPos: true,
                    overflowY: 'auto',
                    closeBtnInside: true,
                    preloader: false,
                    midClick: true,
                    removalDelay: 300,
                    mainClass: 'my-mfp-zoom-in',
                    callbacks: {
                             parseAjax: function( mfpResponse ) {
                              mfpResponse.data = $(mfpResponse.data).find('.content');
                            }
                          }
                });
               }else{
                $this.closest('.comment-form').append('<div class="error">'+id+'</div>');
               }
            }
    });
});

$( 'body' ).delegate( '.note-tabs li', 'click', function(event){
  event.preventDefault();
  $(this).parent().find('.selected').removeClass('selected');
  $(this).addClass('selected');
   var action = $(this).attr('id');
   $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: action, 
                    security: $('#hash').val(),
                    unit_id:$.urlParam('unit_id'),
                    section:$.urlParam('section')
                  },
            cache: false,
            success: function (html) {
              $('.content').html(html);
              $(".live-edit").liveEdit({
                  afterSaveAll: function(params) {
                    return false;
                  }
              });
            }
          });
});
$( 'body' ).delegate( '#load_more_notes', 'click', function(event){
   var json = $('#notes_query').html();
   $.ajax({
            type: "POST",
            url: ajaxurl,
            data: { action: 'load_more_notes', 
                    security: $('#hash').val(),
                    json:json
                  },
            cache: false,
            success: function (html) {
              if( jQuery.isNumeric(html)){
                $('#load_more_notes').hide();
              }else{
                var newjson = $(html).filter('#new_notes_query').html();
                $('#notes_query').html(newjson);
                $('#notes_discussions .notes_list').append(html);
                $('#new_notes_query').remove();
              }
              $(".live-edit").liveEdit({
                  afterSaveAll: function(params) {
                    return false;
                  }
              });
            }
          });
});
jQuery(document).ready(function(){
   jQuery('.course_curriculum.accordion .course_section').click(function(event){
           jQuery(this).toggleClass('show');
           jQuery(this).nextUntil('.course_section').toggleClass('show');
   });
   jQuery('.course_timeline.accordion .section').on('click',function(event){
           jQuery(this).toggleClass('show');
           jQuery(this).nextUntil('.section').toggleClass('show');
   });
   jQuery('.course_timeline.accordion').each(function(){ 
      var $this = $(this);
      $this.find('.unit_line.active').prevAll('.section').trigger('click');
   });
   $('.unit_content').on('unit_traverse',function(){ 
      var section =$('.course_timeline.accordion').find('.unit_line.active').prev('.section');
      jQuery('.course_timeline.accordion .section.show').trigger('click'); // Close the open one
      if(!section.hasClass('show'))
        section.trigger('click');
    });
   $('.retake_submit').click(function(){
      $(this).parent().submit();
   });
});
})(jQuery);
