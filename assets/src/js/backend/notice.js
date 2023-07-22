( function ( $ ) {
	class FWProject_Notice {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl ?? '';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce ?? '';
      this.notice();
			// 01775898205
			// Best Friend
		}
    notice() {
      const thisClass = this;var x, c, s, a, ev, go;
      document.querySelectorAll( '.fwp-notice--dismissible' ).forEach( function( e, i ) {
        if( e.dataset.isHandled != true ) {
          e.dataset.isHandled = true;
          var x = e.querySelector( '.fwp-notice__dismiss' );
          if( x ) {
            x.addEventListener( 'click', function( event ) {
              if( false !== ( ev = x.dataset.events ) ) {
                go = true;ev = JSON.parse( ev );// console.log( ev );
                if( ev.confirm ) {
                  if( ! confirm( ev.confirm ) ) {
                    go = false;
                  }
                }
                if( ev.request && ev.request.length >= 1 && ev.request.action && go ) {
                  $.ajax( {
                    url: thisClass.ajaxUrl,
                    type: 'post',
                    data: ev.request,
                    success: ( response ) => {
                      thisClass.remove( x, e );
                    },
                    error: ( response ) => {
                      console.log( response );
                    },
                  } );
                } else {
                  ( ! go ) || thisClass.remove( x, e );
                }
              }
            } );
            c = e.querySelector( '.fwp-notice__cancel' );
            if( c ) {
              c.addEventListener( 'click', () => x.click() );
            }
          }
        }
      } );
    }
    remove( x, e ) {
      var a;
      // Remove notice with little f trnasition effect.
      a = x.dataset.delay ?? 100;a = parseInt( a );e.style.opacity = 1;
      e.style.transition = 'opacity ' + ( a / 1000 ) + 's ease';e.style.opacity = 0;
      setTimeout( () => e.remove(), a );
      // e.addEventListener( "transitionend", () => e.remove() } );
    }
	}

	new FWProject_Notice();
} )( jQuery );
