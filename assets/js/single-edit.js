jQuery( document ).ready( function ( $ ) {
    $( "#mpr-addon-options-enable" ).click( function () {
        var options = $( "#mpr-addon-options-pane" );

        if ( $( this ).attr( 'checked' ) == 'checked' )
            options.removeClass( 'hide-if-js' ).show();
        else
            options.hide();
    } );

    $( ".mpr-addon-action" ).click( function () {

        if ( $( this ).attr( 'id' ) == 'mpr-addon-additional-fee-select' ) {
            $( "#mpr-addon-additional-fee-container" ).show();
        } else {
            $( "#mpr-addon-additional-fee-container" ).hide();
        }

        if ( $( this ).attr( 'id' ) == 'mpr-addon-hide-from-store-select' ) {
            $( "#mpr-addon-hide-from-store-container" ).show();
        } else {
            $( "#mpr-addon-hide-from-store-container" ).hide();
        }

    } );
} );