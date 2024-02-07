import icons from "./icons";
const PROMPTS = {
    i18n: {},voices: {}, names: [], currentGroups: 'standing',
    groupSelected: false, outfitSliders: [], groupExists: false,
    
    get_template: (thisClass) => {
        var json, html;PROMPTS.global_cartBtn = false;
        html = document.createElement('div');html.classList.add('dynamic_popup');
        if(PROMPTS.lastJson) {
            html.innerHTML = PROMPTS.generate_template(thisClass);
        } else {
            html.classList.add('dynamic_popup__preload');
            html.innerHTML = `<div class="spinner-material"></div><h3>${PROMPTS.i18n?.pls_wait??'Please wait...'}</h3>`;
        }
        return html;
    },
    init_prompts: (thisClass) => {
        // PROMPTS.core = thisClass;
    },
    init_events: (thisClass) => {
        document.querySelectorAll('.popup_foot .button[data-react], .back2previous_step[data-react="back"]').forEach((el) => {
            el.addEventListener('click', (event) => {
                event.preventDefault();
                switch (el.dataset.react) {
                    case 'back':
                        PROMPTS.do_pagination(false, thisClass);
                        break;
                    default:
                        PROMPTS.do_pagination(true, thisClass);
                        break;
                }
            });
        });
        document.querySelectorAll('.toggle-password:not([data-handled])').forEach((el) => {
            el.dataset.handled = true;
            el.addEventListener('click', (event) => {
                event.preventDefault();
                var icon = (el.childNodes && el.childNodes[0])?el.childNodes[0]:false;
                if(!icon) {return;}
                switch (icon.classList.contains('fa-eye')) {
                    case false:
                        el.previousSibling.type = 'password';
                        icon.classList.add('fa-eye');
                        icon.classList.remove('fa-eye-slash');
                        break;
                    default:
                        el.previousSibling.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                        break;
                }
            });
        });
        document.querySelectorAll('.form-control[name="field.9000"]:not([data-handled])').forEach((input) => {
            input.dataset.handled = true;
            let awesomplete = new Awesomplete(input, {
                minChars: 3,
                maxItems: 5,
                autoFirst: true,
                // list: suggestions
            });
            input.addEventListener('input', function() {
                const query = input.value;
                let keyword = document.querySelector('#keyword_search');
                keyword = (keyword)?keyword.value:'';
                // Make the AJAX request to fetch suggestions
                fetch(thisClass.ajaxUrl + '?action=futurewordpress/project/teddybearpopupaddon/action/get_autocomplete&term=location&query='+encodeURIComponent(query)+'&keyword='+encodeURIComponent(keyword))
                  .then(response => response.json())
                  .then(data => {
                    awesomplete.list = (data?.data??data).map((row)=>row?.name??row); // Update the suggestions list
                  })
                  .catch(error => {
                    console.error('Error fetching suggestions:', error);
                  });
            });
        });

        document.querySelectorAll('.dynamic_popup').forEach((popup) => {
            if(popup) {
                var fields = document.querySelector('.tc-extra-product-options.tm-extra-product-options');
                if(!fields) {return;}
                if(!document.querySelector('.tc-extra-product-options-parent')) {
                    var node = document.createElement('div');
                    node.classList.add('tc-extra-product-options-parent');
                    fields.parentElement.insertBefore(node, fields);
                }
                popup.innerHTML = '';popup.appendChild(fields);// jQuery(fields).clone().appendTo(popup);
                
                setTimeout(() => {
                    popup.querySelectorAll('[id]').forEach((el) => {el.id = el.id+'_popup';});
                    popup.querySelectorAll('label[for]').forEach((el) => {el.setAttribute('for', el.getAttribute('for')+'_popup');});
                }, 200);

                document.querySelectorAll('.dynamic_popup .tm-collapse').forEach((el) => {
                    var head = el.firstChild;var body = el.querySelector('.tm-collapse-wrap');
                    head.classList.remove('toggle-header-closed');head.classList.add('toggle-header-open');
                    head.querySelector('.tcfa.tm-arrow').classList.add('tcfa-angle-up');
                    head.querySelector('.tcfa.tm-arrow').classList.remove('tcfa-angle-down');
                    body.classList.remove('closed');body.classList.add('open', 'tm-animated', 'fadein');
                });
            }
        });
        document.querySelectorAll('.dynamic_popup input[type="checkbox"], .dynamic_popup input[type="radio"]').forEach((el) => {
            el.addEventListener('change', (event) => {
                if(el.parentElement) {
                    if(el.parentElement.classList.contains('form-control-checkbox__image')) {
                        if(el.checked) {
                            el.parentElement.classList.add('checked_currently');
                        } else {
                            el.parentElement.classList.remove('checked_currently');
                        }
                    } else if(el.parentElement.classList.contains('form-control-radio__image')) {
                        document.querySelectorAll('input[name="'+el.name+'"]').forEach((radio) => {
                            radio.parentElement.classList.remove('checked_currently');
                        });
                        if(el.checked) {
                            el.parentElement.classList.add('checked_currently');
                        }
                    } else {}
                }
            });
        });
        /**
         * .form-fields__group__outfit instead of .popup_body
         */
        document.querySelectorAll('.dynamic_popup .popup_body input[type=checkbox][data-cost], .dynamic_popup .popup_body input[type=radio]').forEach((el) => {
            el.addEventListener('change', (event) => {
                // [data-cost]
                var img, frame, title, identity, frameHeight, frameWidth;
                frameHeight = 400;frameWidth = 350;
                frame = document.querySelector('.dynamic_popup .header_image');
                if(!frame) {return;} // I can also give here a toast that frame element not found.
                identity = el.name.replaceAll('.', '')+'-'+el.id;

                var isPayableCheckbox = false;
                if(el?.previousElementSibling) {
                    isPayableCheckbox = (((el?.previousElementSibling)?.firstChild)?.dataset)?.outfit;
                    if(!isPayableCheckbox) {isPayableCheckbox = ((el?.previousElementSibling)?.firstChild)?.src;}
                } else {
                    isPayableCheckbox = ((el.dataset?.cost) || (el.dataset?.skip));
                }

                if(el.checked && isPayableCheckbox) {
                    var image_src = ((el?.previousElementSibling)?.firstChild)?.dataset.outfit;
                    img = document.createElement('img');img.src = ((el?.previousElementSibling)?.firstChild)?.src;
                    if((((el?.previousElementSibling)?.firstChild)?.dataset)?.outfit??false) {img.src = image_src;}
                    img.height = frameHeight;img.width = frameWidth;img.id = identity;
                    img.alt = ((el?.previousElementSibling)?.firstChild)?.alt;img.dataset.name = el.name;
                    if((el.dataset?.layer??false) && el.dataset.layer != '') {img.style.zIndex = parseInt(el.dataset.layer);}
                    
                    if(el.type == 'radio') {
                        frame.querySelectorAll('img[data-name="'+el.name+'"').forEach((images) => {images.remove();});
                    }
                    if((el.dataset?.preview??false) == 'true' && image_src) {
                        frame.appendChild(img);
                    } else {
                        /**
                         * Escape other images rather then outfits.
                         */
                        // console.log(156, img);
                    }
                    
                    if(el.dataset?.cost??false) {
                        switch(el.type) {
                            case 'radio':
                                document.querySelectorAll('.dynamic_popup input[type="radio"][name="'+el.name+'"][data-cost]').forEach((radio) => {
                                    if(radio.dataset?.calculated??false) {
                                        thisClass.popupCart.removeAdditionalPrice(radio.value, parseFloat(radio.dataset.cost), radio.dataset?.product);// radio.checked = false;
                                        radio.removeAttribute('data-calculated');
                                    }
                                    if(radio.checked) {
                                        if(radio.parentElement.classList.contains('checked_currently')) {
                                            thisClass.popupCart.addAdditionalPrice(radio.value, parseFloat(radio.dataset.cost), false, radio.dataset?.product);
                                            radio.dataset.calculated = true;
                                        } else {
                                            thisClass.popupCart.removeAdditionalPrice(radio.value, parseFloat(radio.dataset.cost), radio.dataset?.product);
                                            radio.removeAttribute('data-calculated');
                                            frame.querySelectorAll('img[data-name="'+radio.name+'"]').forEach((el)=>el.remove());
                                        }
                                    }
                                });
                                break;
                            case 'checkbox':
                                thisClass.popupCart.addAdditionalPrice(el.value, parseFloat(el.dataset.cost), false, el.dataset?.product);
                                break;
                            default:
                                break;
                        }
                    } else {
                        if(el.dataset?.skip??false) {
                            switch(el.type) {
                                case 'radio':
                                    document.querySelectorAll('.dynamic_popup input[type="radio"][name="'+el.name+'"][data-cost]').forEach((radio) => {
                                        if(radio.dataset?.calculated??false) {
                                            thisClass.popupCart.removeAdditionalPrice(radio.value, parseFloat(radio.dataset.cost));// radio.checked = false;
                                            radio.removeAttribute('data-calculated');
                                            radio.parentElement.classList.remove('checked_currently');
                                        }
                                        if(radio.checked) {
                                            thisClass.popupCart.removeAdditionalPrice(radio.value, parseFloat(radio.dataset.cost));
                                            radio.removeAttribute('data-calculated');
                                            frame.querySelectorAll('img[data-name="'+radio.name+'"]').forEach((el)=>el.remove());
                                            radio.parentElement.classList.remove('checked_currently');
                                        }
                                    });
                                    break;
                                case 'checkbox':
                                    thisClass.popupCart.removeAdditionalPrice(el.value, parseFloat(el.dataset.cost));
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                } else {
                    frame.querySelectorAll('#'+identity.replaceAll('.', '')).forEach((images) => {images.remove();});
                    thisClass.popupCart.removeAdditionalPrice(el.value, parseFloat(el.dataset.cost));
                }
            });
        });
        document.querySelectorAll('.dynamic_popup input[type="date"]').forEach((el) => {
            el.type = 'text';thisClass.flatpickr(el, {enableTime: false, dateFormat: "d M, Y"});
        });

        document.querySelectorAll('.dynamic_popup button[data-type="done"]:not([data-handled])').forEach((done) => {
            done.dataset.handled = true;
            done.addEventListener('click', (event) => {
                done.parentElement.classList.add('d-none');
                done.parentElement.classList.remove('step_visible');
                done.parentElement.parentElement.classList.remove('visible_card');
                done.parentElement.parentElement.parentElement.classList.remove('visible_pops');

                var submitBtn = document.querySelector('.popup_foot .button[data-react=continue]');
                if(submitBtn) {submitBtn.style.display = 'flex';}
                
                var index = done.parentElement?.dataset.step;
                var step = document.querySelector('.swal2-progress-step[data-index="'+index+'"]');
                var span = step?.lastChild;
                if(span) {span.innerHTML = icons.tick + span.textContent;}
                if(step) {step.classList.add('swal2-active-progress-step');}

                document.querySelector('.popup_foot__wrap')?.classList.remove('d-none');
                document.querySelector('.popup_body')?.removeAttribute('data-step-type');
            });
        });
        const fieldInfos = '.popup_step__info .form-fields__group__info > div:nth-child';
        thisClass.config.lastTeddyName = '';
        document.querySelectorAll(fieldInfos+'(-n+2) input').forEach((field) => {
            field.addEventListener('change', (event) => {
                switch(field.type) {
                    case 'text':
                        var input = document.querySelector(fieldInfos+'(2) input');
                        // if(input) {
                        //     if(field.value.trim() == '') {input.checked = true;} else {input.checked = false;}
                        // }
                        break;
                    case 'checkbox':
                        var input = document.querySelector(fieldInfos+'(1) input');
                        if(input) {
                            if(field.checked) {
                                thisClass.config.lastTeddyName = input.value;
                                input.value = PROMPTS.names[Math.floor(Math.random() * PROMPTS.names.length)];
                            } else {
                                input.value = thisClass.config.lastTeddyName;
                            }
                        }
                        break;
                    default:
                        break;
                }
            });
            if(field.type == 'checkbox') {
                field.removeAttribute('name');// field.checked = true;
            }
        });
        document.querySelectorAll('.popup_body .popup_step__info .form-fields__group__info input[type="button"]').forEach((btn) => {
            var nthStep = 1;
            btn.addEventListener('click', (event) => {
                var wrap = ((btn?.parentElement)?.parentElement)?.parentElement;
                if(btn.dataset.actLike == 'next') {
                    if(wrap) {
                        if(nthStep >= 3) {
                            btn.value = thisClass.i18n?.next??'Next';
                        }
                        if(nthStep >= 4) {
                            (((wrap?.parentElement)?.parentElement)?.firstChild)?.click();
                            nthStep = 1;wrap.dataset.visibility = nthStep;
                            btn.value = thisClass.i18n?.continue??'Continue';
                            return;
                        }
                        nthStep++;wrap.dataset.visibility = nthStep;
                    }
                } else if(btn.dataset.actLike == 'skip') {
                    (((wrap?.parentElement)?.parentElement)?.firstChild)?.click();
                } else {}
            });
        });
        document.querySelectorAll('.popup-prices > .button, .calculated-prices .price_amount').forEach((el) => {
            el.addEventListener('click', (event) => {
                document.querySelector('.popup_foot .button[data-react="continue"]')?.click();
            });
        });
        // document.querySelectorAll('.form-control-radio__imgwrap:not([data-handled-tippy]), .form-control-checkbox__imgwrap:not([data-handled-tippy])').forEach((el) => {
        //     el.dataset.handledTippy = true;
        //     var image = el.querySelector('img[data-outfit]');
        //     if(image) {thisClass.tippy(el, {content: `${image?.alt??''}`});}
        // });
        document.querySelectorAll('.form-fields__group__outfit > div:not([data-handled-slide])').forEach((el) => {
            el.dataset.handledSlide = true;var wrap, wrapEL;
            wrap = wrapEL = el.querySelector('.form-wrap__image');
            var splide = wrap.parentElement;
            if(wrap) {
                
                wrap.querySelectorAll('.form-control-label').forEach((elem, i) => {
                    elem.classList.add('keen-slider__slide', 'number-slide' + (i + 1), 'splide__slide');
                });
                if(false) {
                    splide.parentElement.classList.add('splide');
                    splide.classList.add('splide__track');

                    var track = document.createElement('div');
                    track.className = splide.className;
                    splide.childNodes.forEach((fieldset) => {
                        track.appendChild(fieldset);
                    });
                    splide.parentElement.insertBefore(track, splide);
                    
                    
                    wrap.classList.add('keen-slider', 'splide__list');
                    /**
                     * Convert <fieldset> to <ul>
                     */
                    var oldNode = wrap, newNode = document.createElement('ul'), node, nextNode;
                    newNode.className = wrap.className;node = oldNode.firstChild;
                    while (node) {
                        nextNode = node.nextSibling;

                        var oldItem = node, newItem = document.createElement('li'), item, nextItem;
                        newItem.className = node.className;item = oldItem.firstChild;
                        while (item) {
                            nextItem = item.nextSibling;newItem.appendChild(item);item = nextItem;
                        }

                        console.log(newItem);
                        
                        newNode.appendChild(newItem);
                        node.remove();node = nextNode;
                    }
                    wrap = newNode;

                    const slider = new thisClass.Splide(splide.parentElement, {
                        type   : 'slide', // fade | loop
                        // padding: '5rem',
                        perPage: 5,
                        rewind : true,
                        perMove: 1,
                        omitEnd: true,
                        focus  : 'center', // 0 | 1 | 2
                        drag   : 'free', // 'free',
                        // snap   : true,
                        // direction: 'ttb', // rtl
                        // height   : '10rem',
                        // wheel    : true,
                        // autoWidth: true,
                        // autoplay: true,
                        pagination  : true,
                        gap    : '5px',
                        breakpoints: {
                            500: {perPage: 5, drag: 'free'},
                        },

                    });
                    wrapEL.parentElement.appendChild(wrap);wrapEL.remove();
                    slider.mount();
                } else {
                    var arrows = document.createElement('div');arrows.classList.add('keen-slider__arrows');
                    arrows.innerHTML = icons.left;
                    arrows.innerHTML += icons.right;
                    wrap.parentElement.appendChild(arrows);
                    setTimeout(() => {
                        wrap.style.maxWidth = '80%';
                        
                        const slider = new thisClass.KeenSlider(wrap, {
                            drag: false, // loop: true, mode: "free",
                            slides: {perView: 5, spacing: 5},
                            breakpoints: {
                                "(max-width: 500px)": {
                                    mode: "free", drag: true,
                                    // slides: {perView: 5, spacing: 5},
                                },
                            }
                        }, [
                            slider => {
                              slider.on('created', () => {
                                setTimeout(() => {
                                    wrap.style.maxWidth = 'unset';
                                    // console.log('Slider created');
                                    var theIntvalI = 0, theIntval = setInterval(() => {
                                        theIntvalI++;
                                        if(theIntvalI <= 20) {
                                            const event = new Event('resize');window.dispatchEvent(event);
                                        } else {
                                            clearInterval(theIntval);
                                        }
                                    }, 1500);
                                    
                                }, 2000);
                              })
                            },
                        ]);
                        PROMPTS.outfitSliders.push(slider);
                        
                        arrows.querySelectorAll('.svg_icon').forEach((elem) => {
                            elem.addEventListener('click', (event) => {
                                var arrow_mode = ((elem?.querySelector('svg'))?.dataset)?.arrow;
                                switch(arrow_mode) {
                                    case 'left':
                                        slider.prev();
                                        break;
                                    case 'right':
                                        slider.next();
                                        break;
                                    default:
                                        break;
                                }
                            });
                        });
                    }, 500);
                }
            }
        });
        
        // 
        PROMPTS.currentStep = 0;PROMPTS.do_pagination(true, thisClass);
    },
    init_group_select_events: (thisClass) => {
        document.querySelectorAll('.teddy_position__single:not([data-handled])').forEach((el) => {
            el.dataset.handled = true;
            el.addEventListener('click', (event) => {
                if(el.dataset?.type) {
                    PROMPTS.currentGroups = el.dataset.type;PROMPTS.groupSelected = true;
                    var popsBody = document.querySelector('.dynamic_popup');
                    if(popsBody) {
                        popsBody.classList.add('dynamic_popup__preload');
                        popsBody.innerHTML = `<div class="spinner-material"></div><h3>${PROMPTS.i18n?.pls_wait??'Please wait...'}</h3>`
                        setTimeout(() => {
                            document.body.dispatchEvent(new Event('gotproductpopupresult'));
                        }, 1000);
                    }
                }
            });
        });
    },
    generate_template: (thisClass) => {
        var json, html;
        json = PROMPTS.lastJson;
        var custom_fields = PROMPTS.lastJson.product.custom_fields;
        PROMPTS.groupExists = (
            custom_fields.sitting && custom_fields.sitting.length >= 1 &&
            custom_fields.standing && custom_fields.standing.length >= 1
        );
        custom_fields = PROMPTS.get_data(thisClass, true);
        var positions = PROMPTS.lastJson.product?.positions??{};
        positions = (typeof positions !== 'object')?{}:positions;

        
        html = '';
        html += (json.header)?`
            ${(json.header.product_photo)?`
            <div class="dynamic_popup__header">
                <div class="popup_close fa fa-times"></div>
                <span class="back2previous_step d-none" data-icon="dashicons-before dashicons-arrow-left-alt" type="button" data-react="back">Back</span>
                <img class="dynamic_popup__header__image" src="${thisClass.config?.siteLogo??''}" alt="">
                
                <div class="popup-prices">
                    <button class="btn btn-primary button">
                        <span>${PROMPTS.i18n?.add_to_cart??'Add To Cart'}</span><div class="spinner-circular-tube"></div>
                    </button>
                    <button class="calculated-prices" ${(PROMPTS.groupSelected)?'':`style="opacity: 0;"`}>
                        <span>${PROMPTS.i18n?.total??'Total'}</span><div class="price_amount">${(PROMPTS.lastJson.product && PROMPTS.lastJson.product.priceHtml)?PROMPTS.lastJson.product.priceHtml:(thisClass.config?.currencySign??'$')+'0.00'}</div>
                    </button>
                </div>
                
            </div>

            ${(! PROMPTS.groupSelected && PROMPTS.groupExists)?`
            <div class="teddy_position">
                <div class="teddy_position__wrap">
                    <div class="teddy_position__row">
                        ${['standing', 'sitting'].map(type => `
                        <div class="teddy_position__single" data-type="${type}">
                            <img src="${(positions[type] && positions[type] != '')?positions[type]:''}" alt="${type}" />
                            <span class="teddy_position__single__caption">${type.toUpperCase()}</span>
                        </div>
                        `).join('')}
                    </div>
                </div>
            </div>
            `:''}
            ${(PROMPTS.groupSelected)?`
            <div class="header_image" ${(json.header.product_photo != 'empty')?`style="background-image: url('${json.header.product_photo}');"`:``}></div>
            `:''}
            `:''}
        `:'';
        if(PROMPTS.groupSelected) {
            html += PROMPTS.generate_fields(thisClass);
        }
        return html;
    },
    generate_fields: (thisClass) => {
        var div, node, step, foot, footwrap, btn, back, prices, fields = PROMPTS.get_data(thisClass);
        if(!fields && (thisClass.config?.buildPath??false)) {
            return '<img src="'+thisClass.config.buildPath+'/icons/undraw_file_bundle_re_6q1e.svg">';
        }
        div = document.createElement('div');node = document.createElement('form');
        node.action=thisClass.ajaxUrl;node.type='post';node.classList.add('popup_body');
        if(!(fields?.standing)) {fields = {standing: fields, sitting: []};}
        Object.values(fields).forEach((group, i) => {
            group.forEach((field, i) => {
                step = PROMPTS.do_field(field);i++;
                step.dataset.step = field.fieldID;
                node.appendChild(step);
                PROMPTS.totalSteps=(i+1);
            });
        });
        foot = document.createElement('div');foot.classList.add('popup_foot');
        footwrap = document.createElement('div');footwrap.classList.add('popup_foot__wrap');
        footwrap.innerHTML = `
            <ul class="pagination_list">
                ${(thisClass?.progressSteps??[]).map((row, i)=>`
                <li class="pagination_list__item" data-order="${i}">
                    <span class="pagination_list__rounded">${row}</span>
                </li>
                `).join('')}
            </ul>
        `;

        back = document.createElement('button');back.classList.add('btn', 'btn-default', 'button');
        back.type='button';back.dataset.react = 'back';back.innerHTML=PROMPTS.i18n?.back??'Back';
        back.style.display = 'none';footwrap.appendChild(back);
        
        // prices = document.createElement('div');prices.classList.add('calculated-prices');
        // prices.innerHTML=`<span>${PROMPTS.i18n?.total??'Total'}</span><div class="price_amount">${(PROMPTS.lastJson.product && PROMPTS.lastJson.product.priceHtml)?PROMPTS.lastJson.product.priceHtml:(thisClass.config?.currencySign??'')+'0.00'}</div>`;
        // // document.querySelector('.popup-prices')?.appendChild(prices);
        // footwrap.appendChild(prices);
        
        btn = document.createElement('button');btn.classList.add('btn', 'btn-primary', 'button');
        btn.type='button';btn.dataset.react='continue';
        btn.innerHTML=`<span>${PROMPTS.i18n?.continue??'Continue'}</span><div class="spinner-circular-tube"></div>`;
        footwrap.appendChild(btn);
        
        div.appendChild(node);foot.appendChild(footwrap);div.appendChild(foot);
        return div.innerHTML;
    },
    str_replace: (str) => {
        var data = PROMPTS.lastJson,
        searchNeedles = {'product.name': data.product.name};
        Object.keys(searchNeedles).forEach((needle)=> {
            str = str.replaceAll(`{{${needle}}}`, searchNeedles[needle]);
        });
        return str;
    },
    get_data: (thisClass, both = false) => {
        var fields = PROMPTS.lastJson.product.custom_fields;
        if(!fields || fields == '') {return false;}
        if(!(fields?.standing)) {fields = {standing: fields, sitting: []};}
        // if(fields?.product) {fields = [...fields.product];}
        // console.log(fields);
        
        Object.keys(fields).forEach((key) => {
            if(key != 'product' && fields[key]) {fields[key].forEach((row, i) => {row.orderAt = (i+1);});}
        });
        return (both)?fields:fields[PROMPTS.currentGroups];
        // return fields;
    },
    do_field: (field, child = false) => {
        var fields, form, group, fieldset, input, level, span, option, head, image, others, body, div, info, title, done, imgwrap, i = 0;
        div = document.createElement('div');if(!child) {div.classList.add('popup_step', 'd-none', 'popup_step__'+field.type.replaceAll(' ', '-'));}
        
        if(!child) {
            done = document.createElement('button');done.type = 'button';done.dataset.type = 'done';
            done.innerHTML = PROMPTS.i18n?.next??'Next';div.appendChild(done);
        }
        if((field?.heading??'').trim() != '') {
            head = document.createElement('h2');
            head.innerHTML = PROMPTS.str_replace(field?.heading??'');
            div.appendChild(head);
        }
        
        if((field?.subtitle??'')!='') {
            info = document.createElement('p');
            info.innerHTML=PROMPTS.str_replace(field?.subtitle??'');
            div.appendChild(info);
        }
        
        input = level = false;
        fieldset = document.createElement('fieldset');
        fieldset.classList.add('popup_step__fieldset');
        
        if(field?.options && field.options.length <= 4) {fieldset.classList.add('big_thumb');}
        if((field?.label??'') != '') {
            level = document.createElement('label');
            level.innerHTML = PROMPTS.str_replace(field?.label??'');
            level.setAttribute('for',`field_${field?.fieldID??i}`);
        }
        
        switch (field.type) {
            case 'textarea':
                input = document.createElement('textarea');input.classList.add('form-control');
                input.name = 'field.'+field.fieldID;
                input.placeholder = PROMPTS.str_replace(field?.placeholder??'');
                input.id = `field_${field?.fieldID??i}`;input.innerHTML = field?.value??'';
                // if(field?.dataset??false) {input.dataset = field.dataset;}
                input.dataset.fieldId = field.fieldID;
                break;
            case 'input':case 'text':case 'button':case 'number':case 'date':case 'time':case 'local':case 'color':case 'range':
                input = document.createElement('input');input.classList.add('form-control');
                input.name = 'field.'+field.fieldID;
                input.placeholder = PROMPTS.str_replace(field?.placeholder??'');
                input.id = `field_${field?.fieldID??i}`;input.type = (field.type=='input')?'text':field.type;
                // if(field?.dataset??false) {input.dataset = field.dataset;}
                input.value = field?.value??'';input.dataset.fieldId = field.fieldID;
                if(level) {fieldset.appendChild(level);}
                if(input) {fieldset.appendChild(input);}
                if(input || level) {div.appendChild(fieldset);}
                break;
            case 'select':
                input = document.createElement('select');input.classList.add('form-control');
                input.name = 'field.'+field.fieldID;input.id = `field_${field?.fieldID??i}`;
                // if(field?.dataset??false) {input.dataset = field.dataset;}
                input.dataset.fieldId = field.fieldID;
                (field?.options??[]).forEach((opt,i)=> {
                    option = document.createElement('option');option.value=opt?.label??'';option.innerHTML=opt?.label??'';option.dataset.index = i;
                    input.appendChild(option);
                });
                
                if(level) {fieldset.appendChild(level);}
                if(input) {fieldset.appendChild(input);}
                if(input || level) {div.appendChild(fieldset);}
                break;
            case 'doll':case 'radio':case 'checkbox':
                input = document.createElement('div');input.classList.add('form-wrap');
                field.options = (field.options)?field.options:[];
                field.type = (field.type == 'doll')?'radio':field.type;
                if((field?.title??'') != '') {
                    title = document.createElement('h4');title.classList.add('title');
                    title.innerHTML = field?.title??'';fieldset.appendChild(title);
                }
                // field.options = field.options.reverse();
                Object.values(field.options).forEach((opt, optI)=> {
                    if(opt && opt.label) {
                        level = document.createElement('label');level.classList.add('form-control-label', 'form-control-'+field.type);
                        // level.setAttribute('for', `field_${field?.fieldID??i}_${optI}`);
                        if(opt.input) {level.classList.add('form-flexs');}
                        span = document.createElement('span');
                        if(opt.imageUrl) {
                            imgwrap = document.createElement('div');
                            imgwrap.classList.add('form-control-'+field.type+'__imgwrap');
                            image = document.createElement('img');image.src = opt.imageUrl;
                            image.alt = opt.label;// level.appendChild(image);
                            level.classList.add('form-control-'+field.type+'__image');
                            input.classList.add('form-wrap__image');
                            if((opt?.thumbUrl??false) && opt.thumbUrl != '') {
                                image.src = opt.thumbUrl;image.dataset.outfit = opt.imageUrl;
                            }
                            imgwrap.appendChild(image);level.appendChild(imgwrap);
                        }
                        if(!(opt?.input)) {
                            opt.cost = (opt?.cost)?opt.cost:false;
                            span.innerHTML = `<span title="${thisClass.esc_attr(opt.label)}">${opt.label}</span>`+(
                                (opt.cost && opt.cost !== '' && parseFloat(opt.cost) !== 'NaN')?(
                                    ` <strong>${thisClass.config?.currencySign??'$'} ${parseFloat(opt.cost).toFixed(2)}</strong>`
                                ):` <strong class="hiddenable zero_amount">${thisClass.config?.currencySign??'$'} 0.00</strong>`
                           );
                        } else {
                            others = document.createElement('input');others.type='text';
                            others.name='field.'+field.fieldID+'.others';others.placeholder=opt.label;
                            others.dataset.fieldId = field.fieldID;others.dataset.index = optI;
                            span.appendChild(others);
                        }
                        option = document.createElement('input');option.value=opt?.value??opt.label;
                        option.name='field.'+field.fieldID+'.option'+((field.type == 'checkbox')?'.' + optI:'');
                        option.dataset.index = optI;option.dataset.fieldId = field.fieldID;
                        option.id=`field_${field?.fieldID??i}_${optI}`;option.type=field.type;
                        if(field?.layer??false) {option.dataset.layer=field.layer;}
                        if((opt?.cost??'') == '') {opt.cost = '0';}
                        if(opt?.cost) {option.dataset.cost=opt.cost;}
                        if(opt?.skip) {option.dataset.skip = true;}
                        if(child) {option.dataset.preview=child;}
                        if((opt?.product) && opt.product != '') {
                            option.dataset.product = opt.product;
                        }
                        level.appendChild(option);level.appendChild(span);input.appendChild(level);
                        fieldset.appendChild(input);div.appendChild(fieldset);
                    }
                });
                break;
            case 'password':
                group = document.createElement('div');group.classList.add('input-group', 'mb-3');
                input = document.createElement('input');input.classList.add('form-control');
                input.name = 'field.'+field.fieldID;
                input.placeholder = PROMPTS.str_replace(field?.placeholder??'');
                input.id = `field_${field?.fieldID??i}`;input.type = (field.type=='input')?'text':field.type;
                // if(field?.dataset??false) {input.dataset = field.dataset;}
                input.value = field?.value??'';input.dataset.fieldId = field.fieldID;
                var eye = document.createElement('div');
                eye.classList.add('input-group-append', 'toggle-password');
                eye.innerHTML = '<i class="fa fa-eye"></i>';
                group.appendChild(input);group.appendChild(eye);
                if(level) {fieldset.appendChild(level);}
                if(input) {fieldset.appendChild(group);}
                if(input || level) {div.appendChild(fieldset);}
                break;
            case 'confirm':
                input = document.createElement('div');input.classList.add('the-success-icon');
                input.innerHTML = field?.icon??'';
                fieldset.appendChild(input);div.appendChild(fieldset);
                break;
            case 'voice':
                input = document.createElement('div');input.classList.add('do_recorder');
                // if(field?.dataset??false) {input.dataset = field.dataset;}
                input.innerHTML = field?.icon??'';input.dataset.cost = field?.cost??0;
                fieldset.appendChild(input);div.appendChild(fieldset);
                break;
            case 'outfit':
                fields = document.createElement('div');fields.classList.add('form-fields', 'form-fields__group', 'form-fields__group__'+(field.type).replaceAll(' ', ''));
                (field?.groups??[]).forEach((group, groupI)=> {
                    group.fieldID = (field?.fieldID??0)+'.'+(group?.fieldID??groupI);
                    if(group?.options) {
                        /**
                         * Filterout all items those are not in stock
                         */
                        // stock_status
                        group.options = Object.values(group.options);
                        group.options = group.options.filter(opt => !(opt?.stock_status) || ['instock'].includes(opt?.stock_status));
                        group.options?.push({
                            label: PROMPTS.i18n?.skip??'Skip',
                            next: '', image: '',// cost: '0',
                            thumb: '', thumbUrl: '', skip: true
                        });
                    }
                    fields.appendChild(PROMPTS.do_field(group, true));
                });
                fieldset.appendChild(fields);div.appendChild(fieldset);
                break;
            case 'info':
                fields = document.createElement('div');fields.dataset.visibility = 1;
                fields.classList.add('form-fields', 'form-fields__group', 'form-fields__group__'+(field.type).replaceAll(' ', ''));
                // field.groups = field.groups.reverse();
                var inputsArgs = {}, inputs = {
                    teddy_name: {
                        type: 'text',
                        label: PROMPTS.i18n?.teddyfullname??'Teddy name',
                        // placeholder: PROMPTS.i18n?.teddyfullname??'Teddy full Name',
                        dataset: {title: PROMPTS.i18n?.teddyfullname??'Teddy full Name'}
                    },
                    choose_name: {
                        type: 'checkbox',
                        label: PROMPTS.i18n?.chooseaname4me??'Choose a name for me',
                        // placeholder: PROMPTS.i18n?.teddyfullname??'Teddy full Name',
                        dataset: {title: PROMPTS.i18n?.teddyfullname??'Teddy full Name'},
                        options: [{value: 'tochoose', label: PROMPTS.i18n?.chooseaname4me??'Choose a name for me'}]
                    },
                    teddy_birth: {
                        type: 'date', // default: new Date().toLocaleDateString('en-US'),
                        label: PROMPTS.i18n?.teddybirth??'Birth date',
                        // placeholder: PROMPTS.i18n?.dtofteddybirth??'Date of teddy\'s birth',
                        dataset: {title: PROMPTS.i18n?.teddybirth??'Birth date'}
                    },
                    teddy_reciever: {
                        type: 'text',
                        label: PROMPTS.i18n?.recieversname??'Reciever\'s Name',
                        // placeholder: PROMPTS.i18n?.recieversname??'Reciever\'s Name',
                        dataset: {title: PROMPTS.i18n?.recieversname??'Reciever\'s Name'}
                    },
                    teddy_sender: {
                        type: 'text',
                        label: PROMPTS.i18n?.sendersname??'Created with love by',
                        // placeholder: PROMPTS.i18n?.sendersname??'Created with love by',
                        dataset: {title: PROMPTS.i18n?.sendersname??'Created with love by'}
                    }
                };
                Object.keys(inputs).forEach((type, typeI) => {
                    inputsArgs = {
                        fieldID: (field?.fieldID??0)+'.'+(type?.fieldID??typeI),
                        ...inputs[type]
                    };
                    if(type == 'choose_name' && Object.keys(inputs)[(typeI-1)] == 'teddy_name') {
                        // field[type] = 'on';
                        inputsArgs.default = inputsArgs.value = false;
                    }
                    // if(field[type] == 'on') {}
                    fields.appendChild(PROMPTS.do_field(inputsArgs, true));
                });
                inputsArgs = {
                    fieldID: (field?.fieldID??0)+'.'+10,
                    type: 'button', value: PROMPTS.i18n?.continue??'Continue',
                };
                var btn_next = PROMPTS.do_field(inputsArgs, true);
                btn_next.querySelector('input').dataset.actLike = 'next';
                fields.appendChild(btn_next);
                inputsArgs = {
                    fieldID: (field?.fieldID??0)+'.'+11,
                    type: 'button', value: PROMPTS.i18n?.skip??'Skip',
                };
                var btn_skip = PROMPTS.do_field(inputsArgs, true);
                btn_skip.querySelector('input').dataset.actLike = 'skip';
                fields.appendChild(btn_skip);

                fieldset.appendChild(fields);div.appendChild(fieldset);
                break;
            default:
                // console.log('Failed implimenting '+field.type);
                input = level = false;
                break;
        }
        i++;
        if((field?.extra_fields??false)) {
            field.extra_fields.forEach((extra) => {
                div.appendChild(PROMPTS.do_field(extra, true));
            });
        }
        return div;
    },
    do_submit: async (thisClass, el) => {
        var data = thisClass.generate_formdata(el);
        var args = thisClass.lastReqs = {
            best_of: 1,frequency_penalty: 0.01,presence_penalty: 0.01,top_p: 1,
            max_tokens: parseInt(data?.max_tokens??700),temperature: 0.7,model: data?.model??"text-davinci-003",
        };
        try {
            args.prompt = thisClass.str_replace(
                Object.keys(data).map((key)=>'{{'+key+'}}'),
                Object.values(data),
                thisClass.popup.thefield?.syntex??''
           );
            PROMPTS.lastJson = await thisClass.openai.createCompletion(args);
            var prompt = thisClass.popup.generate_results(thisClass);
            document.querySelector('#the_generated_result').value = prompt;
            // console.log(prompt);
        } catch (error) {
            thisClass.openai_error(error);
        }
    },
    do_pagination: async (plus, thisClass) => {
        var step, root, header, field, back, data, submit;PROMPTS.currentStep = PROMPTS?.currentStep??0;
        root = '.fwp-swal2-popup .popup_body .popup_step';
        if(!PROMPTS.lastJson.product || !PROMPTS.lastJson.product.custom_fields || PROMPTS.lastJson.product.custom_fields=='') {return;}
        if(PROMPTS?.global_cartBtn || await PROMPTS.beforeSwitch(thisClass, plus)) {
            PROMPTS.currentStep = (plus)?(
                (PROMPTS.currentStep < PROMPTS.totalSteps)?(PROMPTS.currentStep+1):PROMPTS.currentStep
           ):(
                (PROMPTS.currentStep > 0)?(PROMPTS.currentStep-1):PROMPTS.currentStep
           );
            if(PROMPTS.currentStep <= 0) {return;}
            submit = document.querySelector('.popup_foot .button[data-react="continue"]');
            if(submit && submit.classList) {
                if(PROMPTS.currentStep >= (PROMPTS.totalSteps-1) || PROMPTS?.global_cartBtn) {
                    submit.firstElementChild.innerHTML = PROMPTS.i18n?.add_to_cart??'Add To Cart';
                } else {
                    submit.firstElementChild.innerHTML = PROMPTS.i18n?.continue??'Continue';
                }
            }
            
            field = PROMPTS.get_data(thisClass).find((row)=>row.orderAt==PROMPTS.currentStep);
            if(plus && field && field.type == 'confirm' && ! await PROMPTS.do_search(field, thisClass)) {
                return false;
            }

            if(PROMPTS.currentStep >= PROMPTS.totalSteps || PROMPTS?.global_cartBtn) {
                step = document.querySelector('.popup_step.step_visible');data = [];
                data = thisClass.transformObjectKeys(thisClass.generate_formdata(document.querySelector('.popup_body')));
                
                submit = document.querySelector('.popup_foot .button[data-react="continue"]');
                if(submit && submit.classList) {
                    submit.setAttribute('disabled', true);
                    PROMPTS.currentStep--;

                    // data.product = PROMPTS.lastJson.product.id;
                    var formdata = new FormData();
                    formdata.append('action', 'futurewordpress/project/ajax/cart/add');
                    formdata.append('_nonce', thisClass.ajaxNonce);
                    const generated = await PROMPTS.get_formdata(thisClass, formdata);
                    
                    var charges = PROMPTS.sortout_unnecessery_data(thisClass.popupCart.additionalPrices);
                    formdata.append('charges', JSON.stringify(charges));
                    formdata.append('dataset', JSON.stringify(generated));
                    formdata.append('product_id', PROMPTS.lastJson.product.id);
                    formdata.append('quantity', 1);
                    thisClass.sendToServer(formdata);
                    PROMPTS.global_cartBtn = false;

                    setTimeout(() => {submit.removeAttribute('disabled');}, 100000);
                }
                // if(PROMPTS.validateField(step, data, thisClass)) {
                // } else {console.log('Didn\'t Submit');}
            } else {
                document.querySelectorAll('.popup_foot .button[data-react="back"], .back2previous_step[data-react="back"]').forEach((back) => {
                    if(!plus && PROMPTS.currentStep<=1) {back.classList.add('invisible');}
                    else {back.classList.remove('invisible');}
                });
                
                field = PROMPTS.get_data(thisClass).find((row)=>row.orderAt==PROMPTS.currentStep);
                header = document.querySelector('.header_image');
                if(header) {
                    if(field && field?.headerbgurl && field.headerbgurl != '') {
                        jQuery(header).css('background-image', 'url('+field.headerbgurl+')');
                    }
                }
                document.querySelectorAll(root+'.step_visible').forEach((el) => {el.classList.add('d-none');el.classList.remove('step_visible');});
                step = document.querySelector(root+'[data-step="'+(field?.fieldID??PROMPTS.currentStep)+'"]');
                if(step) {
                    if(!plus) {step.classList.add('popup2left');}
                    step.classList.remove('d-none');setTimeout(() => {step.classList.add('step_visible');},300);
                    if(!plus) {setTimeout(() => {step.classList.remove('popup2left');},1500);}
                }

                // Change swal step current one.
                var popup = document.querySelector('.dynamic_popup');
                var popupParent = (popup)?popup.parentElement:document.querySelector('.swal2-html-container');
                thisClass.frozenNode = document.createElement('div');
                thisClass.frozenNode.appendChild(popup);

                var find = PROMPTS.get_data(thisClass).find((row)=>row.orderAt == PROMPTS.currentStep);
                var found = PROMPTS.progressSteps.indexOf(find?.steptitle??false);
                thisClass.Swal.update({
                    currentProgressStep: ((found)?found:(PROMPTS.currentStep-1)),
                    // progressStepsDistance: (PROMPTS.progressSteps.length<=5)?'2rem':(
                    //     (PROMPTS.progressSteps.length>=8)?'0rem':'1rem'
                    //)
                });
                // thisClass.Swal.update({currentProgressStep: (PROMPTS.currentStep-1)});

                if(popupParent) {popupParent.innerHTML = '';popupParent.appendChild(thisClass.frozenNode.childNodes[0]);}
                setTimeout(() => {PROMPTS.work_with_pagination(thisClass);}, 300);
                
            }
        } else {
            console.log('Proceed failed');
        }
    },
    beforeSwitch: async (thisClass, plus) => {
        var field, back, next, elem, last;last = elem = false;
        if(plus) {
            field = PROMPTS.get_data(thisClass).find((row)=>row.orderAt==PROMPTS.currentStep);
            elem = document.querySelector('.popup_body .popup_step[data-step="'+(field?.fieldID??PROMPTS.currentStep)+'"]');
            elem = (elem && elem.nextElementSibling)?parseInt(elem.nextElementSibling.dataset?.step??0):0;
            // if(!elem || typeof elem.nextElementSibling === 'undefined') {return false;}
            if(elem>=1 && (PROMPTS.currentStep+1) < elem) {
                last = PROMPTS.currentStep;
                PROMPTS.currentStep = (elem-1);
            }
        }
        if(plus && PROMPTS.totalSteps!=0 && PROMPTS.totalSteps<=PROMPTS.currentStep) {
            // Submitting popup!
            if(elem) {PROMPTS.currentStep = last;}
            return (PROMPTS.totalSteps != PROMPTS.currentStep);
        }
        if(plus) {
            var data = thisClass.generate_formdata(document.querySelector('.popup_body'));
            var step = document.querySelector('.popup_step.step_visible'), prev = [];
            if(!step) {return (PROMPTS.currentStep<=0);}
            if(!PROMPTS.validateField(step, data, thisClass)) {return false;}

            step.querySelectorAll('input, select').forEach((el,ei) => {
                // el is the element input
                if(!prev.includes(el.name) && data[el.name] && data[el.name]==el.value) {
                    // item is the fieldset
                    var item = PROMPTS.get_data(thisClass).find((row, i)=>row.fieldID==el.dataset.fieldId);
                    if(item) {
                        // opt is the options
                        var opt = (item?.options??[]).find((opt,i)=>i==el.dataset.index);
                        // console.log(item, opt);
                        if(!opt) {
                            var group = (item?.groups??[]).find((grp,i)=>grp.fieldID==el.dataset.fieldId);
                            // console.log(group, item.groups);
                            if(group) {
                                opt = (group?.options??[]).find((opt,i)=>i==el.dataset.index);
                                // console.log(opt);
                            }
                        }
                        if(opt) {
                            prev.push(el.dataset.index);
                            if(!item.is_conditional && opt.next && opt.next!='') {
                                next = PROMPTS.get_data(thisClass).find((row)=>row.fieldID==parseInt(opt.next));
                                // console.log(next);
                                if(next) {
                                    next.returnStep = item.orderAt;
                                    PROMPTS.currentStep = ((next?.orderAt??(next?.fieldID??0))-1);
                                    return true;
                                }
                                return false;
                            } else {
                                // return false;
                            }
                        }
                    }
                }
                return true;
            });
        }
        if(!plus) {
            var current = PROMPTS.get_data(thisClass).find((row)=>row.orderAt==PROMPTS.currentStep);
            var returnStep = current?.returnStep??false;
            var next = PROMPTS.get_data(thisClass).find((row)=>row.orderAt==returnStep);
            if(returnStep && next) {
                PROMPTS.currentStep = (parseInt(returnStep)+1);
                current.returnStep=false;
                return true;
            }
        }
        
        return true;
        // return (!plus || PROMPTS.currentStep < PROMPTS.totalSteps);
        // setTimeout(() => {return true;},100);
    },
    validateField: (step, data, thisClass) => {
        // data = thisClass.generate_formdata(document.querySelector('.popup_body'));
        var fieldValue, field;fieldValue = step.querySelector('input, select');
        fieldValue = (fieldValue)?fieldValue?.name??false:false;
        field = PROMPTS.get_data(thisClass).find((row)=>row.fieldID==step.dataset.step);
        if(!field) {return false;}

        thisClass.Swal.resetValidationMessage();
        switch (field?.type??false) {
            case 'text':case 'number':case 'color':case 'date':case 'time':case 'local':case 'range':case 'checkbox':case 'radio':
                if(field.required && (!data[fieldValue] || data[fieldValue]=='')) {
                    thisClass.Swal.showValidationMessage('You can\'t leave it blank.');
                    return false;
                }
                break;
            default:
                return true;
                break;
        }
        return true;
    },
    do_search: async (field, thisClass) => {
        var submit = document.querySelector('.popup_foot .button[data-react="continue"]');
        if(submit) {submit.disabled = true;}
        var args, request, formdata;
        args = thisClass.transformObjectKeys(thisClass.generate_formdata(document.querySelector('.popup_body')));
        formdata = new FormData();
        // for (const key in args) {
        //     formdata.append(key, args[key]);
        // }
        args.field.product = PROMPTS.lastJson.product.name;
        formdata.append('formdata', JSON.stringify(args));
        formdata.append('_nonce', thisClass.ajaxNonce);
        formdata.append('action', 'futurewordpress/project/ajax/search/popup');
    
        request = await fetch(thisClass.ajaxUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formdata
        })
        .then(response => response.json())
        .then(data => console.log(data))
        .catch(err => console.error(err));
        
        if(submit) {submit.removeAttribute('disabled');}
        return true;
    },
    on_Closed: (thisClass) => {
        var popup = document.querySelector('.dynamic_popup .tc-extra-product-options.tm-extra-product-options');
        var parent = document.querySelector('.tc-extra-product-options-parent');
        if(parent && popup) {parent.innerHTML = '';parent.appendChild(popup);}
        PROMPTS.groupSelected = false;PROMPTS.currentGroups = 'standing';
        return true;
    },
    get_formdata: async (thisClass, formdata = false) => {
        var form = thisClass.generate_formdata(document.querySelector('.popup_body'));
        Object.keys(form).forEach((name) => {
            var elem = document.querySelector('[name="'+name+'"]');
            if(elem.value.trim().toLocaleLowerCase() == form[name].trim().toLocaleLowerCase()) {
                var split = name.split('.');split[1] = parseInt(split[1]);
                var field = PROMPTS.get_data(thisClass).find((row)=>row.fieldID==split[1]);
                var img = elem.previousElementSibling;
                var match = (field?.options??[]).find((row)=>row.label && row.label.toLocaleLowerCase()==form[name].toLocaleLowerCase());
                form[name] = {
                    title: field?.title??(field?.steptitle??(field?.subtitle??'')),
                    name: elem.name,
                    value: form[name],
                    price: match?.cost??(elem.dataset?.cost??0),
                    image: match?.imageUrl??(img?.src??((img?.dataset??{})?.outfit??'')),
                    // field: match
                };
            } else {
                var split = name.split('.');split[1] = parseInt(split[1]);
                var field = PROMPTS.get_data(thisClass).find((row)=>row.fieldID==split[1]);
                var img = elem.previousElementSibling;var split = name.split('.');split[1] = parseInt(split[1]);
                switch(field.type) {
                    case 'outfit':
                        split[2] = parseInt(split[2]);
                        var match = ((field?.groups??[])[split[2]]?.options??[]).find((row)=>row.label.trim().toLocaleLowerCase()==form[name].trim().toLocaleLowerCase());
                        
                        form[name] = {
                            title: field?.title??(field?.steptitle??(field?.subtitle??'')),
                            name: elem.name,
                            value: form[name],
                            price: match?.cost??(elem.dataset?.cost??0),
                            image: match?.imageUrl??(img?.src??(img.dataset?.outfit??'')),
                            // field: match
                        };
                        break;
                    case 'voice':
                        var match = (field?.options??[]).find((row)=>(row?.label??'').trim().toLocaleLowerCase()==form[name].trim().toLocaleLowerCase());
                        form[name] = {
                            title: field?.title??(field?.steptitle??(field?.subtitle??'')),
                            name: elem.name,
                            value: form[name],
                            price: match?.cost??(elem.dataset?.cost??0),
                            image: match?.imageUrl??(img?.src??(img.dataset?.outfit??'')),
                            // field: match
                        };
                        console.log('voice', match);
                        break;
                    default:
                        var match = (field?.options??[]).find((row)=>(row?.label??'').trim().toLocaleLowerCase()==form[name].trim().toLocaleLowerCase());
                        form[name] = {
                            title: field?.title??(field?.steptitle??(field?.subtitle??'')),
                            name: elem.name,
                            value: form[name],
                            price: match?.cost??(elem.dataset?.cost??0),
                            image: match?.thumbUrl??(match?.imageUrl??(img?.src??(img.dataset?.outfit??''))),
                            // field: match
                        };
                        break;
                }
            }
        });
        const hasVoice = PROMPTS.get_data(thisClass).find((row)=>(row.type=='voice'));
        if(hasVoice) {
            var voiceArgs = {
                title: PROMPTS.i18n?.voice??'Voice',
                name: '', image: '',
            };
            var voice_name_path = 'field.'+(hasVoice?.orderAt??115)+'.'+(hasVoice?.fieldID??'115');
            if(thisClass.voiceRecord.recordedBlob !== null) {
                const voiceName = await thisClass.voiceRecord.recordedFileName();
                // PROMPTS.voices[voiceName] = await fetch(thisClass.voiceRecord.audioPreview.src).then(r => r.blob());
                voiceArgs.value = voiceName;voiceArgs.voice = voiceName;
                PROMPTS.voices[voiceName] = thisClass.voiceRecord.recordedBlob;
                voiceArgs.cost = parseFloat(thisClass.voiceRecord.recordButton.dataset?.cost??'0');
                if(formdata) {
                    formdata.append('voice', PROMPTS.voices[voiceName], voiceName);
                }
                form[voice_name_path] = voiceArgs;
            } else {
                if (document.querySelector('.do_recorder__released')) {
                    voiceArgs.voiceLater = true;
                    form[voice_name_path] = voiceArgs;
                }
            }
        }
        form = thisClass.transformObjectKeys(form);
        // PROMPTS.get_data(thisClass).map((row)=>(row.type=='voice')?row:false);
        return form;
    },
    sortout_unnecessery_data: (form) => {
        console.log(form);
        form?.forEach((row, i) => {
            if(row.item == 'tochoose') {delete form[i];}
        });
        return form;
    },




    work_with_pagination: (thisClass) => {
        var steps = document.querySelector('.swal2-progress-steps');
        var pagin = document.querySelector('.pagination_list');
        // if(pagin) {pagin.parentElement.insertBefore(steps, pagin);pagin.remove();}
        if(pagin) {pagin.innerHTML = steps.innerHTML;pagin.classList.add('swal2-progress-steps');}

        var submit = document.querySelector('.popup_foot .button[data-react="continue"]');
        if(submit) {
            submit.firstElementChild.innerHTML = PROMPTS.i18n?.add_to_cart??'Add To Cart';
        }

        setTimeout(() => {
            document.querySelectorAll('.dynamic_popup .popup_foot__wrap .swal2-progress-steps .swal2-progress-step').forEach((step, index) => {
                step.dataset.index = (index + 1);
                step.addEventListener('click', (event) => {
                    event.preventDefault();
                    document.querySelectorAll('.dynamic_popup .popup_body .popup_step').forEach((el, elI) => {
                        el.classList.remove('step_visible');el.classList.add('d-none');
                        if(step.dataset.index == el.dataset.step) {
                            el.classList.add('step_visible');el.classList.remove('d-none');
                            document.querySelector('.popup_body')?.classList.add('visible_card');
                            document.querySelector('.popup_body')?.parentElement.classList.add('visible_pops');
                            if(el.dataset?.step) {
                                var presentStep = PROMPTS.get_data(thisClass).find((row)=>row.orderAt == el.dataset.step);
                                if(presentStep) {
                                    document.querySelector('.popup_body').dataset.stepType = presentStep.type;
                                }
                            }

                            PROMPTS.global_cartBtn = true;

                            PROMPTS.currentStep = el.dataset.step;
                            var field = PROMPTS.get_data(thisClass).find((row)=>row.orderAt==PROMPTS.currentStep);
                            var header = document.querySelector('.header_image');
                            if(header) {
                                if(field && field.headerbgurl != '') {
                                    jQuery(header).css('background-image', 'url('+field.headerbgurl+')');
                                }
                            }
                            document.querySelector('.popup_foot__wrap')?.classList.add('d-none');
                        }
                    });
                })
            });
        }, 300);
    }
    
};
export default PROMPTS;