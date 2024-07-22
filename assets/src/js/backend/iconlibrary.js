/**
 * Icon Library for Dubido backend.
 * 
 * @author @mahmudremal
 */

// var vex = require('vex-js')
// vex.registerPlugin(require('vex-dialog'))
// vex.defaultOptions.className = 'vex-theme-os'
import axios, {isCancel, AxiosError} from 'axios';
import Toastify from 'toastify-js';
import Dropzone from "dropzone";
Dropzone.autoDiscover = false;


class iconLibrary {
    /**
     * Constructor for iconLibrary.
     *
     * @param {Object} [config={title: 'Select SVG icon', callback: (svg) => {}}] - An optional configuration object with title and callback properties.
     */
    constructor(config = {}) {
        this.icons = [];// this.vex = vex;
        this.config = Object.assign({
            selected: [],
            multiple: true,
            icons_per_load: 24,
            callback: (svg) => {},
            title: 'Select SVG icon',
            submitBtnText: 'Add these icons',
        }, config);
        this.paged = 1;this.elements = {};
        // window.iconLibrary = this;
        this.setup_necessery_utilities();
    }
    /**
     * Setup necessary utilities for the icon library.
     */
    setup_necessery_utilities() {
        this.container = document.querySelector('.swal2-html-container');
        if (!this.container) {
            this.container = document.createElement('div');
        }
        this.render_library();
    }
    load_icons() {
        return new Promise((resolve, reject) => {
            // 
            this.elements.tabIcons.library.classList.add('loading');
            // 
            // this.paged++;
            // 
            this.icons = [];this.iconsError = false;this.pagination = false;
            var data = {action: 'teddy/library/icons', _nonce: fwpSiteConfig.ajax_nonce, paged: this.paged, per_page: this.config.icons_per_load};
            if (!(this?.firstLoaded) && this.paged <= 1) {data.includings = this.config.selected.join(',');}
            var formdata = new FormData();
            Object.keys(data).forEach(key => formdata.append(key, data[key]));
            axios.post(fwpSiteConfig.ajaxUrl, formdata)
            .then(response => response?.data??response)
            .then(async (response) => {
                this.elements.tabIcons.library.classList.remove('loading');
                if (response?.success) {
                    this.icons = Object.values(response.data);
                    this.firstLoaded = true;
                    if (response?.pagination) {
                        Object.keys(response.pagination).forEach(pagisKey => response.pagination[pagisKey] = parseInt(response.pagination[pagisKey]));
                        this.pagination = response.pagination;
                    }
                } else if (response?.data) {
                    this.iconsError = response?.data;
                } else {
                    console.log(response)
                }
            })
            .catch(error => reject(error))
            .finally(() => resolve(this.icons));
        });
    }
    /**
     * Render the tab with upload functionality.
     *
     * @param {HTMLDivElement} tabContent - The tab content element.
     * @returns {Promise} A promise that resolves when the tab is rendered.
     */
    render_library() {
        var pop = this.elements.library = document.createElement('div');pop.classList.add('icon-media');this.openLibrary();
        // 
        var closeBtn = document.createElement('button');closeBtn.classList.add('icon-media-close');
        closeBtn.addEventListener('click', (event) => {event.preventDefault();this.closeLibrary();});
        closeBtn.title = 'Close';closeBtn.innerHTML = this.inline_icons('cross');closeBtn.type = 'button';
        // 
        var content = document.createElement('div');content.classList.add('icon-media-content');
        var frame = document.createElement('div');frame.classList.add('icon-media-frame');
        var title = document.createElement('div');title.classList.add('icon-media-frame-title');
        var h1 = document.createElement('h1');h1.innerHTML = this.config.title;
        title.appendChild(h1);frame.appendChild(title);
        var tabPane = document.createElement('div');tabPane.classList.add('icon-media-tab-pane');
        var tabRauter = document.createElement('div');tabRauter.classList.add('icon-media-tab-rauter');
        // 
        var tabContents = document.createElement('div');tabContents.classList.add('icon-media-tab-content');
        // 
        var tablist = document.createElement('div');tablist.classList.add('icon-media-tab-tablist');
        var tabs = {upload: 'Upload Icons', media: 'Media Library'}
        Object.keys(tabs).forEach(async (tabKey, index) => {
            var btn = document.createElement('button');btn.type = 'button';btn.innerHTML = tabs[tabKey];
            btn.setAttribute('aria-selected', true);btn.dataset.tabindex = index;
            var tabContent = document.createElement('div');tabContent.classList.add('icon-media-tab-content-single');
            var blankBlock = document.createElement('div');blankBlock.classList.add('icon-media-tab-content-blank');
            blankBlock.innerHTML = 'Something left to render. Please see your console if any issue happens.';
            tabContent.appendChild(blankBlock);
            let isMade = false;
            switch (tabKey) {
                case 'upload':
                    isMade = await this.render_tab_upload(tabContent);
                    // if (this.icons.length <= 0) {tabContent.classList.add('activate');btn.classList.add('activate');}
                    break;
                case 'media':
                    isMade = await this.render_tab_icons(tabContent);
                    tabContent.classList.add('activate');btn.classList.add('activate'); // Forcefully activate this tab
                    // if (this.icons.length >= 1) {tabContent.classList.add('activate');btn.classList.add('activate');}
                    this.load_icons().then(data => this.render_tab_icons(tabContent)).catch(error => console.error(error));
                    break;
                default:
                    break;
            }
            if (isMade) {blankBlock.remove();}
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                [...tabContents.children].filter(tab => tab.classList.contains('activate')).map(tab => tab.classList.remove('activate'));
                [...tablist.children].filter(tab => tab.classList.contains('activate')).map(tab => tab.classList.remove('activate'));
                // 
                tabContent.classList.add('activate');btn.classList.add('activate');
                // 
                switch (tabKey) {
                    case 'media':
                        this.load_icons().then(data => this.render_tab_icons(tabContent)).catch(error => console.error(error));
                        break;
                    default:
                        break;
                }
            });
            tablist.appendChild(btn);tabContents.appendChild(tabContent);
        });
        // 
        var tabFoot = document.createElement('div');tabFoot.classList.add('icon-media-tab-foot');
        var tabFootWrap = document.createElement('div');tabFootWrap.classList.add('icon-media-tab-foot-wrap');
        var tabFootActs = document.createElement('div');tabFootActs.classList.add('icon-media-tab-foot-acts');
        var proceedTo = document.createElement('button');proceedTo.classList.add('icon-media-tab-foot-proceed');
        proceedTo.innerHTML = this.config?.submitBtnText;proceedTo.type = 'button';
        proceedTo.addEventListener('click', (event) => {
            event.preventDefault();
            var selectedIcons = this.icons.filter(icon => icon?.selected);
            if (!(this.config?.multiple) && selectedIcons.length >= 1) {selectedIcons = selectedIcons[0];}
            this.closeLibrary();this.config.callback(selectedIcons);
        });
        tabFootActs.appendChild(proceedTo);tabFootWrap.appendChild(tabFootActs);tabFoot.appendChild(tabFootWrap);
        // 
        tabRauter.appendChild(tablist);tabPane.appendChild(tabRauter);tabPane.appendChild(tabContents);frame.appendChild(tabPane);frame.appendChild(tabFoot);content.appendChild(frame);pop.appendChild(content);pop.appendChild(closeBtn);
        // 
        if (this.container.children.length) {this.container.insertBefore(pop, this.container.children[0]);} else {this.container.appendChild(pop);}
    }
    async render_tab_icons(tab) {
        if (!(this?.icons) || !tab) {return false;}
        let isFirstTime = !(this.elements?.tabIcons);
        if (isFirstTime) {
            this.elements.tabIcons = {};
            var library = this.elements.tabIcons.library = document.createElement('div');
            library.classList.add('imglibrary');
        } else {
            var library = this.elements.tabIcons.library;
        }
        
        library.innerHTML = '';// Remove all previous elements form here.
        var wrap = document.createElement('div');
        wrap.classList.add('imglibrary-wrap');
        var grid = this.elements.tabIcons.grid = document.createElement('div');
        grid.classList.add('justified-grid-gallery');
        if (this?.iconsError) {
            var error = document.createElement('div');error.classList.add('imglibrary-error');
            error.innerHTML = this.iconsError;
        } else {
            Object.values(this.icons).forEach(icon => {
                var figure = icon.figure = document.createElement('figure');figure.id = icon.icon_id;
                figure.setAttribute('style', '--width: 640; --height: 520;');
                if (this.config.selected.includes(icon.icon_id)) {
                    icon.selected = true;figure.classList.add('selected');
                }
                var image = document.createElement('img');image.src =icon.url;image.alt = icon?.name;
                image.id = `icons-${icon.icon_id}`;image.dataset.iconType = icon?.type??'';
                image.dataset.iconSize = icon?.size??'';image.dataset.iconId = icon.icon_id;
                var actsIcon = document.createElement('span');actsIcon.classList.add('acts', 'dashicons', 'dashicons-ellipsis');
                var actsMenu = document.createElement('div');actsMenu.classList.add('acts_menus');
                var actsItems = document.createElement('ul');actsItems.classList.add('acts_items');
                var acts = {remove: 'Remove', info: 'Copy info', download: 'Download'};
                Object.keys(acts).map(act => {
                    var actsItem = document.createElement('li');actsItem.classList.add('acts_items_single');
                    var actSpan = document.createElement('span');// actSpan.classList.add('acts_items_single_title');
                    actsItem.addEventListener('click', (event) => {
                        event.preventDefault();event.stopPropagation();
                        switch (act) {
                            case 'remove':
                                var data = {action: 'teddy/library/icon/remove', _nonce: fwpSiteConfig.ajax_nonce, icon_id: icon.icon_id};
                                var formdata = new FormData();figure.classList.add('loading');
                                Object.keys(data).forEach(key => formdata.append(key, data[key]));
                                try {
                                    axios.post(fwpSiteConfig.ajaxUrl, formdata)
                                    .then(response => response?.data??response)
                                    .then(async (response) => {
                                        if (response?.success) {
                                            figure.remove();
                                        } else {
                                            throw new Error('Something went wrong! File didn\'t removed form server');
                                        }
                                    })
                                    .catch(error => {
                                        console.error(error);
                                        throw new Error('Failed to execute request. Please check your console.');
                                    });
                                } catch (error) {
                                    Toastify({text: error, className: 'info', gravity: 'left', duration: 3000, stopOnFocus: true, style: {background: 'linear-gradient(to right, #f4433678, #F44336)'}}).showToast();
                                }
                                break;
                            case 'info':
                                if (navigator?.clipboard) {
                                    navigator.clipboard.writeText(JSON.stringify(icon)).then(res => {
                                        setTimeout(() => {
                                            Toastify({text: 'Icon information copied to clipboard', className: 'info', gravity: 'left', duration: 3000, stopOnFocus: true, style: {background: 'linear-gradient(to right, #81C784, #2E7D32)'}}).showToast();
                                        }, 1000);
                                    }).catch(err => console.error(err));
                                }
                                break;
                            case 'download':
                                var dlBtn = document.createElement('a');dlBtn.target = '_blank';dlBtn.href = icon.url;dlBtn.download = icon.name;setTimeout(() => {dlBtn.click();}, 1500);
                                break;
                            default:
                                break;
                        }
                        actsIcon.click();
                    });
                    actSpan.innerHTML = acts[act];actsItem.appendChild(actSpan);actsItems.appendChild(actsItem);
                });
                actsIcon.addEventListener('click', (event) => {
                    event.preventDefault();event.stopPropagation();
                    var actsBackDrop = this.elements.tabIcons.library.querySelector('.imglibrary_backdrops');
                    actsMenu.classList.toggle('visible');
                    if (actsBackDrop) {
                        actsBackDrop.remove();
                    } else {
                        var actsBackDrop = document.createElement('div');actsBackDrop.classList.add('imglibrary_backdrops');
                        actsBackDrop.addEventListener('click', (event) => {
                            event.preventDefault();actsIcon.click();
                        });
                        this.elements.tabIcons.library.appendChild(actsBackDrop);
                    }
                });
                figure.addEventListener('click', (event) => {
                    event.preventDefault();event.stopPropagation();
                    if (!(this.config?.multiple)) {
                        this.icons.filter(icon => icon?.selected).map(icon => {
                            icon.selected = false;
                            icon.figure.classList.remove('selected');
                            return icon;
                        });
                        icon.selected = true;icon.figure.classList.add('selected');
                    } else {
                        figure.classList.toggle('selected');icon.selected = !(icon?.selected);
                    }
                });
                actsMenu.appendChild(actsItems);figure.appendChild(actsIcon);figure.appendChild(image);figure.appendChild(actsMenu);
                grid.appendChild(figure);
            });
            wrap.appendChild(grid);
            if (this?.pagination && this.pagination?.pages > 1) {
                var pagiCont = document.createElement('div');pagiCont.classList.add('pagis-cont');
                var pagiWrap = document.createElement('div');pagiWrap.classList.add('pagis-wrap');
                var pagiBlock = document.createElement('div');pagiBlock.classList.add('pagis-block');
                var pagiRow = document.createElement('div');pagiRow.classList.add('pagis-row');
                var pagis = this.generatePagination();
                // console.log(pagis);
                // 
                pagis = pagis.map(page => {
                    var pagiSingle = document.createElement('div');pagiSingle.classList.add('pagis-single');
                    var pagiSpan = document.createElement('div');pagiSpan.classList.add('pagis-span');
                    switch (page) {
                        case 1:
                            pagiSpan.innerHTML = 'First';
                            break;
                        case this.pagination.current:
                            pagiSpan.innerHTML = 'Current';
                            break;
                        case this.pagination.pages:
                            pagiSpan.innerHTML = 'Last';
                            break;
                        default:
                            pagiSpan.innerHTML = page;
                            break;
                    }
                    pagiSpan.innerHTML = (page == this.pagination.current)?'Current':page;
                    pagiSingle.appendChild(pagiSpan);
                    pagiSingle.addEventListener('click', (event) => {
                        event.preventDefault();var tabContent = tab;
                        this.paged = page;
                        this.load_icons().then(data => this.render_tab_icons(tabContent)).catch(error => console.error(error));
                    });
                    return pagiSingle;
                });
                pagis.forEach(page => {
                    pagiRow.appendChild(page);
                });
                pagiBlock.appendChild(pagiRow);pagiWrap.appendChild(pagiBlock);pagiCont.appendChild(pagiWrap);
                wrap.appendChild(pagiCont);
            }
            library.appendChild(wrap);
        }
        if (isFirstTime) {tab.appendChild(library);}
        // 
        return library;
    }
    /**
     * Render the tab with upload functionality.
     *
     * @param {HTMLDivElement} tabContent - The tab content element.
     * @returns {Promise} A promise that resolves when the tab is rendered.
     */
    async render_tab_upload(tab) {
        var wrap = document.createElement("div");wrap.classList.add('icon-media-tab-content-upload');
        var drop = document.createElement("div");drop.classList.add('icon-media-tab-content-drop', 'dropzone');
        // var dPanel = document.createElement("div");dPanel.classList.add('dropzone-panel', 'mb-lg-0', 'mb-2');
        // var dAttachF = document.createElement("a");dAttachF.classList.add('dropzone-select', 'btn', 'btn-sm', 'btn-primary', 'me-2');dAttachF.innerHTML = 'Upload Icons';
        // var dRemoveAll = document.createElement("a");dRemoveAll.classList.add('dropzone-remove-all', 'btn', 'btn-sm', 'btn-light-primary');dRemoveAll.innerHTML = 'Remove All';
        // var items = document.createElement("div");items.classList.add('icon-media-tab-content-items', 'dropzone-items', 'wm-200px');
        var dHint = document.createElement("span");dHint.classList.add('form-text', 'text-muted');dHint.innerHTML = 'Max file size is 2MB and max number of files is 200.';
        // dPanel.appendChild(dAttachF);dPanel.appendChild(dRemoveAll);drop.appendChild(dPanel);drop.appendChild(items);
        this.uploadDrop = new Dropzone(drop, {
            url: fwpSiteConfig.ajaxUrl,
            sending: (file, xhr, formData) => {
                if (!formData.get('action')) {
                    formData.append('action', 'teddy/library/icon/add');
                    formData.append('_nonce', fwpSiteConfig.ajax_nonce);
                }
                formData.append('icon_info[]', JSON.stringify({
                    name: file.name, size: file.size, type: file.type,
                    width: file.width, height: file.height
                }));
            },
            paramName: '_icon_file',
            maxFilesize: 2, // MB
            method: 'post',
            uploadMultiple: true,
            maxFiles: 200,
            clickable: true,
            acceptedFiles: 'image/*,text/*,.svg',
            addRemoveLinks: true,
            // previewTemplate: `
            // <div class="dropzone-item" style="display:none">
            //     <!--begin::File-->
            //     <div class="dropzone-file">
            //         <div class="dropzone-filename" title="some_image_file_name.jpg">
            //             <span data-dz-name>some_image_file_name.jpg</span>
            //             <strong>(<span data-dz-size>340kb</span>)</strong>
            //         </div>

            //         <div class="dropzone-error" data-dz-errormessage></div>
            //     </div>
            //     <!--end::File-->

            //     <!--begin::Progress-->
            //     <div class="dropzone-progress">
            //         <div class="progress">
            //             <div
            //                 class="progress-bar bg-primary"
            //                 role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-dz-uploadprogress>
            //             </div>
            //         </div>
            //     </div>
            //     <!--end::Progress-->

            //     <!--begin::Toolbar-->
            //     <div class="dropzone-toolbar">
            //         <span class="dropzone-delete" data-dz-remove><i class="bi bi-x fs-1"></i></span>
            //     </div>
            //     <!--end::Toolbar-->
            // </div>
            // `,
            // previewsContainer: items,
            // clickable: dAttachF,
            accept: function(file, done) {
                done();
            },
            init: function() {
                this.on("addedfile", file => {
                    // console.log(`File added: ${file.name}`);
                });
                this.on("complete", function(file) {
                    this.removeFile(file);
                });
            },
            success: (file, response) => {
                console.log(response);
            }
        });
        // let mockFile = { name: "Filename", size: 12345 };
        // this.uploadDrop.displayExistingFile(mockFile, 'https://image-url');
        drop.appendChild(dHint);wrap.appendChild(drop);tab.appendChild(wrap);
        return wrap;
    }
    inline_icons(id = false) {
        const icons = {
            cross: `<svg fill="#646970" width="32px" height="32px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M18.8,16l5.5-5.5c0.8-0.8,0.8-2,0-2.8l0,0C24,7.3,23.5,7,23,7c-0.5,0-1,0.2-1.4,0.6L16,13.2l-5.5-5.5 c-0.8-0.8-2.1-0.8-2.8,0C7.3,8,7,8.5,7,9.1s0.2,1,0.6,1.4l5.5,5.5l-5.5,5.5C7.3,21.9,7,22.4,7,23c0,0.5,0.2,1,0.6,1.4 C8,24.8,8.5,25,9,25c0.5,0,1-0.2,1.4-0.6l5.5-5.5l5.5,5.5c0.8,0.8,2.1,0.8,2.8,0c0.8-0.8,0.8-2.1,0-2.8L18.8,16z"></path></g></svg>`
        };
        return (icons[id])?icons[id]:false;
    }
    /**
     * Generate pagination array based on the current page, total pages, and pagination limit.
     *
     * @param {number} [paginationLimit=5] - The number of pages to display in the pagination array.
     * @returns {Array} An array of page numbers to be displayed in the pagination.
     */
    generatePagination(paginationLimit = 5) {
        let startPage, endPage;
        const currentPage = this.pagination.current;
        const totalPages = this.pagination.pages;// Math.ceil(this.pagination.total / this.pagination.perpage);
        // 
        if (totalPages <= paginationLimit) {
            startPage = 1;
            endPage = totalPages;
        } else {
            if (currentPage <= Math.ceil(paginationLimit / 2)) {
                startPage = 1;
                endPage = paginationLimit;
            } else if (currentPage + Math.floor(paginationLimit / 2) >= totalPages) {
                startPage = totalPages - paginationLimit + 1;
                endPage = totalPages;
            } else {
                startPage = currentPage - Math.floor(paginationLimit / 2);
                endPage = currentPage + Math.floor(paginationLimit / 2);
            }
        }
        // 
        const pages = Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);
        // 
        return pages;
    }
    /**
     * Close the library.
     */
    closeLibrary() {
        if (this?.library) {return;}
        this.elements.library.remove();
        var pop = document.querySelector('.swal2-popup');
        if (pop) {pop.classList.remove('iconlibrary-opened');}
    }
    /**
     * Open the library.
     */
    openLibrary() {
        var pop = document.querySelector('.swal2-popup');
        if (pop) {pop.classList.add('iconlibrary-opened');}
    }
}
export default iconLibrary;