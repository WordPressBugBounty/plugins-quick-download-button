/**
 * Quick Download Button — Frontend
 *
 * Listens for the cancelable 'qdb-before-download' custom event fired by
 * the free plugin before every download. When a gate is active on the
 * clicked button, this script intercepts the event, shows the appropriate
 * gate UI, and calls event.detail.proceed() once the visitor clears it.
 *
 * Gate detection: reads data attributes set server-side by each module.
 *   data-qdb-email-gate      → email capture form
 *   data-qdb-passcode        → passcode input form
 *   data-qdb-limit-reached   → hard block with message
 *
 * Gate forms are injected into the button wrapper by PHP (shortcode).
 * For Gutenberg blocks (static save, no PHP injection point) the forms
 * are created dynamically by this script.
 *
 * Window.open + wait-time fix
 * ───────────────────────────
 * When the free plugin's countdown (wait > 0) is active, fireDownload() is
 * called from setInterval — not a direct user gesture. Browsers block
 * window.open() originating from async/timer callbacks. The fix is to
 * pre-open the download window during the last real user gesture (the gate
 * form submit, or the original button click for expiry-only buttons), store
 * the WindowProxy on the button element, then navigate it after AJAX resolves.
 * Browsers allow navigation on an already-opened WindowProxy from async code.
 */
( function () {
    'use strict';

    if ( typeof qdbp_data === 'undefined' ) return;

    // ── Cookie helper ──────────────────────────────────────────────────────
    function getCookie( name ) {
        var match = document.cookie.match(
            new RegExp( '(?:^|; )' + name.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ) + '=([^;]*)' )
        );
        return match ? decodeURIComponent( match[ 1 ] ) : '';
    }

    // ── Cache-compat: strip gate attrs and refresh counts on page load ─────
    //
    // Full-page caches bake server-side state into HTML at cache-build time.
    // On subsequent visits the cached HTML may show gates that the visitor
    // already cleared, or a stale download count.  This block corrects both
    // before any interaction occurs.
    //
    document.addEventListener( 'DOMContentLoaded', function () {

        // 1. Email gate — correct cached HTML based on the unlock cookie.
        //
        //    Standard mode  (no data-qdb-always-show): remove the gate attr
        //    so the button behaves as if freshly rendered for a returning visitor.
        //
        //    Always-show mode (data-qdb-always-show="1"): keep the gate attr
        //    but switch the gate to its returning-visitor (thank-you) state and
        //    mark the button with data-qdb-email-returning="1".
        if ( getCookie( 'qdbp_email_unlocked' ) ) {
            document.querySelectorAll( '[data-qdb-email-gate]' ).forEach( function ( btn ) {
                var wrapper = btn.closest( '[data-plugin-name="qdbn"]' );
                var gate    = wrapper && wrapper.parentNode
                    ? wrapper.parentNode.querySelector( '.qdbp-email-gate' )
                    : null;
                var alwaysShow = gate && gate.getAttribute( 'data-qdb-always-show' ) === '1';

                if ( alwaysShow ) {
                    // Mark button as returning so handleEmailGate shows the ty state.
                    btn.setAttribute( 'data-qdb-email-returning', '1' );
                    // Ensure the correct sub-state is visible inside the gate.
                    var newState = gate.querySelector( '.qdbp-email-new-state' );
                    var tyState  = gate.querySelector( '.qdbp-email-returning-state' );
                    if ( newState ) newState.setAttribute( 'hidden', '' );
                    if ( tyState  ) tyState.removeAttribute( 'hidden' );
                } else {
                    // Standard mode — bypass the gate entirely.
                    btn.removeAttribute( 'data-qdb-email-gate' );
                    if ( gate ) gate.setAttribute( 'hidden', '' );
                }
            } );
        }

        // 2. Passcode gate — unlock cookie is per-button-id.
        document.querySelectorAll( '[data-qdb-passcode]' ).forEach( function ( btn ) {
            var btnId = btn.getAttribute( 'data-qdb-btn-id' ) || '';
            if ( btnId && getCookie( 'qdbp_passcode_' + btnId ) ) {
                btn.removeAttribute( 'data-qdb-passcode' );
                var wrapper = btn.closest( '[data-plugin-name="qdbn"]' );
                if ( wrapper && wrapper.parentNode ) {
                    var gate = wrapper.parentNode.querySelector( '.qdbp-passcode-gate' );
                    if ( gate ) gate.setAttribute( 'hidden', '' );
                }
            }
        } );

        // 3. Download counter — refresh stale counts on cached pages.
        document.querySelectorAll( '.qdbp-dl-count' ).forEach( function ( countEl ) {
            // Traverse to find the button in the sibling qdbn wrapper.
            var infoEl  = countEl.parentElement; // <quick-download-button-info>
            var qdbnEl  = infoEl ? infoEl.previousElementSibling : null;
            var btn     = qdbnEl ? qdbnEl.querySelector( '.g-btn.f-l' ) : null;
            var btnId   = btn ? ( btn.getAttribute( 'data-qdb-btn-id' ) || '' ) : '';
            if ( ! btnId ) return;

            ajax( 'qdbp_get_count', { btn_id: btnId }, function ( data ) {
                var label = ( qdbp_data.count_label || '{count} downloads' )
                    .replace( '{count}', Number( data.count ).toLocaleString() );
                countEl.textContent = label;
            }, function () { /* silent — stale value stays */ } );
        } );
    } );

    // ── AJAX helper ────────────────────────────────────────────────────────
    function ajax( action, data, onSuccess, onError ) {
        var body = new FormData();
        body.append( 'action',   action );
        body.append( 'security', qdbp_data.security );
        Object.keys( data ).forEach( function ( k ) { body.append( k, data[ k ] ); } );

        fetch( qdbp_data.ajaxurl, { method: 'POST', body: body } )
            .then( function ( r ) { return r.json(); } )
            .then( function ( res ) {
                if ( res.success ) {
                    onSuccess( res.data );
                } else {
                    onError( ( res.data && res.data.message ) ? res.data.message : 'Something went wrong.' );
                }
            } )
            .catch( function () { onError( 'Network error. Please try again.' ); } );
    }

    // ── DOM helpers ────────────────────────────────────────────────────────

    /** Return the outermost wrapper element for the button. */
    function getWrapper( button ) {
        var qdbnEl = button.closest( '[data-plugin-name="qdbn"]' );
        return qdbnEl ? qdbnEl.parentNode : null;
    }

    /** Find an existing gate element or create one dynamically. */
    function getOrCreateGate( button, gateClass, buildFn ) {
        var wrapper = getWrapper( button );
        if ( wrapper ) {
            var existing = wrapper.querySelector( '.' + gateClass );
            if ( existing ) return existing;
        }
        // Gutenberg / no PHP-injected form — build it dynamically
        var gate = buildFn();
        var anchor = button.closest( '.qdbn-download-button-inner' ) || button.parentNode;
        anchor.after( gate );
        return gate;
    }

    function showGate( gateEl ) {
        gateEl.removeAttribute( 'hidden' );
        var input = gateEl.querySelector( 'input' );
        if ( input ) input.focus();
    }

    function hideGate( gateEl ) {
        gateEl.setAttribute( 'hidden', '' );
    }

    function setError( gateEl, message ) {
        var errEl = gateEl.querySelector( '.qdbp-gate-error' );
        if ( ! errEl ) return;
        errEl.textContent = message;
        errEl.removeAttribute( 'hidden' );
    }

    function clearError( gateEl ) {
        var errEl = gateEl.querySelector( '.qdbp-gate-error' );
        if ( errEl ) { errEl.textContent = ''; errEl.setAttribute( 'hidden', '' ); }
    }

    function setSubmitState( btn, loading ) {
        btn.disabled = loading;
        if ( loading ) {
            btn.dataset.origText = btn.textContent;
            btn.textContent = '…';
        } else {
            btn.textContent = btn.dataset.origText || btn.textContent;
        }
    }

    // ── Download URL builder ───────────────────────────────────────────────
    //
    // Mirrors the free plugin's extFileUrl() URL construction so we can
    // navigate a pre-opened window without calling proceed() (which would
    // call window.open() again and get blocked).
    //
    function buildDownloadUrl( linkType, linkUrl, btnId, token ) {
        if ( typeof quick_download_object === 'undefined' || ! quick_download_object.redirecturl ) {
            return null;
        }
        var url = quick_download_object.redirecturl
            + '?_wpnonce=' + quick_download_object.security
            + '&' + linkType + '=' + linkUrl;
        if ( btnId ) url += '&qdb_btn_id=' + encodeURIComponent( btnId );
        if ( token ) url += '&qdbp_token='  + encodeURIComponent( token );
        return url;
    }

    // ── Pre-open window helpers ────────────────────────────────────────────
    //
    // Call preOpenWindow() during a user gesture (click / form submit) so the
    // browser issues a WindowProxy. Later, navigatePreOpenedWindow() sets its
    // href from an async callback — browsers allow this because the window was
    // opened synchronously during a user gesture.
    //
    function preOpenWindow( button ) {
        var targetBlank = button.getAttribute( 'data-target-blank' );
        var target = ( targetBlank === 'false' ) ? '_self' : '_blank';
        button._qdbpDlWin = window.open( '', target );
        return button._qdbpDlWin;
    }

    function navigatePreOpenedWindow( button, url ) {
        var win = button._qdbpDlWin;
        button._qdbpDlWin = null;
        if ( win ) {
            win.location.href = url;
        } else if ( url ) {
            // Fallback: pre-open was not available (e.g. popup blocker closed it).
            var targetBlank = button.getAttribute( 'data-target-blank' );
            window.open( url, ( targetBlank === 'false' ) ? '_self' : '_blank' );
        }
    }

    function closePreOpenedWindow( button ) {
        var win = button._qdbpDlWin;
        button._qdbpDlWin = null;
        // Close only if it is a new tab, not _self.
        if ( win && win !== window ) {
            win.close();
        }
    }

    // ── Email Gate ─────────────────────────────────────────────────────────

    /**
     * Build a basic email gate element for dynamic injection (Gutenberg blocks
     * without a PHP-rendered gate form).
     *
     * @param {boolean} alwaysShow  Include the always-show returning-visitor state.
     * @param {boolean} isReturning Render with the returning state visible.
     */
    function buildEmailGateEl( alwaysShow, isReturning ) {
        var gate = document.createElement( 'div' );
        gate.className = 'qdbp-gate qdbp-email-gate';

        var newStateOpen  = '';
        var newStateClose = '';
        var tyState       = '';

        if ( alwaysShow ) {
            gate.setAttribute( 'data-qdb-always-show', '1' );
            newStateOpen  = '<div class="qdbp-email-new-state"' + ( isReturning ? ' hidden' : '' ) + '>';
            newStateClose = '</div>';
            tyState =
                '<div class="qdbp-email-returning-state"' + ( isReturning ? '' : ' hidden' ) + '>' +
                    '<p class="qdbp-gate-label">Welcome back! Click below to continue your download.</p>' +
                    '<button type="button" class="qdbp-gate-submit qdbp-email-ty-proceed">Continue Download</button>' +
                '</div>';
        }

        gate.innerHTML =
            newStateOpen +
            '<form class="qdbp-email-form">' +
                '<label class="qdbp-gate-label">Enter your email to download</label>' +
                '<div class="qdbp-email-field-row">' +
                    '<input type="email" class="qdbp-email-input" placeholder="you@example.com" required />' +
                    '<button type="submit" class="qdbp-gate-submit">Get Download Link</button>' +
                '</div>' +
                '<div class="qdbp-gate-error" hidden></div>' +
            '</form>' +
            newStateClose +
            tyState;

        return gate;
    }

    /**
     * Shared helper: proceed after a gate is cleared.
     * Handles both expiry (uses pre-opened window via wrapProceedWithToken)
     * and plain downloads (navigates the pre-opened window directly).
     */
    function proceedAfterGate( button, btnId, proceed, linkType, linkUrl ) {
        if ( button.hasAttribute( 'data-qdb-limit-reached' ) ) {
            closePreOpenedWindow( button );
            handleLimitReached( button );
            return;
        }
        if ( button.hasAttribute( 'data-qdb-expiry' ) ) {
            proceed( linkType, linkUrl ); // wrapProceedWithToken uses button._qdbpDlWin
        } else {
            var url = buildDownloadUrl( linkType, linkUrl, btnId );
            if ( url ) {
                navigatePreOpenedWindow( button, url );
            } else {
                closePreOpenedWindow( button );
                proceed( linkType, linkUrl );
            }
        }
    }

    function handleEmailGate( button, btnId, proceed, linkType, linkUrl ) {
        var isReturning = button.getAttribute( 'data-qdb-email-returning' ) === '1';
        var alwaysShow  = isReturning; // returning implies always-show is on

        var gateEl = getOrCreateGate( button, 'qdbp-email-gate', function () {
            return buildEmailGateEl( alwaysShow, isReturning );
        } );

        // Sync sub-state visibility in case the cached HTML is out of phase.
        if ( gateEl.getAttribute( 'data-qdb-always-show' ) === '1' ) {
            var newState = gateEl.querySelector( '.qdbp-email-new-state' );
            var tyState  = gateEl.querySelector( '.qdbp-email-returning-state' );
            if ( isReturning ) {
                if ( newState ) newState.setAttribute( 'hidden', '' );
                if ( tyState  ) tyState.removeAttribute( 'hidden' );
            } else {
                if ( newState ) newState.removeAttribute( 'hidden' );
                if ( tyState  ) tyState.setAttribute( 'hidden', '' );
            }
        }

        clearError( gateEl );
        showGate( gateEl );

        // ── Returning visitor (always-show mode) ───────────────────────────
        // Show the thank-you state. The "Continue Download" button is the
        // user gesture — pre-open the window here before any async work.
        if ( isReturning ) {
            var tyBtn = gateEl.querySelector( '.qdbp-email-ty-proceed' );
            if ( ! tyBtn ) return;

            var newTyBtn = tyBtn.cloneNode( true );
            tyBtn.replaceWith( newTyBtn );

            newTyBtn.addEventListener( 'click', function () {
                preOpenWindow( button );      // user gesture — safe to open
                hideGate( gateEl );
                proceedAfterGate( button, btnId, proceed, linkType, linkUrl );
            } );
            return;
        }

        // ── New visitor — standard email form ─────────────────────────────
        var oldForm = gateEl.querySelector( '.qdbp-email-form' );
        var form    = oldForm.cloneNode( true );
        oldForm.replaceWith( form );

        var submitBtn = form.querySelector( '.qdbp-gate-submit' );

        form.addEventListener( 'submit', function ( e ) {
            e.preventDefault();
            var emailInput = form.querySelector( '.qdbp-email-input' );
            var email      = emailInput ? emailInput.value.trim() : '';
            if ( ! email ) return;

            // Pre-open the download window NOW while we are still inside the
            // form submit user-gesture so it stays valid through the AJAX call.
            preOpenWindow( button );

            setSubmitState( submitBtn, true );
            clearError( gateEl );

            ajax(
                'qdbp_email_gate',
                { email: email, btn_id: btnId },
                function () {
                    // ── AJAX success ─────────────────────────────────────
                    var isAlwaysShow = gateEl.getAttribute( 'data-qdb-always-show' ) === '1';

                    if ( isAlwaysShow ) {
                        // Transition the gate to the returning state for any
                        // subsequent clicks within this page session.
                        var ns = gateEl.querySelector( '.qdbp-email-new-state' );
                        var ts = gateEl.querySelector( '.qdbp-email-returning-state' );
                        if ( ns ) ns.setAttribute( 'hidden', '' );
                        if ( ts ) ts.removeAttribute( 'hidden' );
                        button.setAttribute( 'data-qdb-email-returning', '1' );
                        // Gate stays visible but in returning state — hide it
                        // for this interaction and let the click re-show it.
                        hideGate( gateEl );
                    } else {
                        hideGate( gateEl );
                        button.removeAttribute( 'data-qdb-email-gate' );
                    }

                    proceedAfterGate( button, btnId, proceed, linkType, linkUrl );
                },
                function ( msg ) {
                    setSubmitState( submitBtn, false );
                    setError( gateEl, msg );
                    closePreOpenedWindow( button );
                }
            );
        } );
    }

    // ── Passcode Gate ──────────────────────────────────────────────────────

    function buildPasscodeGateEl() {
        var gate = document.createElement( 'div' );
        gate.className = 'qdbp-gate qdbp-passcode-gate';
        gate.innerHTML =
            '<form class="qdbp-passcode-form">' +
                '<label class="qdbp-gate-label">Enter passcode to download</label>' +
                '<div class="qdbp-passcode-field-row">' +
                    '<input type="password" class="qdbp-passcode-input" placeholder="••••••" required />' +
                    '<button type="submit" class="qdbp-gate-submit">Unlock</button>' +
                '</div>' +
                '<div class="qdbp-gate-error" hidden></div>' +
            '</form>';
        return gate;
    }

    function handlePasscodeGate( button, btnId, proceed, linkType, linkUrl ) {
        var gateEl = getOrCreateGate( button, 'qdbp-passcode-gate', buildPasscodeGateEl );
        clearError( gateEl );
        showGate( gateEl );

        var oldForm = gateEl.querySelector( '.qdbp-passcode-form' );
        var form    = oldForm.cloneNode( true );
        oldForm.replaceWith( form );

        var submitBtn  = form.querySelector( '.qdbp-gate-submit' );
        var codeInput  = form.querySelector( '.qdbp-passcode-input' );

        form.addEventListener( 'submit', function ( e ) {
            e.preventDefault();
            var code = codeInput ? codeInput.value.trim() : '';
            if ( ! code ) return;

            // Pre-open during user gesture — same rationale as email gate.
            preOpenWindow( button );

            setSubmitState( submitBtn, true );
            clearError( gateEl );

            ajax(
                'qdbp_verify_passcode',
                { passcode: code, btn_id: btnId },
                function () {
                    hideGate( gateEl );
                    button.removeAttribute( 'data-qdb-passcode' );

                    if ( button.hasAttribute( 'data-qdb-limit-reached' ) ) {
                        closePreOpenedWindow( button );
                        handleLimitReached( button );
                        return;
                    }

                    if ( button.hasAttribute( 'data-qdb-expiry' ) ) {
                        // wrapProceedWithToken will use button._qdbpDlWin.
                        proceed( linkType, linkUrl );
                    } else {
                        var url = buildDownloadUrl( linkType, linkUrl, btnId );
                        if ( url ) {
                            navigatePreOpenedWindow( button, url );
                        } else {
                            closePreOpenedWindow( button );
                            proceed( linkType, linkUrl );
                        }
                    }
                },
                function ( msg ) {
                    setSubmitState( submitBtn, false );
                    setError( gateEl, msg );
                    closePreOpenedWindow( button );
                    if ( codeInput ) { codeInput.value = ''; codeInput.focus(); }
                }
            );
        } );
    }

    // ── Download Limit ─────────────────────────────────────────────────────

    function handleLimitReached( button ) {
        var msg = button.getAttribute( 'data-qdb-limit-msg' ) ||
            'You have reached the download limit. Please try again later.';

        // Remove any existing message first
        var wrapper = getWrapper( button );
        if ( wrapper ) {
            var prev = wrapper.querySelector( '.qdbp-limit-msg' );
            if ( prev ) prev.remove();
        }

        var errEl       = document.createElement( 'p' );
        errEl.className = 'qdb-info qdb-error qdbp-limit-msg';
        errEl.textContent = msg;

        var qdbnEl = button.closest( '[data-plugin-name="qdbn"]' );
        var infoEl = qdbnEl ? qdbnEl.nextElementSibling : null;

        if ( infoEl && infoEl.classList.contains( 'qdb-btn-info' ) ) {
            infoEl.appendChild( errEl );
        } else if ( qdbnEl ) {
            qdbnEl.after( errEl );
        }

        button.disabled = true;
    }

    // ── Expiring Links ─────────────────────────────────────────────────────

    /**
     * Intercept the proceed call to fetch a signed token first, then build
     * the download URL with &qdbp_token=TOKEN appended.
     *
     * If button._qdbpDlWin was set by a gate handler (or the click listener
     * below), it is used for navigation so no second window.open() is needed.
     */
    function wrapProceedWithToken( button, btnId, origProceed ) {
        return function ( linkType, linkUrl ) {
            // Disable button for the duration of the AJAX call to prevent
            // double-click from generating multiple tokens.
            button.disabled = true;

            ajax(
                'qdbp_get_token',
                { btn_id: btnId },
                function ( data ) {
                    button.disabled = false;
                    var url = buildDownloadUrl( linkType, linkUrl, btnId, data.token );
                    if ( url ) {
                        if ( button._qdbpDlWin ) {
                            // Use the window pre-opened by the gate submit or click handler.
                            navigatePreOpenedWindow( button, url );
                        } else {
                            var targetBlank = button.getAttribute( 'data-target-blank' );
                            window.open( url, ( targetBlank === 'false' ) ? '_self' : '_blank' );
                        }
                    } else {
                        closePreOpenedWindow( button );
                        origProceed( linkType, linkUrl );
                    }
                },
                function () {
                    button.disabled = false;
                    closePreOpenedWindow( button );
                    // Token generation failed — fall through without token
                    // (server will block if btn has strict expiry enforcement).
                    origProceed( linkType, linkUrl );
                }
            );
        };
    }

    // ── Expiry-only click pre-opener ───────────────────────────────────────
    //
    // For buttons that have expiry but no gate, the download window must be
    // pre-opened during the original button click (the only user gesture).
    // Gate buttons skip this — their form submit handler calls preOpenWindow().
    //
    document.addEventListener( 'click', function ( e ) {
        var button = e.target.closest( '[data-qdb-expiry]' );
        if ( ! button ) return;
        // Gates handle their own pre-open on form submit.
        if ( button.hasAttribute( 'data-qdb-email-gate' ) ) return;
        if ( button.hasAttribute( 'data-qdb-passcode' )   ) return;
        preOpenWindow( button );
    }, true ); // capture phase — runs before the free plugin's click handler

    // ── Main listener ──────────────────────────────────────────────────────

    document.addEventListener( 'qdb-before-download', function ( e ) {
        var button   = e.detail.button;
        var linkType = e.detail.linkType;
        var linkUrl  = e.detail.linkUrl;
        var btnId    = e.detail.btnId;
        var proceed  = e.detail.proceed;

        // Wrap proceed with token generation BEFORE any gate checks,
        // so the token is appended regardless of which gate clears last.
        if ( button.hasAttribute( 'data-qdb-expiry' ) ) {
            proceed = wrapProceedWithToken( button, btnId, proceed );
        }

        if ( button.hasAttribute( 'data-qdb-email-gate' ) ) {
            e.preventDefault();
            handleEmailGate( button, btnId, proceed, linkType, linkUrl );
            return;
        }

        if ( button.hasAttribute( 'data-qdb-passcode' ) ) {
            e.preventDefault();
            handlePasscodeGate( button, btnId, proceed, linkType, linkUrl );
            return;
        }

        if ( button.hasAttribute( 'data-qdb-limit-reached' ) ) {
            e.preventDefault();
            closePreOpenedWindow( button );
            handleLimitReached( button );
            return;
        }

        // Expiry only (no other gate) — intercept and open with token.
        // button._qdbpDlWin was already set by the click pre-opener above.
        if ( button.hasAttribute( 'data-qdb-expiry' ) ) {
            e.preventDefault();
            proceed( linkType, linkUrl );
        }
    } );

} )();
