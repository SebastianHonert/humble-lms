jQuery(document).ready(function($) {
  'use strict'

  /**
   * Show/hide loading layer/spinner
   * 
   * @since   0.0.1
   */
  function loadingLayer(show = false) {
    let loadingLayer = $('.humble-lms-loading-layer')
    if (show) {
      loadingLayer.css('display', 'flex')
    } else {
      loadingLayer.hide(0)
    }
  }

  /**
   * Redirect after lesson is marked as complete.
   * 
   * @since   0.0.1
   */
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

  /**
   * Open another lesson and pass context parameters.
   * 
   * @todo    Provide another AJAX function for simple lesson switching.
   * @since   0.0.1
   */
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
        // Taking a nap...
      }
    })
  })

  /**
   * Evaluate a quiz.
   * Humble_LMS_Quiz module generated in public/js/humble-lms-quiz.js
   * 
   * @since   0.0.1
   */
  $(document).on('submit', '#humble-lms-evaluate-quiz', function (e) {
    e.preventDefault()

    let quizForm = $('#humble-lms-evaluate-quiz')
    let quizIds = quizForm.find($('input[name="quiz-ids"]')).val().split(',')
        quizIds = quizIds.map(function (id) { 
          return parseInt(id)
        })

    let evaluation = Humble_LMS_Quiz.evaluate(quizIds)

    $('.humble-lms-message-quiz').hide(0)
    $('.humble-lms-quiz-score').text(evaluation.grade + ' %')
    $('.humble-lms-quiz-passing-grade').text(evaluation.passing_grade + ' %')

    if (evaluation.completed || evaluation.tryAgain === 1) {
      loadingLayer(true)

      $.ajax({
        url: humble_lms.ajax_url,
        type: 'POST',
        data: {
          action: 'evaluate_quiz',
          evaluation: evaluation,
          nonce: humble_lms.nonce
        },
        dataType: 'json',
        error: function(MLHttpRequest, textStatus, errorThrown) {
          console.log(errorThrown)
          loadingLayer(false)
        },
        success: function(response, textStatus, XMLHttpRequest) {
          if (evaluation.tryAgain === 0) {
            $('.humble-lms-quiz-message-image').html('<i class="ti-face-smile"></i>')
            $('.humble-lms-quiz-message').removeClass('humble-lms-quiz-message--failed').addClass('humble-lms-quiz-message--success')
            $('.humble-lms-quiz-message, .humble-lms-message-quiz--completed').fadeIn(500)
            $('#humble-lms-mark-complete').fadeIn(500)
            loadingLayer(false)
          } else {
            location.reload()
          }
        },
        complete: function(reply, textStatus) {
          loadingLayer(false)
        }
      })
    } else {
      $('.humble-lms-quiz-message-image').html('<i class="ti-face-sad"></i>')
      $('.humble-lms-quiz-message').removeClass('humble-lms-quiz-message--success').addClass('humble-lms-quiz-message--failed')
      $('.humble-lms-quiz-message, .humble-lms-message-quiz--failed').fadeIn(500)
    }
  })

  $('.humble-lms-quiz-message').on('click', function() {
    $(this).fadeOut(500)
  })

  /**
   * Mark the current lesson as complete.
   *
   * @since   0.0.1
   */
  $(document).on('submit', '#humble-lms-mark-complete', function (e) {
    e.preventDefault()
    loadingLayer(true)
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
          markComplete: true
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

  /**
   * Show messages on opening a lesson.
   *
   * @since   0.0.1
   */
  function showMessages() {
    let messageContainer = $('.humble-lms-award-message')
    let messages = $('.humble-lms-award-message-inner')

    function closeMessages () {
      if (messages.length && messages.length === 1) {
        messageContainer.fadeOut(500, function() {
          messages.remove()
        })
        return
      }
      
      messages.eq(0).animate({
        opacity: 0,
      }, 500, function () {
        $(this).remove()
        messages = $('.humble-lms-award-message-inner')
        if (messages.length) {
          messages.eq(0).animate({
            opacity: 1,
          }, 500)
        }
      })
    }

    $('.humble-lms-award-message-close').on('click touch', function () {
      closeMessages()
    })

    $('.humble-lms-award-message-inner').on('click touch', function () {
      closeMessages()
    })

    messageContainer.fadeIn(500)
    messages.first().animate({
      opacity: 1,
    }, 500)

    $(document).keyup( function (e) {
      if (e.key === 'Escape') {
        messageContainer.fadeOut(500, function() {
          messages.remove()
        })
      }
    })
  }

  showMessages()

})
