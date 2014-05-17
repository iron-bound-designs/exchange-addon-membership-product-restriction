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

        if ( $( this ).attr( 'id' ) == 'mpr-addon-free-for-member-select' ) {
            $( "#mpr-addon-free-for-member-container" ).show();
        } else {
            $( "#mpr-addon-free-for-member-container" ).hide();
        }

    } );

    $( "#mpr-addon-free-after-date" ).datepicker( {
        prevText: '',
        nextText: '',
        dateFormat: $( 'input[name=it_exchange_availability_date_picker_format]' ).val()
    } );

    $( "#mpr-addon-non-members-free-after-date" ).datepicker( {
        prevText: '',
        nextText: '',
        dateFormat: $( 'input[name=it_exchange_availability_date_picker_format]' ).val()
    } );
} );