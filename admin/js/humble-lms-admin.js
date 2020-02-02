
jQuery(document).ready(function($) {

  'use strict'

  /**
   * Searchable multi-select.
   * 
   * @since   0.0.1
   */
  $('.humble-lms-searchable').multiSelect({
    selectableHeader: "<input type='text' class='search-input widefat humble-lms-searchable-input' autocomplete='off' placeholder='Search...'>",
    selectionHeader: "<input type='text' class='search-input widefat humble-lms-searchable-input' autocomplete='off' placeholder='Search...'>",
    afterInit: function(ms) {
      let that = this,
          $selectableSearch = that.$selectableUl.prev(),
          $selectionSearch = that.$selectionUl.prev(),
          selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
          selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected'
  
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
            $('#humble_lms_' + content).val(JSON.stringify(selectedItemIds))
          }
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
  $('.humble-lms-repeater').live('click', function(e) {
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

    clone.appendTo(target)

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
  $('.humble-lms-remove-answer').live('click', function(e) {
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
      console.log(index)
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
    return functionToCheck && {}.toString.call(functionToCheck) === '[object Function]';
  }

})
