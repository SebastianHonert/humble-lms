
jQuery(document).ready(function($) {

  'use strict'

  /**
   * Searchable multi-select.
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
            selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
            selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected'

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
        let sortableList = that.$element.parent().parent().find('.ms-selection .ms-list')[0]
        
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

  initMultiselect('.humble-lms-searchable')

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
    let sections = $('.humble-lms-course-section:not(.humble-lms-course-section--cloneable')
    let section = $(this).parent().parent('.humble-lms-course-section')

    if (sections.length === 1) {
      return
    }

    section.fadeOut(500, function() {
      $(this).remove()

      sections = $('.humble-lms-course-section:not(.humble-lms-course-section--cloneable')
      sections.each(function(index, el) {
        $(el).attr('data-id', (index+1))
        $(el).find('.humble-lms-multiselect-value').prop('id', 'humble_lms_course_lessons-' + (index+1))
        $(el).find('.humble-lms-searchable').attr('data-content', 'course_lessons-' + (index+1))
      })

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
    let sections = $('.humble-lms-course-section:not(.humble-lms-course-section--cloneable')
    
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


    // Humble LMS Course Section
    if (element.hasClass('humble-lms-course-section')) {
      key = $('.humble-lms-course-section:not(.humble-lms-course-section--cloneable)').length

      clone.attr('data-id', (key+1))
      clone.find('.humble-lms-multiselect-value').prop('id', 'humble_lms_course_lessons-' + (key+1))
      clone.removeClass('humble-lms-course-section--cloneable')
      clone.find('.humble-lms-searchable--cloneable').addClass('humble-lms-searchable').removeClass('humble-lms-searchable--cloneable')
      clone.find('.humble-lms-searchable').attr('data-content', 'course_lessons-' + (key+1))
      clone.css('display', 'block')
      clone.appendTo(target)

      initMultiselect(clone.find('.humble-lms-searchable'))
      clone.find('.humble-lms-searchable').multiSelect('deselect_all')

      return
    }

    // Append clone
    clone.appendTo(target)

    // Humble LMS Answer
    if (element.hasClass('humble-lms-answer')) {
      elements = $($(this).data('element'))
      reindex(elements)
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
   * Color picker
   * 
   * @since   0.0.1
   */
  $('.humble_lms_color_picker')
  if ($.isFunction( jQuery.fn.wpColorPicker)) {
		$('.humble_lms_color_picker').wpColorPicker()
	}

})
