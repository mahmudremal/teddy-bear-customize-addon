// https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
import { __ } from '@wordpress/i18n';


( function ( $ ) {
	class FWProject_Buttons {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl ?? '';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce ?? '';
      this.btnSwitch();
			// 01775898205
			// Best Friend
      // this is a translated message by js  __( 'Layout style dark background', 'fwp-Listivo-child-c4trade' ),
		}
    btnSwitch() {
      const thisClass = this;var x, c, s, a, ev, is, go;
      document.querySelectorAll( '.btn-ajax-switch' ).forEach( function( e, i ) {
        if( e.dataset.isHandled != true ) {
          e.dataset.isHandled = true;
          e.addEventListener( 'click', function( event ) {
            if( false !== ( ev = e.dataset.events ) && ( ! e.dataset.disabled || e.dataset.disabled == 'false' ) ) {
              thisClass.beforeEffect( e );
              go = true;ev = JSON.parse( ev );// console.log( ev );
              if( ev.confirm && ! confirm( __( 'Are you sure you want to switch this listing status?', 'woocommerce-checkout-video-snippet' ) ) ) {
                go = false;thisClass.afterEffect( e );
              }
              if( ev.request && ev.request.action && ev.request.action != '' && go ) {
                ev.request.status = ( e.dataset.status == 'on' ) ? 'pending' : 'publish';
                $.ajax( {
                  url: thisClass.ajaxUrl,
                  type: 'post',
                  data: ev.request,
                  success: ( response ) => {
                    is = ( response.success && response.data.status );
                    thisClass.proceed( e, is );
                  },
                  error: ( response ) => {
                    console.log( response.responseText );
                    thisClass.afterEffect( e );
                  },
                } );
              } else {
                ( ! go ) || thisClass.proceed( e, true );
              }
            }
          } );
        }
      } );
    }
    proceed( e, status ) {
      const thisClass = this;var a;
      if( status ) {
        e.dataset.status = ( e.dataset.status == 'on' ) ? 'off' : 'on';
        if( e.dataset.onFinished ) {
          // window[ e.dataset.onFinished ]( e );
          eval( e.dataset.onFinished );
          // var F = new Function( e.dataset.onFinished );return( F() );
        }
      }
      thisClass.afterEffect( e );
    }
    beforeEffect( e ) {
      e.classList.add( 'loading' );e.dataset.disabled = true;
      if( e.dataset.onInit ) {
        // window[ e.dataset.onInit ]( e );
        eval( e.dataset.onInit );
      }
    }
    afterEffect( e ) {
      e.classList.remove( 'loading' );e.dataset.disabled = false;
    }
	}

	new FWProject_Buttons();
} )( jQuery );
