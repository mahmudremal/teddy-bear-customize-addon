class Exim {
    constructor(thisClass) {
        this.setup_hooks(thisClass);
    }
    setup_hooks(thisClass) {
        // this.get_products(thisClass);
        this.init_events(thisClass);
    }
    get_products(thisClass, type = 'product') {
        var formdata = new FormData(); // content || product
        formdata.append('action', 'futurewordpress/project/ajax/export/' + type);
        formdata.append('_nonce', thisClass.ajaxNonce);
        thisClass.sendToServer(formdata);
    }
    init_events(thisClass) {
        ['export_product_response', 'export_content_response'].forEach((hook) => {
            document.body.addEventListener(hook, (event) => {
                Exim.lastJson = thisClass.lastJson;
                thisClass.Swal.fire({
                    title: 'Success',
                    background: 'rgb(255 255 255)',
                    showConfirmButton: false,
                    showCancelButton: false,
                    showCloseButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    showDenyButton: false,
                    confirmButtonColor: '#ffc52f',
                    cancelButtonColor: '#de424b',
                    dismissButtonColor: '#de424b',
                    customClass: {popup: 'fwp-export_popup', confirmButton: 'text-dark'},
                    // focusConfirm: true,
                    // reverseButtons: true,
                    backdrop: `rgb(137 137 137 / 74%)`,
                    // html: `<div class="export_popup"></div>`,
                    showLoaderOnConfirm: true,
                    footer: false,
                    didOpen: async () => {
                        var json_export = {imports: Exim.lastJson?.exports??Exim.lastJson, importable: true};
                        var prod_title = Exim.lastJson?.prod_title??'export_product';
                        var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(json_export));
                        var dlAnchorElem = document.createElement('a');
                        dlAnchorElem.target = '_blank';
                        dlAnchorElem.setAttribute("href", dataStr);
                        dlAnchorElem.setAttribute("download", prod_title + ".json");
                        dlAnchorElem.click();
                        setTimeout(() => {dlAnchorElem.remove();}, 1500);
                    },
                    allowOutsideClick: () => !thisClass.Swal.isLoading(),
                }).then((res) => {});
            });
        });
        
    }
}
export default Exim;