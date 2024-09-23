document.addEventListener('DOMContentLoaded', function () {
    "use strict";
    //Bail execution if quickDownloadButton is not on page 
    let elem_button_exist_on_page = document.querySelector('.g-btn.f-l');
    if (null === elem_button_exist_on_page || '' === elem_button_exist_on_page) return;


    let quickDownloadButton = document.querySelectorAll('.g-btn.f-l');

    quickDownloadButton.forEach(qdButton => {

        qdButton.addEventListener('click', function (event) {
            event.preventDefault();

            let attachment_id = qdButton.getAttribute('data-attachment-id');
            let download_url = qdButton.getAttribute('data-page-id');
            let haveExternal = qdButton.getAttribute('data-have-external');
            let targetBlank = qdButton.getAttribute('data-target-blank');
            let pid = qdButton.getAttribute('data-id');
            let waitTime = qdButton.getAttribute('data-spinner');
            let msg = qdButton.getAttribute('data-msg');
            let validate = qdButton.getAttribute('data-validate');
            let validateMsg = qdButton.getAttribute('data-validate-msg');
            let member = qdButton.getAttribute('data-member');
            let seconds;


            var error = {};
            var haveError = false;

            // If no download link found in button
            if (
                'false' == haveExternal
                && null == attachment_id
            ) {
                haveError = true;
                error['no-link'] = 'No link found in button';
            }

            if (
                '' == qdButton.getAttribute('data-external-url')
                && null == attachment_id
            ) {
                haveError = true;
                error['no-link'] = 'No link found for external URL';
            }

            // Getenberg validate member - When validating in gutenberg, we want to make sure that the validate from shortcode is not set and only member validate is set from gutenberg

            if (null != quick_download_object) {
                let userRoles = quick_download_object['qdbn_user_roles'];
                if (null != member && null == validate) {
    
                    if (userRoles.includes(member)) {
                        validate = '1';
                       
                    } else {
                        validateMsg = member + ' account required.';
                        validate = '0';
                       
                    }
    
                    if (document.body.classList.contains('logged-in') && member == 'loggedin') {
                        validate = '1';
                    } else {
                        if ( member == 'loggedin' ) {
                            validateMsg = 'You must be logged in to download.';
                            validate = '0';
                        }
                        
                    }
                    // Reset it if no condition is required.
                    if ('0' == member) {
                        validate = '1';
                    }
                }
            }

            // Validate with Shortcode - We used validate attribute for shortcode and member attribute for gutenberg. Switched member to validate above.

            if (
                null != validate && '0' == validate
            ) {
                haveError = true;
                error['no-link'] = validateMsg;
            }



            if (haveError) {
                let errContainer = document.createElement('p');
                errContainer.setAttribute('class', 'qdb-info qdb-error');
                errContainer.innerHTML = error['no-link'];

                let btnContainer = this.parentNode.parentNode;

                if (btnContainer.classList.contains('qdbn')) {

                    if (btnContainer.nextElementSibling.classList.contains('qdb-btn-info')) {

                        btnContainer.nextElementSibling.append(errContainer);
                    } else {
                        btnContainer.after(errContainer);
                    }

                }
                this.disabled = true;

                return false;
            }



            // Create loading container
            let loadingContainer = document.createElement('div');
            loadingContainer.setAttribute('class', 'download-loading-container');

            // Create loader spinner
            let counterContainer = document.createElement('div');
            counterContainer.setAttribute('class', 'counterContainer');
            let loader = document.createElement('div');
            loader.setAttribute('class', 'qdbu-loader');

            // Get the button style hex color. To be used for the loading animation bar color. //
            let btnColor = null;
            let extractedHexCodes = null;
            btnColor = this.getAttribute('style');
            let regularExpression = /#(?:[0-9a-fA-F]{3}){1,2}/g
            // btw: this is the same as writing RegExp(/#(?:[0-9a-fA-F]{3}){1,2}/, 'g')
            if (btnColor) {
                extractedHexCodes = btnColor.match(regularExpression);
                if (null !== extractedHexCodes)
                    loader.style.borderTopColor = extractedHexCodes[0];
            }


            counterContainer.append(loader);

            loadingContainer.append(counterContainer);


            if ("" !== msg && null !== msg) {

                // Create message
                let info = document.createElement('div');
                info.setAttribute('class', 'loading-msg');
                info.innerHTML = `<span class="msg">${msg}</span>`;

                loadingContainer.classList.add('have-waiting-message');

                loadingContainer.append(info)
            }

            let container = qdButton.parentNode;

            let manualWaitDuration = parseInt(waitTime + '00');

            switch (waitTime) {
                case '10':
                    seconds = 1000;
                    break;
                case '20':
                    seconds = 2000;
                    break;
                case '30':
                    seconds = 3000;
                    break;
                default:
                    seconds = manualWaitDuration;
                    break;
            }


            attachment_id = parseInt(attachment_id) - parseInt(download_url);

            let download_external_url = qdButton.getAttribute('data-external-url');

            function createLink(linkType, linkUrl, currentLoader = '', currentloadingContainer = '') {
                let haveWaitTime = true;
                if (isNaN(parseInt(waitTime))) {
                    haveWaitTime = false;
                }
                if (parseInt(waitTime) !== 0 && haveWaitTime) {

                    container.prepend(loadingContainer)
                    let countdownMsg = document.createElement('span');
                    countdownMsg.setAttribute('class', 'countdownMsg');

                    let currentL = '' == currentLoader ? document.querySelector('.counterContainer') : currentLoader;
                    currentL.append(countdownMsg);

                    timer(parseInt(waitTime), countdownMsg);


                } else {

                    extFileUrl(linkType, linkUrl)
                }

                function timer(timeInMins, output = '') {
                    let done = false;

                    if ('' != output) {
                        output.innerHTML = `${timeInMins}`;
                    }

                    let time = timeInMins * 1;

                    let x = setInterval(function () {
                        let seconds = time % (timeInMins + 1);

                        seconds = seconds < 10 ? seconds : seconds;

                        if (seconds > -1) {
                            // Output.
                            let sec = `${seconds}`;
                            if ('' != output) {

                                output.innerHTML = `${sec}`;

                            }
                            time--;
                        } else {
                            clearInterval(x);
                            done = true;

                            let currentLoadingC = '' == currentloadingContainer ? document.querySelector('.download-loading-container') : currentloadingContainer;
                            currentLoadingC.remove();

                            extFileUrl(linkType, linkUrl)
                        }

                        return done;

                    }, 1000);

                }
            }


            if (null !== download_external_url & "" !== download_external_url) {
                if ("false" !== haveExternal && "" !== download_external_url) {
                    // From gutenberg
                    createLink('external_link', download_external_url, counterContainer, loadingContainer)
                } else {

                    if (download_external_url.indexOf('?') === -1) {
                        download_external_url += '?download';
                    }


                    if (targetBlank == "false") {
                        window.open(download_external_url, '_self');
                    } else {
                        window.open(download_external_url, '_blank');
                    }

                }

                return;

            } else {
                // This is internal Download
                createLink('aid', attachment_id, counterContainer, loadingContainer)
            }

            /* This function check nonce and open the download url in a new tab. */
            function extFileUrl($type, $url) {
                var data = {
                    'action': 'qdbu_download_ajax_referer',
                    'security': quick_download_object.security
                }

                if (data.security) {

                    let url = `${quick_download_object.redirecturl}?_wpnonce=${data.security}&${$type}=${$url}`;

                    if (targetBlank == "false") {
                        window.open(url, '_self');
                    } else {
                        window.open(url, '_blank');
                    }

                }
            }

        })
    })

})
