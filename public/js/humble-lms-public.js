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
      loadingLayer(false)
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
    loadingLayer(true)
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
        loadingLayer(false)
        console.error(errorThrown)
      },
      success: function(response, textStatus, XMLHttpRequest) {
        redirectForm(response)
      }
    })
  })

  /**
   * Select track/course archive category.
   *
   * @since   0.0.1
   */
  $('select[name=category]').on('change', function() {
    $('form#humble_lms_archive_select_category').submit()
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
            window.location = window.location.href
          }
        },
        complete: function(reply, textStatus) {
          // ... 
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
        },
        complete: function(reply, textStatus) {
          // ...
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

  /**
   * Reset user progress.
   *
   * @since   0.0.1
   */
  $('#humble-lms-reset-user-progress').on('click', function(e) {
    e.preventDefault()
    let userId = $(this).data('user-id')

    if (confirm(humble_lms.confirmResetUserProgress)) {
      loadingLayer(true)
      setTimeout( function() {
        $.ajax({
          url: humble_lms.ajax_url,
          type: 'POST',
          data: {
            action: 'reset_user_progress',
            userId: userId,
            nonce: humble_lms.nonce
          },
          dataType: 'json',
          error: function(MLHttpRequest, textStatus, errorThrown) {
            loadingLayer(false)
          },
          success: function(response, textStatus, XMLHttpRequest) {
            let url = window.location.href  
            if (url.indexOf('?') > -1) {
              url += '&progress=reset'
            } else {
              url += '?progress=reset'
            }
            window.location.href = url
            loadingLayer(false)
          },
          complete: function(reply, textStatus) {
            loadingLayer(false)
          }
        })
      }, 250)
    }
  })

  /**
   * Render PayPal buttons (checkout/memberships).
   * 
   * @since   0.0.1
   */
  const paypalButtonsMembership = (function() {
    $('input[name="humble_lms_membership"]').on('click', function() {
      $('.humble-lms-checkout-membership').removeClass('checked')
      $(this).parent().parent().addClass('checked')
      $('#humble-lms-paypal-buttons').data('price', $(this).data('price'))
      $('#humble-lms-paypal-buttons').data('membership', $(this).val())

      let membership = $('#humble-lms-paypal-buttons').data('membership')
      membership = membership.charAt(0).toUpperCase() + membership.slice(1)
      $('.humble-lms-lightbox p').html(membership + ', <strong>' + humble_lms.currency + ' ' + $('#humble-lms-paypal-buttons').data('price') + '</strong>')
      $('.humble-lms-btn--purchase-membership').removeClass('humble-lms-btn--disabled').addClass('humble-lms-toggle-lightbox')
    })

    if (humble_lms.is_user_logged_in && typeof paypal !== 'undefined' && $('#humble-lms-paypal-buttons').length !== 0) {
      paypal.Buttons({
        createOrder: function(data, actions) {
          let membership = $('#humble-lms-paypal-buttons').data('membership')
          let price = $('#humble-lms-paypal-buttons').data('price')

          if (typeof membership === 'undefined' || ! membership) {
            alert(humble_lms.membership_undefined)
            return
          }

          if (typeof price === 'undefined' || ! price) {
            alert(humble_lms.membership_price_undefined)
            return
          }

          return actions.order.create({
            purchase_units: [{
              amount: {
                value: price,
              },
              reference_id: membership
            }]
          })
        },
        onApprove: function(data, actions) {
          let context = $('#humble-lms-paypal-buttons').data('context')

          return actions.order.capture().then(function(details) {
            loadingLayer(true)

            $.ajax({
              url: humble_lms.ajax_url,
              type: 'POST',
              data: {
                action: 'save_paypal_transaction',
                context: context,
                details: details
              },
              dataType: 'json',
              error: function(MLHttpRequest, textStatus, errorThrown) {
                loadingLayer(false)
                alert('Sorry, there has been an error processing your transaction.')
                console.error(errorThrown)
              },
              success: function(response, textStatus, XMLHttpRequest) {
                window.location = window.location.pathname
              }
            })
          })
        }
      }).render('#humble-lms-paypal-buttons')
    }
  })()

  /**
   * Render PayPal buttons (single courses/tracks).
   * 
   * @since   0.0.1
   */
  const paypalButtonsSingle = (function() {
    if (humble_lms.is_user_logged_in && typeof paypal !== 'undefined' && $('#humble-lms-paypal-buttons-single-item').length !== 0) {
      paypal.Buttons({
        createOrder: function(data, actions) {
          let post_id = $('#humble-lms-paypal-buttons-single-item').data('post-id')
          let price = $('#humble-lms-paypal-buttons-single-item').data('price')

          if (typeof post_id === 'undefined' || ! post_id) {
            alert(humble_lms.post_id_undefined)
          }
      
          if (typeof price === 'undefined' || ! price) {
            alert(humble_lms.membership_price_undefined)
          }
    
          return actions.order.create({
            purchase_units: [{
              amount: {
                value: price
              },
              reference_id: post_id
            }]
          })
        },
        onApprove: function(data, actions) {
          let context = $('#humble-lms-paypal-buttons-single-item').data('context')

          return actions.order.capture().then(function(details) {
            loadingLayer(true)

            $.ajax({
              url: humble_lms.ajax_url,
              type: 'POST',
              data: {
                action: 'save_paypal_transaction',
                context: context,
                details: details
              },
              dataType: 'json',
              error: function(MLHttpRequest, textStatus, errorThrown) {
                loadingLayer(false)
                alert('Sorry, there has been an error processing your transaction.')
                console.error(errorThrown)
              },
              success: function(response, textStatus, XMLHttpRequest) {
                loadingLayer(true)
                window.location = window.location.pathname
              }
            })
          })
        }
      }).render('#humble-lms-paypal-buttons-single-item')
    }
  })()

  /**
   * Toggle lightbox.
   * 
   * @since   0.0.1
   */
  $(document).on('click', '.humble-lms-toggle-lightbox', function() {
    if ($('.humble-lms-lightbox-wrapper').css('display') === 'none') {
      $('.humble-lms-lightbox-wrapper').css('display', 'flex')
    } else {
      $('.humble-lms-lightbox-wrapper').fadeOut(200)
    }
  })

  $(document).click(function(event) {
    let $target = $(event.target);
    if(!$target.closest('.humble-lms-toggle-lightbox').length && !$target.closest('.humble-lms-lightbox').length && !$('.humble-lms-lighbox').is(':visible') && $('.humble-lms-lightbox').is(":visible")) {
      $('.humble-lms-lightbox-wrapper').fadeOut(200)
    }        
  })

  /**
   * Toggle syllabus.
   * 
   * @since   0.0.1
   */
  const handleSyllabus = (function() {
    let syllabus = $('.humble-lms-syllabus--lesson')
    let toggleButton = $('.humble-lms-toggle-syllabus')
    let syllabus_max_height = humble_lms.syllabus_max_height;
    let syllabus_height = syllabus.innerHeight()

    if (syllabus_height > syllabus_max_height) {
      toggleButton.css('display', 'block')
    }

    function toggleSyllabusHeight() {
      if (syllabus.innerHeight() <= syllabus_max_height) {
        syllabus.css({'height':'auto'})
      } else {
        syllabus.css({'height':syllabus_max_height + 'px'})
      }
    }

    toggleButton.on('click', function() {
      toggleSyllabusHeight()
    })

    toggleSyllabusHeight()
  })()

  /**
   * Toggle user transactions.
   */
  $('.humble-lms-user-transaction__title').on('click', function() {
    let that = $(this)
    $(this).parent().find($('.humble-lms-user-transaction__content')).slideToggle()
  })

})
