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
    get_exports(thisClass, type = 'product', paged = 1) {
        this.current_paged = paged;
        this.current_operation = type;

        var formdata = new FormData(); // content || product || any post type
        formdata.append('action', 'teddybear/project/ajax/export/' + type);
        formdata.append('_nonce', thisClass.ajaxNonce);
        formdata.append('paged', paged);
        formdata.append('type', type);
        thisClass.sendToServer(formdata);
    }
    init_events(thisClass) {
        this.exports = [];this.lastJson = false;
        ['exports_response'].forEach((hook) => {
            document.body.addEventListener(hook, (event) => {
                this.lastJson = thisClass.lastJson;
                if(this.lastJson?.pagination) {
                    const pagination = this.lastJson.pagination;
                    if(pagination?.current) {this.current_paged = parseInt(pagination?.current??1);}
                    if(pagination?.total) {this.total_paged = parseInt(pagination?.total??1);}
                    if(this.total_paged > this.current_paged) {
                        this.current_paged++;
                        Object.values((this.lastJson?.exports??[])).forEach((row) => {
                            this.exports.push(row);
                        });
                        this.get_exports(thisClass, this.current_operation, this.current_paged);
                    } else {
                        thisClass.Swal.fire({
                            title: "Good job!",
                            text: "You got what you expected!",
                            icon: "success",
                            didOpen: async () => {
                                var json_export = {imports: this.exports, importable: true};
                                var prod_title = this.lastJson?.prod_title??'export_product';
                                var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(json_export));
                                var dlAnchorElem = document.createElement('a');
                                dlAnchorElem.target = '_blank';
                                dlAnchorElem.setAttribute("href", dataStr);
                                dlAnchorElem.setAttribute("download", prod_title + ".json");
                                dlAnchorElem.click();
                                setTimeout(() => {
                                    dlAnchorElem.remove();
                                    this.exports = false;
                                    thisClass.lastJson = false;
                                    this.lastJson = false;
                                    this.total_paged = false;
                                    this.current_paged = false;
                                    this.current_operation = false;
                                }, 1500);
                            },
                            allowOutsideClick: () => !thisClass.Swal.isLoading(),
                        });
                    }
                }
            });
        });
    }
}
export default Exim;
// thisClass.Exim.get_exports(thisClass, 'product', 1) // product | content | post,page,custom_post_type