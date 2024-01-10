class Exim {
    constructor(thisClass) {
        this.setup_hooks(thisClass);
    }
    setup_hooks(thisClass) {
        this.total_paged = false;
        this.current_paged = false;
        this.current_operation = false;
        
        this.init_events(thisClass);
    }
    get_products(thisClass, type = 'product', paged = 1) {
        this.current_paged = paged;
        this.current_operation = type;

        var formdata = new FormData(); // content || product
        formdata.append('action', 'futurewordpress/project/ajax/export/' + type);
        formdata.append('_nonce', thisClass.ajaxNonce);
        formdata.append('paged', paged);
        thisClass.sendToServer(formdata);
    }
    init_events(thisClass) {
        const EximClass = this;EximClass.exports = [];EximClass.lastJson = false;
        ['export_product_response', 'export_content_response'].forEach((hook) => {
            document.body.addEventListener(hook, (event) => {
                EximClass.lastJson = thisClass.lastJson;
                if(EximClass.lastJson?.pagination) {
                    const pagination = EximClass.lastJson.pagination;
                    if(pagination?.current) {EximClass.current_paged = parseInt(pagination?.current??1);}
                    if(pagination?.total) {EximClass.total_paged = parseInt(pagination?.total??1);}
                    if(EximClass.total_paged > EximClass.current_paged) {
                        EximClass.current_paged++;
                        Object.values((EximClass.lastJson?.exports??[])).forEach((row) => {
                            EximClass.exports.push(row);
                        });
                        EximClass.get_products(thisClass, EximClass.current_operation, EximClass.current_paged);
                    } else {
                        thisClass.Swal.fire({
                            title: "Good job!",
                            text: "You got what you expected!",
                            icon: "success",
                            didOpen: async () => {
                                var json_export = {imports: EximClass.exports, importable: true};
                                var prod_title = EximClass.lastJson?.prod_title??'export_product';
                                var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(json_export));
                                var dlAnchorElem = document.createElement('a');
                                dlAnchorElem.target = '_blank';
                                dlAnchorElem.setAttribute("href", dataStr);
                                dlAnchorElem.setAttribute("download", prod_title + ".json");
                                dlAnchorElem.click();
                                setTimeout(() => {
                                    dlAnchorElem.remove();
                                    EximClass.exports = false;
                                    thisClass.lastJson = false;
                                    EximClass.lastJson = false;
                                    EximClass.total_paged = false;
                                    EximClass.current_paged = false;
                                    EximClass.current_operation = false;
                                }, 1500);
                            },
                            allowOutsideClick: () => !thisClass.Swal.isLoading(),
                        });
                    }
                }
                
            });
        });
        
    }
    js_fetch_example() {
        // fetch("example_url", {
        // "headers": {
        //     "accept": "*/*",
        //     "accept-language": "en-GB,en-US;q=0.9,en;q=0.8,bn;q=0.7",
        //     "cache-control": "no-cache",
        //     "content-type": "multipart/form-data; boundary=----WebKitFormBoundary1VBdTAmW0PGXOBep",
        //     "pragma": "no-cache",
        //     "sec-ch-ua": "\"Google Chrome\";v=\"119\", \"Chromium\";v=\"119\", \"Not?A_Brand\";v=\"24\"",
        //     "sec-ch-ua-mobile": "?0",
        //     "sec-ch-ua-platform": "\"Windows\"",
        //     "sec-fetch-dest": "empty",
        //     "sec-fetch-mode": "cors",
        //     "sec-fetch-site": "same-origin",
        //     "x-requested-with": "XMLHttpRequest"
        // },
        // "referrer": "https://dubidofactory.com/",
        // "referrerPolicy": "strict-origin-when-cross-origin",
        // "body": JSON.stringify({action: 'example_action'}),
        // "method": "POST",
        // "mode": "cors",
        // "credentials": "include"
        // }).then((res) => res.json()).then((json) => {
        //     console.log(json);
        // }).catch((error) => {console.error(error);});
    }
}
export default Exim;
// thisClass.Exim.get_products(thisClass, 'product')