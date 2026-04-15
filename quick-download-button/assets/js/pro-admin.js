/**
 * Quick Download Button — Admin JS
 *
 * Handles the Shortcode Builder page:
 *   - Reads all [data-sc-attr] inputs and builds a shortcode string
 *   - Conditionally shows/hides dependent fields
 *   - WP media picker for the file URL field
 *   - Copy to clipboard
 */
( function ( $ ) {
    'use strict';

    // Only run on the builder page.
    if ( ! $( '#qdbp-builder-result' ).length ) return;

    var SHORTCODE = 'quick_download_button';

    // ── Build shortcode from all tagged inputs ───────────────────────────────
    function buildShortcode() {
        var parts = [];

        // Text / URL / number inputs and selects
        $( '.qdbp-b-input' ).each( function () {
            var $el      = $( this );
            var attr     = $el.data( 'sc-attr' );
            var defVal   = String( $el.data( 'sc-default' ) );
            var val      = String( $el.val() ).trim();

            if ( ! attr ) return;
            if ( val === '' || val === defVal ) return;

            parts.push( attr + '="' + val.replace( /"/g, '&quot;' ) + '"' );
        } );

        // Checkboxes
        $( '.qdbp-b-checkbox' ).each( function () {
            var $el    = $( this );
            var attr   = $el.data( 'sc-attr' );
            var defVal = String( $el.data( 'sc-default' ) );
            var onVal  = String( $el.data( 'sc-on' )  || '1' );
            var offVal = String( $el.data( 'sc-off' ) || '0' );
            var val    = $el.is( ':checked' ) ? onVal : offVal;

            if ( ! attr ) return;
            if ( val === defVal ) return;

            parts.push( attr + '="' + val + '"' );
        } );

        // Radio groups
        var radioGroups = {};
        $( '.qdbp-b-radio' ).each( function () {
            var $el  = $( this );
            var attr = $el.data( 'sc-attr' );
            if ( ! attr || radioGroups[ attr ] ) return;
            radioGroups[ attr ] = true;

            var defVal = String( $el.data( 'sc-default' ) );
            var val    = $( 'input[data-sc-attr="' + attr + '"]:checked' ).val() || defVal;

            if ( val !== defVal ) {
                parts.push( attr + '="' + val + '"' );
            }
        } );

        var sc = '[' + SHORTCODE;
        if ( parts.length ) {
            sc += ' ' + parts.join( ' ' );
        }
        sc += ']';

        $( '#qdbp-builder-result' ).val( sc );
    }

    // ── Conditional field visibility ─────────────────────────────────────────
    function updateConditionals() {
        // Checkboxes with data-toggle-target
        $( '.qdbp-b-toggle[type="checkbox"]' ).each( function () {
            var $el     = $( this );
            var target  = $el.data( 'toggle-target' );
            if ( ! target ) return;
            var active = $el.is( ':checked' );
            $( target ).prop( 'hidden', ! active );
        } );

        // Text / number inputs: show target when value is non-empty and non-zero
        $( '.qdbp-b-toggle[type="text"], .qdbp-b-toggle[type="number"]' ).each( function () {
            var $el    = $( this );
            var target = $el.data( 'toggle-target' );
            if ( ! target ) return;
            var val    = $el.val().trim();
            var active = val !== '' && val !== '0';
            $( target ).prop( 'hidden', ! active );
        } );

        // Expiry-msg: show if either expiry_hours or expiry_clicks > 0
        var expHours  = parseInt( $( '#qdbp-b-expiry_hours' ).val(), 10 )  || 0;
        var expClicks = parseInt( $( '#qdbp-b-expiry_clicks' ).val(), 10 ) || 0;
        $( '.qdbp-expiry-msg-field' ).prop( 'hidden', expHours === 0 && expClicks === 0 );
    }

    // ── WP Media picker ──────────────────────────────────────────────────────
    $( document ).on( 'click', '.qdbp-media-pick', function () {
        var targetId = $( this ).data( 'target' );

        if ( typeof wp === 'undefined' || ! wp.media ) return;

        var frame = wp.media( {
            title:    'Select or Upload File',
            button:   { text: 'Use this file' },
            multiple: false,
        } );

        frame.on( 'select', function () {
            var attachment = frame.state().get( 'selection' ).first().toJSON();
            $( '#' + targetId ).val( attachment.url ).trigger( 'input' );
        } );

        frame.open();
    } );

    // ── Copy to clipboard ────────────────────────────────────────────────────
    $( '#qdbp-copy-shortcode' ).on( 'click', function () {
        var $btn = $( this );
        var $ta  = $( '#qdbp-builder-result' );

        $ta[0].select();

        if ( navigator.clipboard && navigator.clipboard.writeText ) {
            navigator.clipboard.writeText( $ta.val() ).then( function () {
                flash( $btn );
            } );
        } else {
            document.execCommand( 'copy' );
            flash( $btn );
        }
    } );

    function flash( $btn ) {
        var orig = $btn.text();
        $btn.text( 'Copied!' ).addClass( 'qdbp-copy-btn--done' );
        setTimeout( function () {
            $btn.text( orig ).removeClass( 'qdbp-copy-btn--done' );
        }, 2000 );
    }

    // ── Wire up all inputs ───────────────────────────────────────────────────
    $( document ).on( 'input change', '.qdbp-b-input, .qdbp-b-checkbox, .qdbp-b-radio', function () {
        updateConditionals();
        buildShortcode();
    } );

    // ── Init ─────────────────────────────────────────────────────────────────
    updateConditionals();
    buildShortcode();

} )( jQuery );
