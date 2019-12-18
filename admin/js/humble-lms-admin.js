(function( $ ) {
  'use strict'

  $(document).ready(function() {

    let sortable = null

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

        let sortableList = $('.ms-selection .ms-list')[0]
        sortable = Sortable.create(sortableList, {
          animation: 100,
          onEnd: function () { 
            let selectedItemIds = []
            let selectedItems = Array.from($(sortableList).find('.ms-selected'))

            selectedItems.forEach(item => selectedItemIds.push($(item).data('id')))
            selectedItemIds = selectedItems.map(function (item) {
              return $(item).data('id')
            })

            if ($('#humble_lms_course_lessons').length) {
              $('#humble_lms_course_lessons').val(JSON.stringify(selectedItemIds))
            } else if ($('#humble_lms_track_courses').length) {
              $('#humble_lms_track_courses').val(JSON.stringify(selectedItemIds))
            }
          }
        })

        sortable.options.onEnd()
      },
      afterSelect: function () {
        this.qs1.cache()
        this.qs2.cache()
        sortable.options.onEnd()
      },
      afterDeselect: function () {
        this.qs1.cache()
        this.qs2.cache()
        sortable.options.onEnd()
      }
    })

  })

})( jQuery )
