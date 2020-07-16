
jQuery(document).ready(function($) {

  'use strict'

  /**
   * Searchable/sortable multi-select.
   * 
   * @since   0.0.1
   */
  function initMultiselect (el) {
    $(el).multiSelect({
      selectableHeader: "<input type='text' class='search-input widefat humble-lms-searchable-input' autocomplete='off' placeholder='Search...'>",
      selectionHeader: "<input type='text' class='search-input widefat humble-lms-searchable-input' autocomplete='off' placeholder='Search...'>",
      afterInit: function(ms) {
        let that = this,
            $selectableSearch = that.$selectableUl.prev(),
            $selectionSearch = that.$selectionUl.prev(),
            selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
            selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected'

        $('.humble-lms-course-section-title').on('keyup', function() {
          that.sortable.options.onEnd()
        })
        
        that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
        .on('keydown', function (e) {
          if (e.which === 40) {
            that.$selectableUl.focus()
            return false
          }
        })

        that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
        .on('keydown', function (e) {
          if (e.which == 40) {
            that.$selectionUl.focus()
            return false
          }
        })

        let content = that.$element.data('content')
        let sortableList = that.$element.parent().find('.ms-selection .ms-list')[0]
        
        that.sortable = Sortable.create(sortableList, {
          animation: 100,
          onEnd: function () {
            let selectedItemIds = []
            let selectedItems = Array.from($(sortableList).find('.ms-selected'))

            selectedItems.forEach(item => selectedItemIds.push($(item).data('id')))
            selectedItemIds = selectedItems.map(function (item) {
              return $(item).data('id')
            })

            if ($('#humble_lms_' + content).length) {
              $('#humble_lms_' + content).val(selectedItemIds)
            }

            updateCourseSectionsInput()
          }
        })

        this.sortable.options.onEnd()
      },
      afterSelect: function () {
        this.qs1.cache()
        this.qs2.cache()
        this.sortable.options.onEnd()
      },
      afterDeselect: function () {
        this.qs1.cache()
        this.qs2.cache()
        this.sortable.options.onEnd()
      }
    })
  }

  initMultiselect($('.humble-lms-searchable'))

  /**
   * Multiselect: select/deselect all
   * 
   * @since   0.0.1
   */
  $('.humble-lms-multiselect-select-all').on('click', function() {
    $('.humble-lms-searchable').multiSelect('select_all')
  })

  $('.humble-lms-multiselect-deselect-all').on('click', function() {
    $('.humble-lms-searchable').multiSelect('deselect_all')
  })

  /**
   * Remove course sections.
   * 
   * @since   0.0.1
   */
  $(document).on('click', '.humble-lms-course-section-remove', function() {
    let sections = $('.humble-lms-course-section')
    let section = $(this).parent().parent('.humble-lms-course-section')

    if (sections.length === 1) {
      alert(humble_lms.oneSectionMessage)
      return
    }

    section.fadeOut(500, function() {
      $(this).remove()

      updateCourseSectionsInput()
    })

  })

  /**
   * Update course sections.
   * 
   * @since   0.0.1
   */
  function updateCourseSectionsInput() {
    let section_array = []
    let sections = $('.humble-lms-course-section')

    sections.each(function(index, el) {
      $(el).attr('data-id', (index+1))
      $(el).find('.humble-lms-course-section-number').text(index+1)
      $(el).find('.humble-lms-multiselect-value').prop('id', 'humble_lms_course_lessons-' + (index+1))
      $(el).find('.humble-lms-searchable').attr('data-content', 'course_lessons-' + (index+1))
      $(el).find('.humble-lms-open-admin-lightbox').attr('data-id', (index+1))
    })

    sections.each(function(index, section) {
      let section_object = {}
      let lessons = $(section).find('.ms-selection .ms-elem-selection.ms-selected')
      let lesson_ids = []


      lessons.each(function(index, lesson) {
        lesson_ids.push($(lesson).data('id'))
      })

      lesson_ids = lesson_ids.join(',')

      section_object['title'] = $(section).find('.humble-lms-course-section-title').first().val()
      section_object['lessons'] = lesson_ids

      section_array.push(section_object)
      $('#humble_lms_course_sections').val(JSON.stringify(section_array))
    })
  }

  /**
   * Sort course sections.
   * 
   * @since   0.0.1
   */
  let el = document.getElementById('humble-lms-admin-course-sections')

  if (el) {
    Sortable.create(el, {
      onSort: function() {
        updateCourseSectionsInput()
      }
    })
  }

  /**
   * Pre-select an activity.
   * 
   * @since   0.0.1
   */
  function activate_activity_trigger_select() {
    let selected_value = $('#humble_lms_activity_trigger').val()
    if (!selected_value) {
      $('.humble-lms-activity-trigger-select').hide(0)
    } else {
      let selected_option = $('#humble_lms_activity_trigger option[value="' + selected_value + '"]')
      let selected_data = selected_option.data('select')
      $('.humble-lms-activity-trigger-select').hide(0)
      if (typeof selected_data !== 'undefined') {
        $('#' + selected_data.toString()).show(0)
      }

      if (selected_value === 'user_completed_quiz') {
        $('#humble_lms_activity_trigger_quiz_percent, .humble-lms-activity-trigger-quiz-percent').show(0)
      } else {
        $('#humble_lms_activity_trigger_quiz_percent, .humble-lms-activity-trigger-quiz-percent').hide(0)
      }

      if (selected_value === 'user_completed_all_track_quizzes') {
        $('#humble_lms_activity_trigger_all_track_quizzes_percent, .humble-lms-activity-trigger-all-track-quizzes-percent').show(0)
      } else {
        $('#humble_lms_activity_trigger_all_track_quizzes_percent, .humble-lms-activity-trigger-all-track-quizzes-percent').hide(0)
      }

      if (selected_value === 'user_completed_all_course_quizzes') {
        $('#humble_lms_activity_trigger_all_course_quizzes_percent, .humble-lms-activity-trigger-all-course-quizzes-percent').show(0)
      } else {
        $('#humble_lms_activity_trigger_all_course_quizzes_percent, .humble-lms-activity-trigger-all-course-quizzes-percent').hide(0)
      }
    }
  }

  function activate_activity_action_select() {
    let selected_value = $('#humble_lms_activity_action').val()
    if (!selected_value) {
      $('.humble-lms-activity-action-select').hide(0)
    } else {
      let selected_option = $('#humble_lms_activity_action option[value="' + selected_value + '"]')
      let selected_data = selected_option.data('select')
      $('.humble-lms-activity-action-select').hide(0)
      if (typeof selected_data !== 'undefined') {
        $('#' + selected_data.toString()).show(0)
      }
    }
  }

  activate_activity_trigger_select()
  activate_activity_action_select()

  // Activities custom post type select options
  $('#humble_lms_activity_trigger').on('change', function() {
    activate_activity_trigger_select()
  })

  $('#humble_lms_activity_action').on('change', function() {
    activate_activity_action_select()
  })

  /**
   * Repeater fields.
   * 
   * @since   0.0.1
   */
  $(document).on('click', '.humble-lms-repeater', function(e) {
    e.preventDefault()

    let elements = $($(this).data('element'))
    let element = $($(this).data('element')).last()
    let target = $($(this).data('target'))

    if ((element.length === 0) || (target.length === 0)) {
      console.log('No element and/or target selected.')
      return
    }
  
    let key = elements.length
    let clone = element.clone()

    // Humble LMS Answer
    if (element.hasClass('humble-lms-answer')) {
      clone.find('.humble-lms-answer-text').val('')
      clone.find('.humble-lms-answer-correct').prop('checked', false)
      clone.appendTo(target)

      elements = $($(this).data('element'))
      reindex(elements)

      return
    }

    // Humble LMS Course Section
    if (element.hasClass('humble-lms-course-section')) {
      key = $('.humble-lms-course-section').length

      // Remove multiSelect container from clone because it needs to be re-initialized
      clone.find('.ms-container').remove()
      clone.attr('data-id', (key+1))
      clone.find('.humble-lms-open-admin-lightbox').attr('data-id', (key+1))
      clone.find('.humble-lms-course-section-number').text((key+1))
      clone.find('.humble-lms-course-section-title').val('')
      clone.find('.humble-lms-multiselect-value').prop('id', 'humble_lms_course_lessons-' + (key+1))
      clone.find('.humble-lms-searchable').attr('data-content', 'course_lessons-' + (key+1))
      clone.find('.humble-lms-searchable').removeAttr('id')
      clone.css('display', 'none').appendTo(target).fadeIn(400)

      initMultiselect(clone.find('.humble-lms-searchable'))
      clone.find('.humble-lms-searchable').multiSelect('deselect_all')
    }
  })

  /**
   * Remove a quiz answer.
   * 
   * @since   0.0.1
   */
  $(document).on('click', '.humble-lms-remove-answer', function(e) {
    e.preventDefault()

    let answers = $('.humble-lms-answer')
    let length = answers.length

    if (length < 2) {
      return
    }

    $(this).closest('div').remove()
    answers = $('.humble-lms-answer')
    reindex(answers)
  })

  /**
   * Re-index elements.
   * 
   * @since   0.0.1
   */
  function reindex(elements) {
    elements.each(function(index, el) {
      $(el).find('input').eq(0).attr('name', 'humble_lms_question_answers[' + index + '][answer]')
      $(el).find('input').eq(1).attr('name', 'humble_lms_question_answers[' + index + '][correct]')
    })
  }

  /**
   * Show/hide loading layer/spinner
   * 
   * @since   0.0.1
   */
  function loadingLayer(show = false, f = null) {
    let loadingLayer = $('.humble-lms-loading-layer')
    if (show) {
      loadingLayer.css('display', 'flex')
    } else {
      loadingLayer.hide(0)
    }

    if (isFunction (f)) {
      setTimeout( function() {
        f()
      }, 100)
    }
  }

  /**
   * Send a test email.
   * 
   * @since   0.0.1
   */
  $('a.humble-lms-send-test-email').on('click', function(e) {
    let container = $(this).closest('div.humble-lms-test-email')
    let subject = container.find('input[name=subject]').val()
    let message = container.find('textarea').val()
    let recipient = container.find('#humble-lms-test-email-recipient').val()
    let format = $(this).data('format')

    if (!message || !recipient) {
      alert(humble_lms.sendTestEmailValidation)
      return
    }

    loadingLayer(true)

    $.ajax({
      url: humble_lms.ajax_url,
      type: 'POST',
      data: {
        action: 'send_test_email',
        subject: subject,
        message: message,
        recipient: recipient,
        format: format,
        nonce: humble_lms.nonce
      },
      dataType: 'json',
      error: function(MLHttpRequest, textStatus, errorThrown) {
        console.error(errorThrown)
        loadingLayer(false)
      },
      success: function(response, textStatus, XMLHttpRequest) {
        loadingLayer(false, function() {
          if (response === 'success') {
            alert(humble_lms.sendTestEmailSuccess)
          } else {
            alert(humble_lms.sendTestEmailError)
          }
        })
      },
    })
  })

  /**
   * Check if object is a function.
   * 
   * @since   0.0.1
   */
  function isFunction(functionToCheck) {
    return functionToCheck && {}.toString.call(functionToCheck) === '[object Function]'
  }

  /**
   * Reset user progress.
   *
   * @since   0.0.1
   */
  $('.humble-lms-reset-user-progress').on('click', function(e) {
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
            console.log(errorThrown)
            loadingLayer(false)
          },
          success: function(response, textStatus, XMLHttpRequest) {
            location.reload()
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
   * Uncheck instructor on user profile page.
   *
   * @since   0.0.1
   */
  $('#humble_lms_is_instructor').on('change', function(e) {
    let isInstructor = $(this).is(':checked')
    let wasInstructor = parseInt($('input[name="humble_lms_was_instructor"]').val())

    if (wasInstructor === 1 && ! isInstructor) {
      if (! confirm(humble_lms.uncheckUserIsInstructor)) {
        $(this).prop('checked', true) 
      }
    }
  })

  /**
   * Check if every quiz question has an answer.
   *
   * @since   0.0.1
   */
  $('form#post').on('submit', function(e) {
    if (!$('body').hasClass('post-type-humble_lms_question')) {
      return
    }

    if (!$('input[name="humble_lms_question"]').val()) {
      e.preventDefault()
      alert(humble_lms.questionMissing)
      return false
    }

    if ($('select.humble_lms_question_type').val() === 'multiple_choice') {
      if ($('.humble-lms-answer-correct:checked').length === 0) {
        e.preventDefault()
        alert(humble_lms.correctAnswerMissing)
        return false
      }
    }
  })

  /**
   * Select/deselect all access levels.
   * 
   * @since   0.0.1
   */
  $('.humble-lms-access-levels-check-all').on('click', function() {
    let checkboxes = $('input[name="humble_lms_lesson_access_levels[]"]')
    let checked = false

    $.each(checkboxes, function (index, value) {
      if ($(checkboxes[index]).is(':checked')) {
        checked = true
      }
    })

    $('input[name="humble_lms_lesson_access_levels[]"]').prop('checked', !checked)
  })

  /**
   * Colorpicker
   * 
   * @since   0.0.1
   */
  $('.humble_lms_color_picker')
  if ($.isFunction( jQuery.fn.wpColorPicker)) {
		$('.humble_lms_color_picker').wpColorPicker()
  }
  
  /**
   * Datepicker
   * 
   * @since   0.0.1
   */
  $('.humble-lms-datepicker').datepicker({
    dateFormat: 'yy-mm-dd',
    onSelect: function (selected) {
      let date = new Date(selected)
          date = date.setMinutes(date.getMinutes() - date.getTimezoneOffset())

      let timestamp = Math.floor($.datepicker.formatDate('@', new Date(date)) / 1000)

      if ($(this).attr('name') === 'humble_lms_course_timestamps[from]') {
        $('input[name="humble_lms_course_timestamps[to]"]').datepicker('option', 'minDate', selected)
        $('input[name="humble_lms_course_timestamps[timestampFrom]"]').val(timestamp)
      } else if ($(this).attr('name') === 'humble_lms_course_timestamps[to]') {
        $('input[name="humble_lms_course_timestamps[from]"]').datepicker('option', 'maxDate', selected)
        $('input[name="humble_lms_course_timestamps[timestampTo]"]').val(timestamp)
      }
    }
  })

  $('.humble-lms-clear-datepicker-from').on('click', function () {
    $('input[name="humble_lms_course_timestamps[from]"]').datepicker('setDate', null).val()
    $('input[name="humble_lms_course_timestamps[timestampFrom]"]').val('')
  })

  $('.humble-lms-clear-datepicker-to').on('click', function () {
    $('input[name="humble_lms_course_timestamps[to]"]').datepicker('setDate', null)
    $('input[name="humble_lms_course_timestamps[timestampTo]"]').val('')
  })

  /**
   * Add content with lightbox.
   * 
   * @since   0.0.1
   */
  const adminLightbox = (function() {
    let id

    $(document).on('click', '.humble-lms-open-admin-lightbox', function() {
      id = typeof $(this).data('id') !== 'undefined' ? $(this).data('id') : null;

      $('.humble-lms-add-content-lightbox-wrapper').css('display', 'flex')
      $('.humble-lms-add-content-name').focus()
    })
  
    $(document).on('click', '.humble-lms-add-content-cancel', function() {
      $('.humble-lms-add-content-lightbox-wrapper').fadeOut(200)
      $('.humble-lms-add-content-error').fadeOut(200)
      $('.humble-lms-add-content-success').fadeOut(200)
    })
  
    $(document).on('click', '.humble-lms-add-content-submit', function() {
      let post_type = $('.humble-lms-add-content-lightbox').data('post_type')
      let lang = $('.humble-lms-add-content-lightbox').data('lang')
      let title = $('.humble-lms-add-content-name').val()
  
      if (!post_type || !title) {
        $('.humble-lms-add-content-error').text($('.humble-lms-add-content-error').data('message')).css('display', 'block')
        return
      }

      loadingLayer(true)
  
      $.ajax({
        url: humble_lms.ajax_url,
        type: 'POST',
        data: {
          action: 'add_content',
          title: title,
          post_type: post_type,
          lang: lang,
          nonce: humble_lms.nonce
        },
        dataType: 'json',
        error: function(MLHttpRequest, textStatus, errorThrown) {
          console.error(errorThrown)
          loadingLayer(false)
          $('.humble-lms-add-content-lightbox-wrapper').fadeOut(200)
        },
        success: function(response, textStatus, XMLHttpRequest) {
          loadingLayer(false, function() {
            if (response === 'error') {
              alert('Hm, something went wrong.')
              return
            }
          })

          // Select new option in current section and re-initialize searchable multiSelect
          if (response.post_type === 'humble_lms_lesson') {
            $('.humble-lms-course-section').find('.humble-lms-searchable').multiSelect('addOption', {
              value: response.post_id,
              text: response.post_title
            })

            let currentSection = $('.humble-lms-course-section[data-id="' + id + '"]')
            currentSection.find('.humble-lms-searchable option:last').val(response.post_id).attr('data-id', response.post_id).attr('selected', 'selected')
            currentSection.find('.ms-container').remove()
            currentSection.find('.humble-lms-searchable').multiSelect('refresh')
            initMultiselect($('.humble-lms-searchable'))
          } else if (response.post_type === 'humble_lms_course' || response.post_type === 'humble_lms_question') {
            $('.humble-lms-searchable').multiSelect('addOption', {
              value: response.post_id,
              text: response.post_title
            })

            $('.humble-lms-searchable option:last').val(response.post_id).attr('data-id', response.post_id).attr('selected', 'selected')
            $('.humble-lms-searchable').multiSelect('refresh')
            initMultiselect($('.humble-lms-searchable'))
          }

          // Add link to edit new content
          response.post_edit_link = response.post_edit_link.replace('&#038;', '&')
          $('.humble-lms-add-content-success a').attr('href', response.post_edit_link)
          $('.humble-lms-add-content-success').fadeIn(200)
          $('.humble-lms-add-content-name').val('')
        },
      })
    })
  })()

  // Toggle sections
  $(document).on('click', '.humble-lms-toggle-section', function() {
    $(this).parent().parent().find('.humble-lms-section-toggle-wrapper').toggle(200)
  })

})
