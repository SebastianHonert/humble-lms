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
   * Activate coupons.
   * 
   * @since   0.0.7
   */
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
