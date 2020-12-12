jQuery(document).ready(function($) {
  'use strict'

  const animSpeed = 400
  const animSpeedFast = 150

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

    loadingLayer(true)

    $('.humble-lms-message-quiz').hide(0)
    $('.humble-lms-quiz-score').text(evaluation.percent + ' %')
    $('.humble-lms-quiz-passing-percent').text(evaluation.passing_percent + ' %')

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
        if (response.tryAgain === 1 ) {
          location.reload(true)
          return
        }

        loadingLayer(false)

        if (response.max_attempts_exceeded && response.completed === 0) {
          $('.humble-lms-quiz, .humble-lms-quiz-submit').hide(0)
          $('.humble-lms-message--max-attempts-exceeded__initial').show(0)
          $('.humble-lms-message--limited-attempts').hide(0)
        } else if (response.completed === 1) {
          if (response.remaining_attempts !== -1) {
            $('.humble-lms-quiz-submit').hide(0)
          }

          $('.humble-lms-message--remaining-attempts').hide(0)
          $('.humble-lms-message--max-attempts-completed').show(0)
        }

        $('.humble-lms-quiz-remaining-attempts').text(response.remaining_attempts)

        if (response.completed === 0) {
          $('.humble-lms-quiz-message-image').html('<i class="ti-face-sad"></i>')
          $('.humble-lms-quiz-message').removeClass('humble-lms-quiz-message--success').addClass('humble-lms-quiz-message--failed')
          $('.humble-lms-quiz-message, .humble-lms-message-quiz--failed').fadeIn(animSpeed)
          $('.humble-lms-quiz-message').unbind('click')
        } else if (response.completed === 1) {
          $('.humble-lms-quiz-message-image').html('<i class="ti-face-smile"></i>')
          $('.humble-lms-quiz-message').removeClass('humble-lms-quiz-message--failed').addClass('humble-lms-quiz-message--success')
          $('.humble-lms-quiz-message, .humble-lms-message-quiz--completed').fadeIn(animSpeed)
          $('#humble-lms-mark-complete').fadeIn(animSpeed)

          // Add messages for awards and certificates
          $('.humble-lms-quiz-message').click(function() {
            let messages = []

            if (typeof response.awards !== 'undefined' && response.awards.length > 0) {
              response.awards.forEach(function(award) {
                messages.push(award)
              })
            }

            if (typeof response.certificates !== 'undefined' && response.certificates.length > 0) {
              response.certificates.forEach(function(certificate) {
                messages.push(certificate)
              })
            }

            addMessages(messages)
          })
        }
      },
      complete: function(reply, textStatus) {
        // ... 
      }
    })
  })

  $(document).on('click touch', '.humble-lms-quiz-message', function() {
    $(this).fadeOut(animSpeed)
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
        messageContainer.fadeOut(animSpeed, function() {
          messages.remove()
        })
        return
      }
      
      messages.eq(0).animate({
        opacity: 0,
      }, animSpeed, function () {
        $(this).remove()
        messages = $('.humble-lms-award-message-inner')
        if (messages.length) {
          messages.eq(0).animate({
            opacity: 1,
          }, animSpeed)
        }
      })
    }

    $(document).on('click touch', '.humble-lms-award-message-close', function () {
      closeMessages()
    })

    $(document).on('click touch', '.humble-lms-award-message-inner', function () {
      closeMessages()
    })

    messageContainer.fadeIn(animSpeed)
    messages.first().animate({
      opacity: 1,
    }, animSpeed)

    $(document).keyup( function (e) {
      if (e.key === 'Escape') {
        messageContainer.fadeOut(animSpeed, function() {
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
  const paypalButtonsMembership = (function () {
    $('input[name="humble_lms_membership"]').on('click', function() {
      $('.humble-lms-checkout-membership').removeClass('checked')
      $(this).parent().parent().addClass('checked')
      $('#humble-lms-paypal-buttons').data('price', $(this).data('price'))
      $('#humble-lms-paypal-buttons').data('membership', $(this).val())
      $('#humble-lms-paypal-buttons').data('coupon-id', $(this).data('coupon-id'))
      $('#humble-lms-paypal-buttons').data('original-price', $(this).data('original-price'))

      let membership = $('#humble-lms-paypal-buttons').data('membership')
      membership = membership.charAt(0).toUpperCase() + membership.slice(1)
      $('.humble-lms-lightbox[data-id="checkout"] p').html(membership + ', <strong>' + humble_lms.currency + ' ' + $('#humble-lms-paypal-buttons').data('price') + '</strong>')
      $('.humble-lms-btn--purchase-membership').removeClass('humble-lms-btn--disabled').addClass('humble-lms-toggle-lightbox')
    })

    $('.humble-lms-btn--purchase-membership').on('click', function() {
      loadingLayer(true)

      $('#humble-lms-paypal-buttons').empty()

      let validated_price
      let price = $('#humble-lms-paypal-buttons').data('price')
      let membership = $('#humble-lms-paypal-buttons').data('membership')
      let coupon_id = $('#humble-lms-paypal-buttons').data('coupon-id')

      if (typeof membership === 'undefined' || !membership) {
        return
      }

      if (typeof coupon_id === 'undefined' || !coupon_id) {
        coupon_id = 0
      }

      $.ajax({
        async: false,
        url: humble_lms.ajax_url,
        type: 'POST',
        data: {
          action: 'validate_membership_price',
          coupon_id: coupon_id,
          membership: membership
        },
        dataType: 'json',
        error: function(MLHttpRequest, textStatus, errorThrown) {
          console.log(errorThrown)
        },
        success: function(response, textStatus, XMLHttpRequest) {
          validated_price = response
        },
      })

      if (validated_price.toString() !== price.toString()) {
        setTimeout(function() {
          $('.humble-lms-lightbox-wrapper').css('display', 'none')
          loadingLayer(false)
        }, 100)

        return
      }

      loadingLayer(false)

      if (humble_lms.is_user_logged_in && typeof paypal !== 'undefined' && $('#humble-lms-paypal-buttons').length !== 0) {
        paypal.Buttons({
          createOrder: function(data, actions) {
            if (typeof membership === 'undefined' || ! membership) {
              alert(humble_lms.membership_undefined)
              return
            }
  
            if (typeof price === 'undefined' || ! price) {
              alert(humble_lms.price_undefined)
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
                  alert(humble_lms.general_error)
                  console.error(errorThrown)
                },
                success: function(response, textStatus, XMLHttpRequest) {
                  window.location = window.location.pathname + '?purchase=success'
                }
              })
            })
          }
        }).render('#humble-lms-paypal-buttons')
      }
    })
  })()

  /**
   * Render PayPal buttons (single courses/tracks).
   * 
   * @since   0.0.1
   */
  const paypalButtonsSingle = (function () {
    if (humble_lms.is_user_logged_in && typeof paypal !== 'undefined' && $('#humble-lms-paypal-buttons-single-item').length !== 0) {
      let post_id = $('#humble-lms-paypal-buttons-single-item').data('post-id')
      let price = $('#humble-lms-paypal-buttons-single-item').data('price')

      paypal.Buttons({
        createOrder: function(data, actions) {
          if (typeof post_id === 'undefined' || ! post_id) {
            alert(humble_lms.post_id_undefined)
            return
          }
      
          if (typeof price === 'undefined' || ! price) {
            alert(humble_lms.price_undefined)
            return
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
                alert(humble_lms.general_error)
                console.error(errorThrown)
              },
              success: function(response, textStatus, XMLHttpRequest) {
                window.location = window.location.pathname + '?purchase=success'
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
    let target = $(this).data('target')

    if (typeof target === 'undefined' || !target ) {
      console.log('Lightox target not defined.')
      $('.humble-lms-lightbox-wrapper').fadeOut(animSpeed)
      return
    }

    if (target === 'redeem') {
      let code = $('.humble-lms-input--coupon-code').val()
      if (typeof code === 'undefined' || !code) {
        return
      }
    }

    let lightbox = $('.humble-lms-lightbox[data-id="' + target + '"')

    $('.humble-lms-lightbox').hide(0)
    lightbox.show(0)

    if ($('.humble-lms-lightbox-wrapper').css('display') === 'none') {
      $('.humble-lms-lightbox-wrapper').css('display', 'flex').hide(0).fadeIn(animSpeed)
    } else {
      $('.humble-lms-lightbox-wrapper').fadeOut(animSpeed)
    }
  })

  $(document).click(function(event) {
    let $target = $(event.target);
    if(!$target.closest('.humble-lms-toggle-lightbox').length && !$target.closest('.humble-lms-lightbox').length && !$('.humble-lms-lighbox').is(':visible') && $('.humble-lms-lightbox').is(":visible")) {
      $('.humble-lms-lightbox-wrapper').fadeOut(animSpeed)
    }        
  })

  /**
   * Toggle syllabus.
   * 
   * @since   0.0.1
   */
  const handleSyllabus = (function() {
    let syllabus = $('.humble-lms-syllabus--lesson')
    let syllabus_state = humble_lms.user_syllabus_state
    let toggleButton = $('.humble-lms-toggle-syllabus')
    let syllabus_max_height = humble_lms.syllabus_max_height;
    let syllabus_height = syllabus.innerHeight()

    if (humble_lms.is_user_logged_in && (syllabus_height > syllabus_max_height)) {
      toggleButton.css('display', 'block')
    }

    function toggleSyllabusHeight(ajax = false) {
      let syllabus_height = syllabus.innerHeight()
      
      function expand_syllabus() {
        syllabus.css({'height':'auto'})
        syllabus_height = syllabus.innerHeight()
        $('.humble-lms-toggle-syllabus').text(humble_lms.toggle_syllabus_text_close)
        syllabus_state = 'expanded'
      }

      function close_syllabus() {
        syllabus.css({'height':syllabus_max_height + 'px'})
        syllabus_height = syllabus.innerHeight()
        $('.humble-lms-toggle-syllabus').text(humble_lms.toggle_syllabus_text_expand)
        syllabus_state = 'closed'
      }

      if (!syllabus_state || syllabus_state === 'expanded') {
        expand_syllabus()
      } else {
        close_syllabus()
      }

      if (ajax) {
        loadingLayer(true)
        $.ajax({
          url: humble_lms.ajax_url,
          type: 'POST',
          data: {
            action: 'toggle_syllabus_height',
            syllabusState: syllabus_state,
            nonce: humble_lms.nonce
          },
          dataType: 'json',
          error: function(MLHttpRequest, textStatus, errorThrown) {
            console.error(errorThrown)
            loadingLayer(true)
          },
          success: function(response, textStatus, XMLHttpRequest) {
            if (response === 'closed') {
              close_syllabus()
            } else {
              expand_syllabus()
            }

            loadingLayer(false)
          }
        })
      }
    }

    toggleButton.on('click', function() {
      toggleSyllabusHeight(true)
    })

    toggleSyllabusHeight(false)
  })()

  /**
   * Toggle user transactions.
   *
   * @since   0.0.1
   */
  $('.humble-lms-user-transaction__title').on('click', function() {
    let that = $(this)
    $(this).parent().find($('.humble-lms-user-transaction__content')).slideToggle()
  })

  /**
   * Add award message.
   *
   * @since   0.0.1
   */
  function addMessages(messages) {
    $('.humble-lms-award-message').remove()

    if (messages.length === 0) {
      return
    }

    let awardHTML = '<div class="humble-lms-award-message humble-lms-award-message--quiz"><div>'

    messages.forEach(function (message) {
      if (!message.title || !message.name || (!message.image_url && !message.icon)) {
        return
      }

      let imageHTML = ''

      if (message.image_url) {
        imageHTML = '<img class="humble-lms-award-image humble-lms-bounce-in" src="' + message.image_url + '" alt="" />'
      } else if (message.icon) {
        imageHTML = '<div class="humble-lms-award-message-image humble-lms-bounce-in"><i class="' + message.icon + '"></i></div>'
      } else {
        imageHTML = ''
      }
      
      awardHTML += `<div class="humble-lms-award-message-inner"><div>
        <div class="humble-lms-award-message-close" aria-label="Close award overlay">
          <i class="ti-close"></i>
        </div>
        <h3 class=humble-lms-award-message-title">` + message.title + `</h3>
        <p class="humble-lms-award-message-content-name">` + message.name + `</p>` + imageHTML + `
      </div></div>`
  
    }, animSpeed)

    awardHTML += '</div></div>';

    $('body').append(awardHTML)
    showMessages()
  }

  // TippyJS
  const tippys = tippy('[data-tippy-content]', {
    delay: animSpeedFast,
    animation: 'scale',
    allowHTML: true,
    interactive: true,
    theme: 'default' !== humble_lms.tippy_theme ? humble_lms.tippy_theme : false,
    trigger: 'click'
  })

  tippys.forEach(function (tippy) {
    const content = marked(tippy.popper.innerText)
    tippy.setContent(content)
  })

  // Progress animation
  $('.humble-lms-progress-bar').on('inview', function (event, isInView) {
    if (isInView) {
        let value = $(this).find('.humble-lms-progress-bar-inner').not('.activated')

        value.addClass('activated').width('0').stop().css('opacity', '.875').animate({
            width   : [value.attr('data-value') + '%', 'swing'],
            opacity : [1, 'swing'],
        }, 1000 + Math.ceil(Math.random() * 250))
    }
  })

  // Activate coupon
  $('.humble-lms-redeem-coupon-confirm').on('click', function() {
    let code = $('#humble-lms-redeem-coupon').find('.humble-lms-input--coupon-code').val()

    if (typeof code === 'undefined' || !code) {
      alert('No code...')
      return
    }

    $.ajax({
      async: false,
      url: humble_lms.ajax_url,
      type: 'POST',
      data: {
        action: 'activate_coupon',
        code: code,
      },
      dataType: 'json',
      error: function(MLHttpRequest, textStatus, errorThrown) {
        loadingLayer(false)
        alert(humble_lms.general_error)
        console.log(errorThrown)
      },
      success: function(response, textStatus, XMLHttpRequest) {
        $('#humble-lms-redeem-coupon').submit()
      },
    })
  })

})
