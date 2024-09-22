/**
 * Send to server functions.
 * 
 * @author Remal Mahmud <mahmudremal@yahoo.com>
 */

// class send2Server {
//     constructor(options = {}) {
//         this.config = options = Object.assign({}, options);
//         // 
//     }
// }

export const send2Server = (ajax_url, formdata = {}, options = {}) => {
    options = Object.assign({
        type: "POST",
        cache: false,
        contentType: false,
        processData: false,
    }, options);
    // if (formdata instanceof FormData) {
    //     if (!formdata.get('_nonce')) {
    //         formdata.append('_nonce', thisClass.ajaxNonce);
    //     }
    // }
    return new Promise((resolve, reject) => {
        var ajax_args = {
            url: ajax_url,
            data: formdata,
            type: options.type,
            cache: options.cache,
            contentType: options.contentType,
            processData: options.processData,
            success: (json) => {
                if (json?.success) {resolve(json.data);}
                else if(json.success === false) {reject(json.data);}
                else {resolve(json);}
            },
            error: (error) => {
                try {
                    if (['', '0'].includes((error?.responseText??'').trim())) {error.responseText = false;}
                } catch (error) {}
                reject(error);
            }
        }
        jQuery.ajax(ajax_args);
    });
}