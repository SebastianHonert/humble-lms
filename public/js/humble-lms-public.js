jQuery(document).ready(function($) {
  'use strict'

  // Show/hide loading layer/spinner
  function loadingLayer(show = false) {
    let loadingLayer = $('.humble-lms-loading-layer')
    if (show) {
      loadingLayer.css('display', 'flex')
    } else {
      loadingLayer.hide(0)
    }
  }

  // This dynamic form is used for redirecting purposes by diferent AJAX functions
  function redirectForm( response ) {
    if (!response.redirect_url) {
      return
    }

    let form = $('<form style="display:none" action="' + response.redirect_url + '" method="post">' +
        '<input type="hidden" name="course_id" value="' + response.course_id + '" />' +
        '<input type="hidden" name="lesson_id" value="' + response.lesson_id + '" />' +
        '<input type="hidden" name="completed" value="' + response.completed + '" />' +
        '</form>')
    $('body').append(form)
    form.submit()
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
        console.error(errorThrown)
      },
      success: function(response, textStatus, XMLHttpRequest) {
        redirectForm(response)
      },
      complete: function(reply, textStatus) {
        // ...
      }
    })
  })

  // Mark lesson complete and continue to the next lesson
  $(document).on('submit', '#humble-lms-mark-complete', function (e) {
    e.preventDefault()

    loadingLayer(true)

    // Form contains quiz
    let form = $('#humble-lms-mark-complete')
    let hasQuiz = form.hasClass('humble-lms-has-quiz')
    let quizIds = hasQuiz ? $('#quiz-ids').val().split(',') : []
        quizIds = quizIds.map(function (id) { 
          return parseInt(id) 
        })
    let evaluation = Humble_LMS_Quiz.evaluate()
        evaluation['quizIds'] = quizIds ? quizIds : []

    setTimeout( function() {
      $.ajax({
        url: humble_lms.ajax_url,
        type: 'POST',
        data: {
          action: 'mark_lesson_complete',
          courseId: $('#course-id').val(),
          lessonId: $('#lesson-id').val(),
          lessonCompleted: $(this).data('lesson-completed'),
          nonce: humble_lms.nonce,
          markComplete: true,
          hasQuiz: hasQuiz,
          evaluation: JSON.stringify(evaluation)
        },
        dataType: 'json',
        error: function(MLHttpRequest, textStatus, errorThrown) {
          loadingLayer(false)
        },
        success: function(response, textStatus, XMLHttpRequest) {
          redirectForm(response)
          loadingLayer(false)
        },
        complete: function(reply, textStatus) {
          loadingLayer(false)
        }
      })
    }, 250)
  })

  // Award messages
  function closeAwardMessage () {
    let messages = $('.humble-lms-award-message-inner')

    if (messages.length && messages.length === 1) {
      $('.humble-lms-award-message').fadeOut(500)
      return
    }
    
    messages.eq(0).animate({
      opacity: 0,
    }, 500, function () {
      $(this).remove()
      messages = $('.humble-lms-award-message-inner')
      if (messages.length) {
        $('.humble-lms-award-message-inner').eq(0).animate({
          opacity: 1,
        }, 500)
      }
    })
  }

  $('.humble-lms-award-message-close').on('click touch', function () {
    closeAwardMessage()
  })

  $('.humble-lms-award-message-inner').on('click touch', function () {
    closeAwardMessage()
  })

  $('.humble-lms-award-message').fadeIn(500)
  $('.humble-lms-award-message-inner').first().animate({
    opacity: 1,
  }, 500)

  // Keyboard interaction
  $(document).keyup( function (e) {
    if (e.key === "Escape") { // escape key maps to keycode `27`
      closeAwardMessage()
    }
  })

})
