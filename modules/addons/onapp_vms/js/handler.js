$( document ).ready( function() {
	// bind actions
	$( '.mapserver' ).bind( 'change', function() {
		var go = document.location.href.replace( /&server_id=\d+/, '' );
		go = go.replace( /&page=\d+/, '' );
		go += '&server_id=' + this.value;
		document.location = go;
	} );
	$( '#tab0' ).bind( 'click', function() {
		$( this ).toggleClass( 'tabselected' );
		$( '#tab0box' ).slideToggle( 300 );
		return false;
	} );
	$( 'select#page' ).bind( 'change', function() {
		var go = document.location.href.replace( /&page=\d+/, '' );
		go += '&page=' + this.value;
		document.location.href = go;
	} );
	$( '.blockops button' ).bind( 'click', function() {
		$( '.blockops input' ).val( this.value );
		if( !$( '#blockops :checked' ).length ) {
			return false;
		}
	} );
	$( '#resetfilter' ).bind( 'click', function() {
		var go = document.location.href.replace( /&page=\d+/, '' );
		go += '&filterreset';
		document.location.href = go;
		return false;
	} );
	$( '.unmap' ).bind( 'click', function() {
		if( confirm( LANG.UnmapAlert ) ) {
			return true;
		}
		else {
			return false;
		}
	} );

	// set default values
	var pcre = /server_id=(\d+)/;
	pcre = pcre.exec( document.location.search );
	if( pcre ) {
		$( '.mapserver' ).val( pcre[1] );
	}

	var pcre = /filtermapped/;
	pcre = pcre.exec( document.location.search );
	if( pcre ) {
		$( '#map-filter' ).attr( 'checked', 'checked' );
	}
} );