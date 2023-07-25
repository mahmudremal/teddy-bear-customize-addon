const PROMPTS = {
    i18n: {},voices: {},
    get_template: (thisClass) => {
        var json, html;
        html = document.createElement('div');html.classList.add('dynamic_popup');
        if(PROMPTS.lastJson) {
            html.innerHTML = PROMPTS.generate_template(thisClass);
        } else {
            html.innerHTML = `<div class="spinner-material"></div><h3>${PROMPTS.i18n?.pls_wait??'Please wait...'}</h3>`;
        }
        return html;
    },
    init_prompts: (thisClass) => {
        PROMPTS.core = thisClass;
    },
    init_events: (thisClass) => {
        document.querySelectorAll('.popup_foot .button[data-react]').forEach((el) => {
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
        document.querySelectorAll('.form-control[name="field.9000"]:not([data-handled])').forEach((input)=>{
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
        document.querySelectorAll('.popup_close:not([data-handled])').forEach((el)=>{
            el.dataset.handled = true;
            el.addEventListener('click', (event)=>{
                event.preventDefault();
                if(confirm(PROMPTS.i18n?.rusure2clspopup??'Are you sure you want to close this popup?')) {
                    thisClass.Swal.close();
                }
            });
        });

        document.querySelectorAll('.dynamic_popup').forEach((popup)=>{
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
                    popup.querySelectorAll('[id]').forEach((el)=>{el.id = el.id+'_popup';});
                    popup.querySelectorAll('label[for]').forEach((el)=>{el.setAttribute('for', el.getAttribute('for')+'_popup');});
                }, 200);

                document.querySelectorAll('.dynamic_popup .tm-collapse').forEach((el)=>{
                    var head = el.firstChild;var body = el.querySelector('.tm-collapse-wrap');
                    head.classList.remove('toggle-header-closed');head.classList.add('toggle-header-open');
                    head.querySelector('.tcfa.tm-arrow').classList.add('tcfa-angle-up');
                    head.querySelector('.tcfa.tm-arrow').classList.remove('tcfa-angle-down');
                    body.classList.remove('closed');body.classList.add('open', 'tm-animated', 'fadein');
                });
            }
        });
        document.querySelectorAll('.dynamic_popup input[type="checkbox"], .dynamic_popup input[type="radio"]').forEach((el)=>{
            el.addEventListener('change', (event)=>{
                if(el.parentElement) {
                    if(el.parentElement.classList.contains('form-control-checkbox__image')) {
                        if(el.checked) {
                            el.parentElement.classList.add('checked_currently');
                        } else {
                            el.parentElement.classList.remove('checked_currently');
                        }
                    } else if(el.parentElement.classList.contains('form-control-radio__image')) {
                        document.querySelectorAll('input[name="'+el.name+'"]').forEach((radio)=>{
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
        document.querySelectorAll('.dynamic_popup .popup_body input[type=checkbox][data-cost], .dynamic_popup .popup_body input[type=radio][data-cost]').forEach((el)=>{
            el.addEventListener('change', (event)=>{
                var img, frame, title, identity, frameHeight, frameWidth;
                frameHeight = 400;frameWidth = 350;
                frame = document.querySelector('.dynamic_popup .header_image');
                if(!frame) {return;} // I can also give here a toast that frame element not found.
                identity = el.name.replaceAll('.', '')+'-'+el.id;

                if(el.checked) {
                    img = document.createElement('img');img.src = el.previousElementSibling.src;
                    if(el.previousElementSibling.dataset?.outfit??false) {img.src = el.previousElementSibling.dataset.outfit;}
                    img.height = frameHeight;img.width = frameWidth;img.id = identity;
                    img.alt = el.previousElementSibling.alt;img.dataset.name = el.name;
                    if((el.dataset?.layer??false) && el.dataset.layer != '') {img.style.zIndex = parseInt(el.dataset.layer);}
                    
                    if(el.type == 'radio') {
                        frame.querySelectorAll('img[data-name="'+el.name+'"').forEach((images)=>{images.remove();});
                    }
                    if((el.dataset?.preview??'false') == 'true') {frame.appendChild(img);}
                    
                    if(el.dataset?.cost??false) {
                        switch(el.type) {
                            case 'radio':
                                document.querySelectorAll('.dynamic_popup input[type="radio"][name="'+el.name+'"][data-cost]').forEach((radio)=>{
                                    if(radio.dataset?.calculated??false) {
                                        thisClass.popupCart.removeAdditionalPrice(radio.value, parseFloat(radio.dataset.cost));// radio.checked = false;
                                        radio.removeAttribute('data-calculated');
                                    }
                                    if(radio.checked) {
                                        if(radio.parentElement.classList.contains('checked_currently')) {
                                            // console.log('Has class');
                                            thisClass.popupCart.addAdditionalPrice(radio.value, parseFloat(radio.dataset.cost), false);
                                            radio.dataset.calculated = true;
                                        } else {
                                            // console.log('Hasn\'t class');
                                            thisClass.popupCart.removeAdditionalPrice(radio.value, parseFloat(radio.dataset.cost));
                                            radio.removeAttribute('data-calculated');
                                            frame.querySelectorAll('img[data-name="'+radio.name+'"]').forEach((el)=>el.remove());
                                        }
                                    }
                                });
                                break;
                            case 'checkbox':
                                thisClass.popupCart.addAdditionalPrice(el.value, parseFloat(el.dataset.cost), false);
                                break;
                            default:
                                break;
                        }
                    }
                } else {
                    frame.querySelectorAll('#'+identity).forEach((images)=>{images.remove();});
                    thisClass.popupCart.removeAdditionalPrice(el.value, parseFloat(el.dataset.cost));
                }
            });
        });
        document.querySelectorAll('.dynamic_popup input[type="date"]').forEach((el)=>{
            el.type = 'text';thisClass.flatpickr(el, {enableTime: false, dateFormat: "d M, Y"});
        });
        
        PROMPTS.currentStep=0;PROMPTS.do_pagination(true, thisClass);
    },
    generate_template: (thisClass) => {
        var json, html;
        json = PROMPTS.lastJson;
        html = '';
        html += (json.header)?`
            ${(json.header.product_photo)?`<div class="header_image" ${(json.header.product_photo != 'empty')?`style="background-image: url('${json.header.product_photo}');"`:``}></div>`:''}
        `:'';
        html += PROMPTS.generate_fields(thisClass);
        return html;
    },
    generate_fields: (thisClass) => {
        var div, node, step, foot, btn, back, close, prices, fields = PROMPTS.get_data(thisClass);
        if(!fields && (thisClass.config?.buildPath??false)) {
            return '<img src="'+thisClass.config.buildPath+'/icons/undraw_file_bundle_re_6q1e.svg">';
        }
        close = document.createElement('div');close.classList.add('popup_close', 'fa', 'fa-times');
        div = document.createElement('div');node = document.createElement('form');
        node.action=thisClass.ajaxUrl;node.type='post';node.classList.add('popup_body');
        fields.forEach((field, i) => {
            step = PROMPTS.do_field(field);i++;
            step.dataset.step = field.fieldID;
            node.appendChild(step);
            PROMPTS.totalSteps=(i+1);
        });
        foot = document.createElement('div');foot.classList.add('popup_foot');

        back = document.createElement('button');back.classList.add('btn', 'btn-default', 'button');
        back.type='button';back.dataset.react = 'back';back.innerHTML=PROMPTS.i18n?.back??'Back';
        foot.appendChild(back);
        
        prices = document.createElement('div');prices.classList.add('calculated-prices');
        prices.innerHTML=`<span>${PROMPTS.i18n?.total??'Total'}</span><div class="price_amount">${(PROMPTS.lastJson.product && PROMPTS.lastJson.product.priceHtml)?PROMPTS.lastJson.product.priceHtml:(thisClass.config?.currencySign??'')+'0.00'}</div>`;
        foot.appendChild(prices);
        
        btn = document.createElement('button');btn.classList.add('btn', 'btn-primary', 'button');
        btn.type='button';btn.dataset.react='continue';
        btn.innerHTML=`<span>${PROMPTS.i18n?.continue??'Continue'}</span><div class="spinner-circular-tube"></div>`;
        foot.appendChild(btn);
        
        div.appendChild(close);div.appendChild(node);div.appendChild(foot);
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
    get_data: (thisClass) => {
        var fields = PROMPTS.lastJson.product.custom_fields;
        if(!fields || fields=='') {return false;}
        fields.forEach((row, i) => {row.orderAt = (i+1);});
        return fields;
    },
    do_field: (field, child = false) => {
        var fields, form, group, fieldset, input, level, span, option, head, image, others, body, div, info, i = 0;
        div = document.createElement('div');if(!child) {div.classList.add('popup_step', 'd-none', 'popup_step__'+field.type.replaceAll(' ', '-'));}
        head = document.createElement('h2');head.innerHTML=PROMPTS.str_replace(field?.heading??'');
        div.appendChild(head);
        if((field?.subtitle??'')!='') {
            info = document.createElement('p');
            info.innerHTML=PROMPTS.str_replace(field?.subtitle??'');
            div.appendChild(info);
        }
        
        input = level = false;
        fieldset = document.createElement('fieldset');
        level = document.createElement('label');
        level.innerHTML = PROMPTS.str_replace(field?.label??'');
        level.setAttribute('for',`field_${field?.fieldID??i}`);
        
        switch (field.type) {
            case 'textarea':
                input = document.createElement('textarea');input.classList.add('form-control');
                input.name = 'field.'+field.fieldID;
                input.placeholder = PROMPTS.str_replace(field?.placeholder??'');
                input.id = `field_${field?.fieldID??i}`;input.innerHTML = field?.value??'';
                // if(field?.dataset??false) {input.dataset = field.dataset;}
                input.dataset.fieldId = field.fieldID;
                break;
            case 'input':case 'text':case 'number':case 'date':case 'time':case 'local':case 'color':case 'range':
                input = document.createElement('input');input.classList.add('form-control');
                input.name = 'field.'+field.fieldID;
                input.placeholder = PROMPTS.str_replace(field?.placeholder??'');
                input.id = `field_${field?.fieldID??i}`;input.type = (field.type=='input')?'text':field.type;
                // if(field?.dataset??false) {input.dataset = field.dataset;}
                input.value = field?.value??'';input.dataset.fieldId = field.fieldID;
                if(level) {fieldset.appendChild( level );}
                if(input) {fieldset.appendChild( input );}
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
                if(level) {fieldset.appendChild( level );}
                if(input) {fieldset.appendChild( input );}
                if(input || level) {div.appendChild(fieldset);}
                break;
            case 'doll':case 'radio':case 'checkbox':
                input = document.createElement('div');input.classList.add('form-wrap');
                field.options = (field.options)?field.options:[];
                field.type = (field.type == 'doll')?'radio':field.type;
                // field.options = field.options.reverse();
                Object.values(field.options).forEach((opt, optI)=> {
                    if(opt && opt.label) {
                        level = document.createElement('label');level.classList.add('form-control-label', 'form-control-'+field.type);
                        // level.setAttribute('for', `field_${field?.fieldID??i}_${optI}`);
                        if(opt.input) {level.classList.add('form-flexs');}
                        span = document.createElement('span');
                        if(opt.imageUrl) {
                            image = document.createElement('img');image.src = opt.imageUrl;
                            image.alt = opt.label;level.appendChild(image);
                            level.classList.add('form-control-'+field.type+'__image');
                            input.classList.add('form-wrap__image');
                            if((opt?.thumbUrl??false) && opt.thumbUrl != '') {
                                image.src = opt.thumbUrl;image.dataset.outfit = opt.imageUrl;
                            }
                        }
                        if(!opt.input) {
                            span.innerHTML = opt.label+(
                                (opt?.cost??false)?(
                                ' <strong>'+(thisClass.config?.currencySign??'$')+''+ parseFloat(opt.cost).toFixed(2)+'</strong>'
                                ):''
                            );
                        } else {
                            others = document.createElement('input');others.type='text';
                            others.name='field.'+field.fieldID+'.others';others.placeholder=opt.label;
                            others.dataset.fieldId = field.fieldID;others.dataset.index = optI;
                            span.appendChild(others);
                        }
                        option = document.createElement('input');option.value=opt.label;
                        option.name='field.'+field.fieldID+'.option'+((field.type == 'checkbox')?'.' + optI:'');
                        option.dataset.index = optI;option.dataset.fieldId = field.fieldID;
                        option.id=`field_${field?.fieldID??i}_${optI}`;option.type=field.type;
                        if(field?.layer??false) {option.dataset.layer=field.layer;}
                        if((opt?.cost??'') == '') {opt.cost = '0';}option.dataset.cost=opt.cost;
                        if(child) {option.dataset.preview=child;}
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
                    fields.appendChild(PROMPTS.do_field(group, true));
                });
                fieldset.appendChild(fields);div.appendChild(fieldset);
                break;
            case 'info':
                fields = document.createElement('div');fields.classList.add('form-fields', 'form-fields__group', 'form-fields__group__'+(field.type).replaceAll(' ', ''));
                // field.groups = field.groups.reverse();
                var inputsArgs = {}, inputs = {
                    teddy_name: {
                        type: 'text',
                        // label: PROMPTS.i18n?.teddyname??'DubiDo\'s Name',
                        placeholder: PROMPTS.i18n?.teddyfullname??'Teddy full Name',
                        dataset: {title: PROMPTS.i18n?.teddyfullname??'Teddy full Name'}
                    },
                    teddy_birth: {
                        type: 'date', // default: new Date().toLocaleDateString('en-US'),
                        // label: PROMPTS.i18n?.teddybirth??'DubiDo\'s Birthday',
                        placeholder: PROMPTS.i18n?.teddybirth??'Birth date',
                        dataset: {title: PROMPTS.i18n?.teddybirth??'Birth date'}
                    },
                    teddy_sender: {
                        type: 'text',
                        // label: PROMPTS.i18n?.sendersname??'Sender\'s Name',
                        placeholder: PROMPTS.i18n?.sendersname??'Sender\'s Name',
                        dataset: {title: PROMPTS.i18n?.sendersname??'Sender\'s Name'}
                    },
                    teddy_reciever: {
                        type: 'text',
                        // label: PROMPTS.i18n?.recieversname??'Reciever\'s Name',
                        placeholder: PROMPTS.i18n?.recieversname??'Reciever\'s Name',
                        dataset: {title: PROMPTS.i18n?.recieversname??'Reciever\'s Name'}
                    }
                };
                Object.keys(inputs).forEach((type, typeI)=>{
                    inputsArgs = {
                        fieldID: (field?.fieldID??0)+'.'+(type?.fieldID??typeI),
                        ...inputs[type]
                    };
                    if(field[type] == 'on') {fields.appendChild(PROMPTS.do_field(inputsArgs, true));}
                });
                fieldset.appendChild(fields);div.appendChild(fieldset);
                break;
            default:
                // console.log('Failed implimenting '+field.type);
                input = level = false;
                break;
        }
        i++;
        if((field?.extra_fields??false)) {
            field.extra_fields.forEach((extra)=>{
                div.appendChild(PROMPTS.do_field(extra, true));
            });
        }
        return div;
    },
    do_submit: async (thisClass, el) => {
        var data = thisClass.generate_formdata(el);
        var args = thisClass.lastReqs = {
            best_of: 1,frequency_penalty: 0.01,presence_penalty: 0.01,top_p: 1,
            max_tokens: parseInt( data?.max_tokens??700 ),temperature: 0.7,model: data?.model??"text-davinci-003",
        };
        try {
            args.prompt = thisClass.str_replace(
                Object.keys(data).map((key)=>'{{'+key+'}}'),
                Object.values(data),
                thisClass.popup.thefield?.syntex??''
            );
            PROMPTS.lastJson = await thisClass.openai.createCompletion( args );
            var prompt = thisClass.popup.generate_results( thisClass );
            document.querySelector('#the_generated_result').value = prompt;
            // console.log( prompt );
        } catch (error) {
            thisClass.openai_error( error );
        }
    },
    do_pagination: async (plus, thisClass) => {
        var step, root, header, field, back, data, submit;PROMPTS.currentStep = PROMPTS?.currentStep??0;
        root = '.fwp-swal2-popup .popup_body .popup_step';
        if(!PROMPTS.lastJson.product || !PROMPTS.lastJson.product.custom_fields || PROMPTS.lastJson.product.custom_fields=='') {return;}
        if(await PROMPTS.beforeSwitch(thisClass, plus)) {
            PROMPTS.currentStep = (plus)?(
                (PROMPTS.currentStep < PROMPTS.totalSteps)?(PROMPTS.currentStep+1):PROMPTS.currentStep
            ):(
                (PROMPTS.currentStep > 0)?(PROMPTS.currentStep-1):PROMPTS.currentStep
            );
            if(PROMPTS.currentStep<=0) {return;}
            submit = document.querySelector('.popup_foot .button[data-react="continue"]');
            if(submit && submit.classList) {
                if(PROMPTS.currentStep >= (PROMPTS.totalSteps-1)) {
                    submit.firstElementChild.innerHTML = PROMPTS.i18n?.checkout??'Checkout';
                } else {
                    submit.firstElementChild.innerHTML = PROMPTS.i18n?.continue??'Continue';
                }
            }
            
            field = PROMPTS.lastJson.product.custom_fields.find((row)=>row.orderAt==PROMPTS.currentStep);
            if(plus && field && field.type == 'confirm' && ! await PROMPTS.do_search(field, thisClass)) {
                return false;
            }

            if(PROMPTS.currentStep >= PROMPTS.totalSteps) {
                step = document.querySelector('.popup_step.step_visible');data = [];
                data = thisClass.transformObjectKeys(thisClass.generate_formdata(document.querySelector('.popup_body')));
                
                console.log('Submitting...');
                submit = document.querySelector('.popup_foot .button[data-react="continue"]');
                if(submit && submit.classList) {
                    submit.setAttribute('disabled', true);
                    PROMPTS.currentStep--;

                    // data.product = PROMPTS.lastJson.product.id;
                    var formdata = new FormData();
                    formdata.append('action', 'futurewordpress/project/ajax/cart/add');
                    formdata.append('_nonce', thisClass.ajaxNonce);
                    const generated = await PROMPTS.get_formdata(thisClass, formdata);
                    
                    formdata.append('charges', JSON.stringify(thisClass.popupCart.additionalPrices));
                    formdata.append('dataset', JSON.stringify(generated));
                    formdata.append('product_id', PROMPTS.lastJson.product.id);
                    formdata.append('quantity', 1);
                    thisClass.sendToServer(formdata);

                    setTimeout(() => {submit.removeAttribute('disabled');}, 100000);
                }
                // if(PROMPTS.validateField(step, data, thisClass)) {
                // } else {console.log('Didn\'t Submit');}
            } else {
                back = document.querySelector('.popup_foot .button[data-react="back"]');
                if(back && back.classList) {
                    if(!plus && PROMPTS.currentStep<=1) {back.classList.add('invisible');} else {back.classList.remove('invisible');}
                }
                
                field = PROMPTS.lastJson.product.custom_fields.find((row)=>row.orderAt==PROMPTS.currentStep);
                header = document.querySelector('.header_image');
                if(header) {
                    if(field && field.headerbgurl!='') {
                        jQuery(header).css('background-image', 'url('+field.headerbgurl+')');
                        // header.innerHTML = '';
                    }
                }
                document.querySelectorAll(root+'.step_visible').forEach((el)=>{el.classList.add('d-none');el.classList.remove('step_visible');});
                step = document.querySelector(root+'[data-step="'+(field?.fieldID??PROMPTS.currentStep)+'"]');
                if(step) {
                    if(!plus) {step.classList.add('popup2left');}
                    step.classList.remove('d-none');setTimeout(()=>{step.classList.add('step_visible');},300);
                    if(!plus) {setTimeout(()=>{step.classList.remove('popup2left');},1500);}
                }

                // Change swal step current one.
                var popup = document.querySelector('.dynamic_popup');
                var popupParent = (popup)?popup.parentElement:document.querySelector('.swal2-html-container');
                thisClass.frozenNode = document.createElement('div');
                thisClass.frozenNode.appendChild(popup);

                var find = PROMPTS.lastJson.product.custom_fields.find((row)=>row.orderAt == PROMPTS.currentStep);
                var found = PROMPTS.progressSteps.indexOf(find?.steptitle??false);
                thisClass.Swal.update({
                    currentProgressStep: ((found)?found:(PROMPTS.currentStep-1)),
                    progressStepsDistance: (PROMPTS.progressSteps.length<=5)?'2rem':(
                        (PROMPTS.progressSteps.length>=8)?'0rem':'1rem'
                    )
                });
                // thisClass.Swal.update({currentProgressStep: (PROMPTS.currentStep-1)});

                if(popupParent) {popupParent.innerHTML = '';popupParent.appendChild(thisClass.frozenNode.childNodes[0])}
                
            }
        } else {
            console.log('Proceed failed');
        }
    },
    beforeSwitch: async (thisClass, plus) => {
        var field, back, next, elem, last;last = elem = false;
        if(plus) {
            field = PROMPTS.lastJson.product.custom_fields.find((row)=>row.orderAt==PROMPTS.currentStep);
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
            var data = thisClass.generate_formdata( document.querySelector('.popup_body') );
            var step = document.querySelector('.popup_step.step_visible'), prev = [];
            if(!step) {return (PROMPTS.currentStep<=0);}
            if(!PROMPTS.validateField(step, data, thisClass)) {return false;}

            step.querySelectorAll('input, select').forEach((el,ei)=>{
                // el is the element input
                if(!prev.includes(el.name) && data[el.name] && data[el.name]==el.value) {
                    // item is the fieldset
                    var item = PROMPTS.lastJson.product.custom_fields.find((row, i)=>row.fieldID==el.dataset.fieldId);
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
                                next = PROMPTS.lastJson.product.custom_fields.find((row)=>row.fieldID==parseInt(opt.next));
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
            var current = PROMPTS.lastJson.product.custom_fields.find((row)=>row.orderAt==PROMPTS.currentStep);
            var returnStep = current?.returnStep??false;
            var next = PROMPTS.lastJson.product.custom_fields.find((row)=>row.orderAt==returnStep);
            if(returnStep && next) {
                PROMPTS.currentStep = (parseInt(returnStep)+1);
                current.returnStep=false;
                return true;
            }
        }
        
        return true;
        // return (!plus || PROMPTS.currentStep < PROMPTS.totalSteps);
        // setTimeout(()=>{return true;},100);
    },
    validateField: (step, data, thisClass) => {
        // data = thisClass.generate_formdata(document.querySelector('.popup_body'));
        var fieldValue, field;fieldValue = step.querySelector('input, select');
        fieldValue = (fieldValue)?fieldValue?.name??false:false;
        field = PROMPTS.lastJson.product.custom_fields.find((row)=>row.fieldID==step.dataset.step);
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
        return true;
    },
    get_formdata: async (thisClass, formdata = false) => {
        var form = thisClass.generate_formdata(document.querySelector('.popup_body'));
        Object.keys(form).forEach((name)=>{
            var elem = document.querySelector('[name="'+name+'"]');
            if(elem.value.trim().toLocaleLowerCase() == form[name].trim().toLocaleLowerCase()) {
                var split = name.split('.');split[1] = parseInt(split[1]);
                var field = PROMPTS.lastJson.product.custom_fields.find((row)=>row.fieldID==split[1]);
                var img = elem.previousElementSibling;
                var match = (field?.options??[]).find((row)=>row.label && row.label.toLocaleLowerCase()==form[name].toLocaleLowerCase());
                form[name] = {
                    title: field?.title??(field?.steptitle??(field?.subtitle??'')),
                    name: elem.name,
                    value: form[name],
                    price: match?.cost??(elem.dataset?.cost??0),
                    image: match?.imageUrl??(img?.src??(img.dataset?.outfit??'')),
                    // field: match
                };
            } else {
                var split = name.split('.');split[1] = parseInt(split[1]);
                var field = PROMPTS.lastJson.product.custom_fields.find((row)=>row.fieldID==split[1]);
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
        const hasVoice = PROMPTS.lastJson.product.custom_fields.find((row)=>(row.type=='voice'));
        if(hasVoice) {
            if((thisClass.voiceRecord.audioPreview?.src??'') != '') {
                const voiceName = await thisClass.voiceRecord.recordedFileName();
                PROMPTS.voices[voiceName] = await fetch(thisClass.voiceRecord.audioPreview.src).then(r => r.blob());
                if(formdata) {
                    formdata.append('voice', PROMPTS.voices[voiceName], voiceName);
                }
                form['field.'+(hasVoice?.orderAt??115)+'.'+(hasVoice?.fieldID??'115')] = {
                    title: PROMPTS.i18n?.voice??'Voice',
                    name: '',
                    value: voiceName,
                    // hasVoice?.steptitle??(hasVoice?.heading??'Voice'),
                    image: '',
                    cost: parseFloat(thisClass.voiceRecord.recordButton.dataset?.cost??'0'),
                    voice: voiceName
                };
                
            }
        }
        form = thisClass.transformObjectKeys(form);
        // PROMPTS.lastJson.product.custom_fields.map((row)=>(row.type=='voice')?row:false);

        return form;
    }
    
};
export default PROMPTS;