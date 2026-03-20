document.addEventListener( 'DOMContentLoaded', function () {
    'use strict';

    const buttons = document.querySelectorAll( '.g-btn.f-l' );
    if ( ! buttons.length ) return;

    buttons.forEach( function ( qdButton ) {

        qdButton.addEventListener( 'click', function ( event ) {
            event.preventDefault();

            // Prevent double-click while a countdown is already running
            if ( qdButton.dataset.qdbRunning ) return;

            // ── data attributes ───────────────────────────────────────────────
            const attachmentId         = qdButton.getAttribute( 'data-attachment-id' );
            const downloadPageId       = qdButton.getAttribute( 'data-page-id' );
            const haveExternal         = qdButton.getAttribute( 'data-have-external' );
            const targetBlank          = qdButton.getAttribute( 'data-target-blank' );
            const waitTime             = qdButton.getAttribute( 'data-spinner' );
            const msg                  = qdButton.getAttribute( 'data-msg' );
            const member               = qdButton.getAttribute( 'data-member' );
            const downloadExternalUrl  = qdButton.getAttribute( 'data-external-url' );
            const usePopup             = qdButton.hasAttribute( 'data-qdb-popup' );
            const popupClosable        = qdButton.getAttribute( 'data-qdb-popup-closable' ) !== '0';
            let   validate             = qdButton.getAttribute( 'data-validate' );
            let   validateMsg          = qdButton.getAttribute( 'data-validate-msg' );

            // Popup content source — sibling div inside the same wrapper
            const qdbnEl    = qdButton.closest( '[data-plugin-name="qdbn"]' );
            const wrapperEl = qdbnEl ? qdbnEl.parentNode : null;
            const popupSrc  = ( usePopup && wrapperEl )
                ? wrapperEl.querySelector( '.qdb-popup-src' )
                : null;
            const popupHtml = popupSrc ? popupSrc.innerHTML : '';

            // ── validation ────────────────────────────────────────────────────
            const error    = {};
            let   haveError = false;

            if ( 'false' === haveExternal && null === attachmentId ) {
                haveError = true;
                error.msg = 'No link found in button';
            }

            if ( '' === downloadExternalUrl && null === attachmentId ) {
                haveError = true;
                error.msg = 'No link found for external URL';
            }

            // Gutenberg member/role check
            if ( typeof quick_download_object !== 'undefined' && quick_download_object ) {
                const userRoles = quick_download_object.qdbn_user_roles;
                if ( null !== member && null === validate ) {
                    validate = userRoles.includes( member ) ? '1' : '0';
                    if ( validate === '0' ) validateMsg = member + ' account required.';

                    if ( document.body.classList.contains( 'logged-in' ) && member === 'loggedin' ) {
                        validate = '1';
                    } else if ( member === 'loggedin' ) {
                        validate    = '0';
                        validateMsg = 'You must be logged in to download.';
                    }

                    if ( '0' === member ) validate = '1';
                }
            }

            if ( null !== validate && '0' === validate ) {
                haveError = true;
                error.msg = validateMsg;
            }

            if ( haveError ) {
                const errEl       = document.createElement( 'p' );
                errEl.className   = 'qdb-info qdb-error';
                errEl.textContent = error.msg;
                const btnContainer = qdButton.parentNode.parentNode;
                if ( btnContainer.classList.contains( 'qdbn' ) ) {
                    const infoEl = btnContainer.nextElementSibling;
                    if ( infoEl && infoEl.classList.contains( 'qdb-btn-info' ) ) {
                        infoEl.append( errEl );
                    } else {
                        btnContainer.after( errEl );
                    }
                }
                qdButton.disabled = true;
                return;
            }

            // ── resolve link type & URL ───────────────────────────────────────
            let linkType, linkUrl;

            if ( null !== downloadExternalUrl && '' !== downloadExternalUrl ) {
                if ( 'false' !== haveExternal && '' !== downloadExternalUrl ) {
                    linkType = 'external_link';
                    linkUrl  = downloadExternalUrl;
                } else {
                    const dlUrl = downloadExternalUrl.indexOf( '?' ) === -1
                        ? downloadExternalUrl + '?download'
                        : downloadExternalUrl;
                    window.open( dlUrl, targetBlank === 'false' ? '_self' : '_blank' );
                    return;
                }
            } else {
                linkType = 'aid';
                linkUrl  = parseInt( attachmentId ) - parseInt( downloadPageId );
            }

            // ── wait time ─────────────────────────────────────────────────────
            const waitInt  = parseInt( waitTime );
            const haveWait = ! isNaN( waitInt ) && waitInt > 0;

            if ( ! haveWait ) {
                extFileUrl( linkType, linkUrl );
                return;
            }

            // Mark button as running to prevent double-click
            qdButton.dataset.qdbRunning = '1';

            // ── inline loading container ──────────────────────────────────────
            const loadingContainer = document.createElement( 'div' );
            loadingContainer.className = 'download-loading-container';

            const counterContainer = document.createElement( 'div' );
            counterContainer.className = 'counterContainer';

            const loader = document.createElement( 'div' );
            loader.className = 'qdbu-loader';

            // Match spinner colour to button background
            const btnStyle  = qdButton.getAttribute( 'style' ) || '';
            const hexMatch  = btnStyle.match( /#(?:[0-9a-fA-F]{3}){1,2}/g );
            if ( hexMatch ) loader.style.borderTopColor = hexMatch[ 0 ];

            counterContainer.append( loader );
            loadingContainer.append( counterContainer );

            if ( msg && '' !== msg ) {
                const info   = document.createElement( 'div' );
                info.className = 'loading-msg';
                info.innerHTML = `<span class="msg">${ msg }</span>`;
                loadingContainer.classList.add( 'have-waiting-message' );
                loadingContainer.append( info );
            }

            const container = qdButton.parentNode;
            container.prepend( loadingContainer );

            const inlineCountdown = document.createElement( 'span' );
            inlineCountdown.className = 'countdownMsg';
            counterContainer.append( inlineCountdown );

            // ── popup modal ───────────────────────────────────────────────────
            let modal          = null;
            let overlay        = null;
            let popupCountdown = null;

            if ( usePopup ) {
                overlay           = document.createElement( 'div' );
                overlay.className = 'qdb-popup-overlay';

                modal             = document.createElement( 'div' );
                modal.className   = 'qdb-popup';
                modal.setAttribute( 'role', 'dialog' );
                modal.setAttribute( 'aria-modal', 'true' );
                modal.setAttribute( 'aria-label', 'Download popup' );

                // Close button — only shown when closable
                const closeBtn      = document.createElement( 'button' );
                closeBtn.className  = 'qdb-popup-close';
                closeBtn.setAttribute( 'aria-label', 'Close popup' );
                closeBtn.innerHTML  = '&times;';
                if ( ! popupClosable ) closeBtn.style.display = 'none';

                // Content area
                const bodyEl      = document.createElement( 'div' );
                bodyEl.className  = 'qdb-popup-body';
                if ( popupHtml ) bodyEl.innerHTML = popupHtml;

                // Footer with countdown
                const footer       = document.createElement( 'div' );
                footer.className   = 'qdb-popup-footer';

                popupCountdown           = document.createElement( 'span' );
                popupCountdown.className = 'qdb-popup-countdown';

                const footerText = document.createElement( 'span' );
                footerText.className = 'qdb-popup-footer-text';
                if ( msg && '' !== msg ) {
                    footerText.innerHTML = `<span class="qdb-popup-msg">${ msg }</span> `;
                }
                footerText.append(
                    document.createTextNode( 'Download starts in ' ),
                    popupCountdown,
                    document.createTextNode( ' second(s).' )
                );
                footer.append( footerText );

                modal.append( closeBtn, bodyEl, footer );
                document.body.append( overlay, modal );
                document.body.style.overflow = 'hidden';

                // Trap focus inside modal (accessibility)
                modal.focus && modal.setAttribute( 'tabindex', '-1' );

                if ( popupClosable ) {
                    // Dismiss handlers — close the visual modal but let the timer run
                    const dismissModal = function () {
                        if ( modal && modal.parentNode )   modal.remove();
                        if ( overlay && overlay.parentNode ) overlay.remove();
                        document.body.style.overflow = '';
                        modal   = null;
                        overlay = null;
                    };
                    closeBtn.addEventListener( 'click', dismissModal );
                    overlay.addEventListener( 'click', dismissModal );

                    // Keyboard: Escape closes modal
                    document.addEventListener( 'keydown', function onEsc( e ) {
                        if ( e.key === 'Escape' ) {
                            dismissModal();
                            document.removeEventListener( 'keydown', onEsc );
                        }
                    } );
                }
            }

            // ── single countdown timer ────────────────────────────────────────
            const countdownEls = [ inlineCountdown ];
            if ( popupCountdown ) countdownEls.push( popupCountdown );

            runTimer( waitInt, countdownEls, function () {
                loadingContainer.remove();
                if ( modal   && modal.parentNode )   modal.remove();
                if ( overlay && overlay.parentNode ) overlay.remove();
                document.body.style.overflow = '';
                delete qdButton.dataset.qdbRunning;
                extFileUrl( linkType, linkUrl );
            } );

            // ── helpers ───────────────────────────────────────────────────────
            function runTimer( seconds, outputs, onComplete ) {
                outputs.forEach( function ( el ) { el.textContent = seconds; } );
                let remaining = seconds;
                const interval = setInterval( function () {
                    remaining--;
                    if ( remaining >= 0 ) {
                        outputs.forEach( function ( el ) { el.textContent = remaining; } );
                    } else {
                        clearInterval( interval );
                        onComplete();
                    }
                }, 1000 );
            }

            function extFileUrl( type, url ) {
                if ( typeof quick_download_object === 'undefined' || ! quick_download_object.security ) return;
                const dlUrl = quick_download_object.redirecturl
                    + '?_wpnonce=' + quick_download_object.security
                    + '&' + type + '=' + url;
                window.open( dlUrl, targetBlank === 'false' ? '_self' : '_blank' );
            }

        } ); // click

    } ); // forEach

} ); // DOMContentLoaded
