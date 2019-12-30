jQuery(document).ready(function($) {
  'use strict'

  // This dynamic form is used for redirecting purposes by diferent AJAX functions
  function redirectForm( response ) {
    if (!response.redirect_url) {
      return
    }

    let form = $('<form style="display:none" action="' + response.redirect_url + '" method="post">' +
        '<input type="hidden" name="course_id" value="' + response.course_id + '" />' +
        '<input type="hidden" name="lesson_id" value="' + response.lesson_id + '" />' +
        '<input type="hidden" name="completed" value="' + response.completed + '" />' +
        '</form>');
    $('body').append(form);
    form.submit();
  }

  // Mark lesson complete and continue to the next lesson
  $(document).on('click', '.humble-lms-open-lesson', function (e) {

    $.ajax({
      url: humble_lms.ajax_url,
      type: 'POST',
      data: {
        action: 'mark_lesson_complete',
        courseId: $(this).data('course-id'),
        lessonId:  $(this).data('lesson-id'),
        nonce: humble_lms.nonce,
        markComplete: false
      },
      dataType: 'json',
      error: function(MLHttpRequest, textStatus, errorThrown) {
        console.error(errorThrown);
      },
      success: function(response, textStatus, XMLHttpRequest) {
        // console.log(response)
        redirectForm(response)
      },
      complete: function(reply, textStatus) {
        console.log(textStatus)
      }
    })
  })

  // Mark lesson complete and continue to the next lesson
  $(document).on('submit', '#humble-lms-mark-complete', function (e) {
    e.preventDefault();

    $.ajax({
      url: humble_lms.ajax_url,
      type: 'POST',
      data: {
        action: 'mark_lesson_complete',
        courseId: $('#course-id').val(),
        lessonId: $('#lesson-id').val(),
        lessonCompleted: $(this).data('lesson-completed'),
        nonce: humble_lms.nonce,
        markComplete: true
      },
      dataType: 'json',
      error: function(MLHttpRequest, textStatus, errorThrown) {
        console.error(errorThrown);
      },
      success: function(response, textStatus, XMLHttpRequest) {
        console.log(response)
        redirectForm(response)
      },
      complete: function(reply, textStatus) {
        console.log(textStatus)
      }
    })
  })

  // Award messages
  $('.humble-lms-award-message-close').on('click touch', function() {
    $('.humble-lms-award-message').fadeOut(500)
  })
  
})
