
jQuery(document).ready(function($) {

  'use strict'

  // Searchable multi select
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

  // Pre-select activity
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

})
