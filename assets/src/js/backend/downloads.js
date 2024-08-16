const DOWNLOADS = {
    i18n: {},
    get_preloader: (thisClass) => {
        var html = document.createElement('div');
        html.classList.add('attachments_popup');
        html.innerHTML = `
            <div class="spinner-material"></div>
            <h3>${DOWNLOADS.i18n?.pls_wait??'Please wait...'}</h3>
        `;
        return html;
    },
    init_events: (thisClass) => {
        DOWNLOADS.i18n = thisClass.i18n;
        document.body.addEventListener('order_downloads_success', (event) => {
            if((thisClass?.lastJson)?.attached) {
                DOWNLOADS.lastAttached = thisClass.lastJson.attached;
                const pops = document.querySelector('.attachments_popup');
                // 
                if(pops && DOWNLOADS.lastAttached.length >= 1) {
                    pops.innerHTML = `
                    <table>
                        <thead>
                            <tr>
                                <th>${DOWNLOADS.i18n?.productname??'Product title'}</th>
                                <td>${DOWNLOADS.i18n?.data??'Data'}</td>
                            </tr>
                        </thead>
                        <tbody>
                            ${DOWNLOADS.lastAttached.filter(row => row.attached.voices.length || row.attached.certificates.length).map((order_item) => `
                            <tr>
                                <th>
                                    <a class="attachment__title" href="${order_item.product_edit}" target="_blank">${order_item.product}</a> x ${order_item.quantity}
                                </th>
                                <td>
                                    ${Object.keys(order_item.attached).map((key) => (
                                        order_item.attached[key] // && (order_item.attached[key]?.length >= 1)
                                    )?`
                                        <span class="attachment__type">${thisClass.i18n[key.toLowerCase()]??key}</span>
                                        <div class="attachment__row">
                                            ${
                                            (key == 'voices')?`
                                                ${order_item.attached[key].map((voice) => `
                                                    ${(voice?.skip)?thisClass.i18n?.voice_skipped??'Voice Skipped.':''}
                                                    ${(voice?.later)?thisClass.i18n?.voicewillsend??'Voice will be send through email.':''}
                                                    ${(voice?.blob)?`
                                                        <a class="btn attachment__download" href="${voice.blob}" target="_blank" download="${DOWNLOADS.get_merged_download_file_name(voice.blob, key, order_item)}" title="${
                                                            wp.i18n.sprintf(thisClass.i18n?.method_s??'Method: %s', (voice?.method??'').replace('"', ''))
                                                        }">${DOWNLOADS.get_merged_download_file_name(voice.blob, key, order_item)}</a>
                                                    `:''}
                                                `).join('')}
                                            `:(order_item.attached[key].length >= 1)
                                            ?
                                                order_item.attached[key].map((link) => link.trim() == ''?`
                                                    <span class="attachment__needgen">${thisClass.i18n?.certnotgenerated??'Certificate not generated yet.'}</span>
                                                `:`
                                                    <a class="btn attachment__download" href="${link}" target="_blank" download="${DOWNLOADS.get_merged_download_file_name(link, key, order_item)}">${DOWNLOADS.get_merged_download_file_name(link, key, order_item)}</a>
                                                `).join('')
                                            :
                                                ``
                                            }
                                        </div>
                                    `:'').join('')}
                                </td>
                            </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    `;
                } else {
                    pops.innerHTML = `
                    <div class="nothing_error">
                        ${(DOWNLOADS.lastAttached?.errorSVG)?`
                            <img src="${DOWNLOADS.lastAttached?.errorSVG}" alt="${DOWNLOADS.i18n?.nothingleft??'Nothing left'}" />
                        `:`
                            <span>${DOWNLOADS.i18n?.nothingleft??'Nothing left'}</span>
                        `}
                    </div>
                    `;
                }
            }
        });
        // document.body.addEventListener('', (event) => {});
    },
    init_downloadable_attached_popup: (el, thisClass) => {
        var html, order_id, config = {};

        if((el?.dataset)?.config) {
            try {
                config = JSON.parse((el?.dataset)?.config);
            } catch (error) {
                console.log(err);
            }
        }
        
        if(!(config?.order_id)) {
            thisClass.notify.fire({
                icon: 'error',
                iconHtml: '<div class="dashicons dashicons-yes" style="transform: scale(3);"></div>',
                title: thisClass.i18n?.somethingwentwrong??'Something went wrong!',
            });
            return;
        }

        html = document.createElement('div');
        html.appendChild(DOWNLOADS.get_preloader(thisClass));
        thisClass.Swal.fire({
            showConfirmButton: false, showCancelButton: false, showCloseButton: true,
            cancelButtonText: 'Close', confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
            customClass: {popup: 'fwp-swal2-popup'}, backdrop: 'rgba(0,0,123,0.4)',
            showLoaderOnConfirm: true, allowOutsideClick: false, html: html.innerHTML,
            title: false, // width: 600,
            didOpen: async () => {
                var formdata = new FormData();
                formdata.append('action', 'teddybear/project/ajax/order/downloads');
                formdata.append('_nonce', thisClass.ajaxNonce);
                formdata.append('order_id', config?.order_id);
                thisClass.sendToServer(formdata);
            },
            preConfirm: () => confirm('Are you sure?'), // async (login) => {return false;}
            preDeny: () => confirm('Are you sure?'), // async (login) => {return false;}
        }).then(async (result) => {
            if(result.isConfirmed) {}
        })
    },
    get_merged_download_file_name: (link, type, order_item) => {
        const extension = link.split('/').pop().split('.').pop();
        const filename = link.split('/').pop().split('.')[0];
        switch (type) {
            case 'voices':
                // link = `recorded-${order_item.order_item_id}-${order_item.order_id}.${link.split('.').pop()}`;
                link = `${order_item.order_id}.${extension}`;
                break;
            default:
                link = `${order_item.order_id}.${extension}`;
                break;
        }
        return link;
    }
};
export default DOWNLOADS;