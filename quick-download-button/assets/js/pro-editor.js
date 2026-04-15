/**
 * Quick Download Button — Block Editor Extension
 *
 * Uses two WordPress block filters:
 *  1. blocks.registerBlockType  — adds Pro attributes to the download-button block
 *  2. editor.BlockEdit          — injects a "Gates" panel into the block sidebar
 *
 * No build step required — uses globally available wp.* APIs.
 */
( function () {
    'use strict';

    var el                      = wp.element.createElement;
    var Fragment                = wp.element.Fragment;
    var __                      = wp.i18n.__;
    var addFilter               = wp.hooks.addFilter;
    var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
    var InspectorControls       = wp.blockEditor.InspectorControls;
    var PanelBody               = wp.components.PanelBody;
    var PanelRow                = wp.components.PanelRow;
    var ToggleControl           = wp.components.ToggleControl;
    var TextControl             = wp.components.TextControl;
    var NumberControl           = wp.components.__experimentalNumberControl;

    var TARGET = 'quick-download-button/download-button';

    // Global default for showCount — set server-side via wp_localize_script.
    var globalShowCount = ( typeof qdbp_editor_data !== 'undefined' && qdbp_editor_data.show_download_count ) ? true : false;

    // ── 1. Register Pro attributes ─────────────────────────────────────────
    addFilter(
        'blocks.registerBlockType',
        'qdbp/register-attributes',
        function ( settings, name ) {
            if ( name !== TARGET ) return settings;
            return Object.assign( {}, settings, {
                attributes: Object.assign( {}, settings.attributes, {
                    // Email Gate
                    emailGate:       { type: 'boolean', default: false },
                    emailGateLabel:  { type: 'string',  default: 'Enter your email to download' },
                    emailGateBtnTxt: { type: 'string',  default: 'Get Download Link' },
                    // Passcode
                    passcode:        { type: 'string',  default: '' },
                    passcodeLabel:   { type: 'string',  default: 'Enter passcode to download' },
                    // Download Limit
                    dlLimit:         { type: 'number',  default: 0 },
                    dlLimitWindow:   { type: 'number',  default: 24 },
                    dlLimitMsg:      { type: 'string',  default: 'You have reached the download limit. Please try again later.' },
                    // Download Counter
                    showCount:       { type: 'boolean', default: globalShowCount },
                    // Expiring Links
                    expiryHours:     { type: 'number',  default: 0 },
                    expiryClicks:    { type: 'number',  default: 0 },
                    expiryMsg:       { type: 'string',  default: 'This download link has expired.' },
                } ),
            } );
        }
    );

    // ── 2. Add sidebar panel ───────────────────────────────────────────────
    var withProControls = createHigherOrderComponent( function ( BlockEdit ) {
        return function ( props ) {
            if ( props.name !== TARGET ) return el( BlockEdit, props );

            var attrs = props.attributes;
            var set   = props.setAttributes;

            var divider = el( 'hr', { style: { margin: '12px 0', border: 'none', borderTop: '1px solid #e2e8f0' } } );

            return el(
                Fragment, null,
                el( BlockEdit, props ),
                el( InspectorControls, null,
                    el( PanelBody,
                        { title: __( '⚡ Gates', 'quick-download-button' ), initialOpen: false },

                        // ── Email Gate ──────────────────────────────────────
                        el( ToggleControl, {
                            label:    __( 'Email gate', 'quick-download-button' ),
                            help:     attrs.emailGate
                                ? __( 'Visitor must enter their email before downloading.', 'quick-download-button' )
                                : __( 'No email required.', 'quick-download-button' ),
                            checked:  attrs.emailGate,
                            onChange: function ( v ) { set( { emailGate: v } ); },
                        } ),

                        attrs.emailGate && el( TextControl, {
                            label:    __( 'Gate label', 'quick-download-button' ),
                            value:    attrs.emailGateLabel,
                            onChange: function ( v ) { set( { emailGateLabel: v } ); },
                        } ),

                        attrs.emailGate && el( TextControl, {
                            label:    __( 'Submit button text', 'quick-download-button' ),
                            value:    attrs.emailGateBtnTxt,
                            onChange: function ( v ) { set( { emailGateBtnTxt: v } ); },
                        } ),

                        divider,

                        // ── Passcode ────────────────────────────────────────
                        el( TextControl, {
                            label:    __( 'Passcode', 'quick-download-button' ),
                            help:     attrs.passcode
                                ? __( 'Visitor must enter this code to download.', 'quick-download-button' )
                                : __( 'Leave blank to disable.', 'quick-download-button' ),
                            value:    attrs.passcode,
                            type:     'password',
                            onChange: function ( v ) { set( { passcode: v } ); },
                        } ),

                        attrs.passcode && el( TextControl, {
                            label:    __( 'Passcode label', 'quick-download-button' ),
                            value:    attrs.passcodeLabel,
                            onChange: function ( v ) { set( { passcodeLabel: v } ); },
                        } ),

                        divider,

                        // ── Download Limit ──────────────────────────────────
                        el( NumberControl, {
                            label:    __( 'Download limit', 'quick-download-button' ),
                            help:     __( 'Max downloads per IP / user. 0 = unlimited.', 'quick-download-button' ),
                            value:    attrs.dlLimit,
                            min:      0,
                            onChange: function ( v ) { set( { dlLimit: parseInt( v, 10 ) || 0 } ); },
                        } ),

                        attrs.dlLimit > 0 && el( NumberControl, {
                            label:    __( 'Time window (hours)', 'quick-download-button' ),
                            help:     __( 'Rolling window for the limit count.', 'quick-download-button' ),
                            value:    attrs.dlLimitWindow,
                            min:      1,
                            onChange: function ( v ) { set( { dlLimitWindow: parseInt( v, 10 ) || 24 } ); },
                        } ),

                        attrs.dlLimit > 0 && el( TextControl, {
                            label:    __( 'Limit message', 'quick-download-button' ),
                            value:    attrs.dlLimitMsg,
                            onChange: function ( v ) { set( { dlLimitMsg: v } ); },
                        } ),

                        divider,

                        // ── Download Counter ─────────────────────────────────
                        el( ToggleControl, {
                            label:    __( 'Show download count', 'quick-download-button' ),
                            help:     attrs.showCount
                                ? __( 'Total downloads shown below this button.', 'quick-download-button' )
                                : __( 'Download count hidden for this button.', 'quick-download-button' ),
                            checked:  attrs.showCount,
                            onChange: function ( v ) { set( { showCount: v } ); },
                        } ),

                        divider,

                        // ── Expiring Links ───────────────────────────────────
                        el( NumberControl, {
                            label:    __( 'Link expiry (hours)', 'quick-download-button' ),
                            help:     attrs.expiryHours > 0
                                ? __( 'Each token expires after this many hours.', 'quick-download-button' )
                                : __( 'No time limit. Set > 0 to enable.', 'quick-download-button' ),
                            value:    attrs.expiryHours,
                            min:      0,
                            onChange: function ( v ) { set( { expiryHours: parseInt( v, 10 ) || 0 } ); },
                        } ),

                        el( NumberControl, {
                            label:    __( 'Max clicks per token', 'quick-download-button' ),
                            help:     attrs.expiryClicks > 0
                                ? __( 'Token is invalidated after this many downloads.', 'quick-download-button' )
                                : __( 'Unlimited clicks. Set > 0 to enable.', 'quick-download-button' ),
                            value:    attrs.expiryClicks,
                            min:      0,
                            onChange: function ( v ) { set( { expiryClicks: parseInt( v, 10 ) || 0 } ); },
                        } ),

                        ( attrs.expiryHours > 0 || attrs.expiryClicks > 0 ) && el( TextControl, {
                            label:    __( 'Expiry message', 'quick-download-button' ),
                            help:     __( 'Shown when the link has expired or click limit is reached.', 'quick-download-button' ),
                            value:    attrs.expiryMsg,
                            onChange: function ( v ) { set( { expiryMsg: v } ); },
                        } )
                    )
                )
            );
        };
    }, 'withProControls' );

    addFilter( 'editor.BlockEdit', 'qdbp/pro-controls', withProControls );

} )();
