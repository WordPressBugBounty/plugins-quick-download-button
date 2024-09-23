document.addEventListener('DOMContentLoaded', function () {

    //Bail execution if button is not on page 
    let elem_button_exist_on_page = document.querySelector('.g-btn.f-l');
    if (null === elem_button_exist_on_page || '' === elem_button_exist_on_page) return;

    document.addEventListener('click', function (event) {

        // If the clicked element doesn't have the right selector, bail
        if (!event.target.matches('.g-btn.f-l')) return;

        event.preventDefault();

        let button = event.target;
        let attachment_id = button.getAttribute('data-attachment-id');
        let download_url = button.getAttribute('data-page-id');
        let haveExternal = button.getAttribute('data-have-external');
        let targetBlank = button.getAttribute('data-target-blank');
        let pid = button.getAttribute('data-id');
        let waitTime = button.getAttribute('data-spinner');
        let Havemsg = button.getAttribute('data-have-external');
        let msg = button.getAttribute('data-msg');
        let seconds;


        // Create loading container
        let loadingContainer = document.createElement('div');
        loadingContainer.setAttribute('class', 'download-loading-container');
        // Create loader spinner
        let counterContainer = document.createElement('div');
        counterContainer.setAttribute('class', 'counterContainer');
        let loader = document.createElement('div');
        loader.setAttribute('class', 'qdbu-loader');
        counterContainer.append(loader)
        loadingContainer.append(counterContainer);

        // Create message
        let info = document.createElement('div');
        info.setAttribute('class', 'loading-msg');
        info.innerHTML = `<span class="msg">${msg}</span>`;
        if ( "" !== msg && null !== msg ) {
            loadingContainer.append(info)
        }

        let container = button.parentNode;

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

        let download_external_url = button.getAttribute('data-external-url');

        function createLink(linkType, linkUrl) {
            let haveWaitTime = true;
            if (isNaN(parseInt(waitTime))) {
                haveWaitTime = false;
            }
            if (parseInt(waitTime) !== 0 && haveWaitTime ) {

                container.append(loadingContainer)
                let countdownMsg = document.createElement('span');
                countdownMsg.setAttribute('class', 'countdownMsg');
                document.querySelector('.counterContainer').append(countdownMsg);

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
                    //let minutes = Math.floor(time / timeInMins);
                    let seconds = time % (timeInMins + 1);

                    // minutes = minutes < 10 ?  minutes : minutes;
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

                        document.querySelector('.download-loading-container').remove();
                        extFileUrl(linkType, linkUrl)
                    }

                    return done;

                }, 1000);

            }
        }


        if (null !== download_external_url & "" !== download_external_url) {
            if ("false" !== haveExternal && "" !== download_external_url) {
                // From gutenberg
                createLink('external_link', download_external_url)
            } else {
                // From shortcode. @todo create another download method.
                ///*
                if (download_external_url.indexOf('?') === -1) {
                    download_external_url += '?download';
                }
               
               // window.open(download_external_url, '_blank');

                if(targetBlank == "false") {
                    window.open(download_external_url, '_self');
                } else {
                    window.open(download_external_url, '_blank');
                }
               
                //*/ 
            }

            return;

        } else {
            // This is internal Download
            createLink('aid', attachment_id)
        }

        /* This function check nonce and open the download url in a new tab. */
        function extFileUrl($type, $url) {
            var data = {
                'action': 'qdbu_download_ajax_referer',
                'security': quick_download_object.security
            }

            if (data.security) {

                let url = `${quick_download_object.redirecturl}?${$type}=${$url}&_wpnonce=${data.security}`;
                
                if(targetBlank == "false") {
                    window.open(url, '_self');
                } else {
                    window.open(url, '_blank');
                }
                             
            }
        }

    }, false);

})
