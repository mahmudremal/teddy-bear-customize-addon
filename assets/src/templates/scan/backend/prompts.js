const PROMPTS = {
    i18n: {},
    get_template: (thisClass) => {
        var json, html;
        html = document.createElement('div');html.classList.add('dynamic_popup');
        if(PROMPTS.lastJson) {
            html.appendChild(PROMPTS.generate_template(thisClass));
        } else {
            html.innerHTML = `<div class="spinner-material"></div><h3>${PROMPTS.i18n?.pls_wait??'Please wait...'}</h3>`;
        }
        return html;
    },
    init_prompts: (thisClass) => {
        PROMPTS.core = thisClass;var card;
    },
    init_events: (thisClass) => {
        var template, fields, data, json, form, field, wrap, node, div, button, label, input, h2, formGroup;
        document.querySelectorAll('.popup_foot .button[data-react]').forEach((el) => {
            el.addEventListener('click', (event) => {
                event.preventDefault();
                switch (el.dataset.react) {
                    case 'back':
                        PROMPTS.do_pagination(false);
                        break;
                    default:
                        PROMPTS.do_pagination(true);
                        break;
                }
            });
        });
        document.querySelectorAll('.firststep-promptsdev').forEach((el) => {
            el.addEventListener('click', (event) => {
                event.preventDefault();el.remove();
                PROMPTS.do_fetch(thisClass);
            });
        });
    },
    generate_template: (thisClass) => {
        var json, get_fields, fields, html, data, field, formGroup;
        json = PROMPTS.lastJson;html = '';
        // html = PROMPTS.generate_fields(thisClass);
        get_fields = PROMPTS.get_fields(thisClass);
        formGroup = html = document.createElement('div');
        fields = (PROMPTS.lastJson?.product??false)?PROMPTS.lastJson.product:[];
        fields.forEach((data)=>{
            field = PROMPTS.do_field(PROMPTS.doto_field(data?.type??(data?.fieldtype??'text')), data);
            // formGroup = document.querySelector('.element-type-select > .form-wrap > .form-group');
            // if(formGroup) {formGroup.parentElement.insertBefore(field, formGroup);}
            formGroup.appendChild(field);
        });
        setTimeout(()=>{PROMPTS.init_intervalevent(thisClass);},300);
        if(fields.length>=1) {
            html = document.createElement('div');html.classList.add('element-type-select');
            formGroup.classList.add('form-wrap');html.appendChild(formGroup);
            return html;
        } else {
            html = document.createElement('div');
            html.classList.add('firststep-promptsdev');
            html.innerHTML = `<div>${PROMPTS.i18n?.addnewfield??'Add new field'}</div>`;
            return html;
        }
    },
    generate_fields: (thisClass) => {
        var div, node, step, foot, btn, back, fields = PROMPTS.get_fields(thisClass);
        div = document.createElement('div');node = document.createElement('form');
        node.action=thisClass.ajaxUrl;node.type='post';node.classList.add('popup_body');
        fields.forEach((field, i) => {
            field.required = field?.required??true;
            step = PROMPTS.do_field(field, {});
            step.dataset.step = i;
            node.appendChild(step);
            PROMPTS.totalSteps=i;
        });
        foot = document.createElement('div');foot.classList.add('popup_foot');
        btn = document.createElement('button');btn.classList.add('btn', 'btn-primary', 'button');
        btn.type='button';btn.innerHTML=PROMPTS.i18n?.continue??'Continue';btn.dataset.react='continue';
        back = document.createElement('button');back.classList.add('btn', 'btn-default', 'button');
        back.type='button';back.innerHTML=PROMPTS.i18n?.back??'Back';back.dataset.react = 'back';
        foot.appendChild(back);foot.appendChild(btn);div.appendChild(node);div.appendChild(foot);
        return div.innerHTML;
    },
    do_fetch: (thisClass) => {
        var template, fields, data, form, field, wrap, node, div, button, label, input, h2, formGroup, json;
        json = PROMPTS.get_fields();fields = json.types;
        template = document.querySelector('.dynamic_popup');
        if(!template) {console.log('"template" not found');return;}
        form = document.createElement('div');form.classList.add('element-type-select');
        if(document.querySelector('.element-type-select')) {
            form = document.querySelector('.element-type-select');
        }
        // form.name = 'add-new-element-type-select';form.action = '#';form.method = 'post';
        wrap = document.createElement('div');wrap.classList.add('form-wrap');
        if(template.querySelector('.form-wrap')) {wrap = template.querySelector('.form-wrap');}
        node = document.createElement('form');node.classList.add('form-group', 'add-new-field');
        node.action = '#';node.method = 'post';node.name = 'add-new-element-type-select';
        node.style.display = 'none';
        h2 = document.createElement('h4');h2.classList.add('h4');
        h2.innerHTML = PROMPTS.i18n?.selectatype??'Select a type';
        node.appendChild(h2);
        fields.forEach((field, i)=>{
            div = document.createElement('div');div.classList.add('inputGroup');
            input = document.createElement('input');input.name ='fieldtype';
            input.type = 'radio';input.value = field;input.id = 'eltype-field-'+i;
            label = document.createElement('label');label.classList.add('option');
            label.setAttribute('for', input.id);label.innerHTML = field.toUpperCase();
            div.appendChild(input);div.appendChild(label);node.appendChild(div);
        });
        wrap.appendChild(node);form.appendChild(wrap);
        button = document.createElement('button');button.type='button';
        button.classList.add('btn', 'button', 'save-this-popup');
        button.innerHTML = `<span>${PROMPTS.i18n?.update??'Update'}</span><div class="spinner-material"></div>`;
        form.appendChild(button);
        button = document.createElement('button');button.type='button';
        button.classList.add('btn', 'button', 'add-new-types');
        button.innerHTML = PROMPTS.i18n?.proceed??'Add new field';
        button.style.display = 'inline-block';
        form.appendChild(button);
        button = document.createElement('button');button.type='button';
        button.classList.add('btn', 'button', 'procced_types');
        button.innerHTML = PROMPTS.i18n?.proceed??'Proceed';
        button.style.display = 'none';
        form.appendChild(button);
        // template.innerHTML='';
        template.appendChild(form);
        setTimeout(() => {
            button = document.querySelector('.procced_types');
            if(button) {
                button.addEventListener('click', (event)=>{
                    jQuery('.add-new-field, .procced_types').slideUp();
                    jQuery('.add-new-types').slideDown();
                    data = thisClass.transformObjectKeys(Object.fromEntries(new FormData(document.querySelector('form[name="'+node.name+'"]'))));
                    // thisClass.toastify({text: "Procced clicked",className: "info",style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
                    field = PROMPTS.do_field(PROMPTS.doto_field(data?.fieldtype??'text'), {});
                    formGroup = document.querySelector('.element-type-select > .form-wrap > .form-group');
                    if(formGroup) {formGroup.parentElement.insertBefore(field, formGroup);}
                    setTimeout(()=>{PROMPTS.init_intervalevent(thisClass);},300);
                });
            }
            button = document.querySelector('.add-new-types');
            if(button) {
                button.addEventListener('click', (event)=>{
                    jQuery('.add-new-field, .procced_types').slideDown();
                    jQuery('.add-new-types').slideUp();
                    document.querySelectorAll('.popup_step__body:not([style*="display: none"])').forEach((el)=>{jQuery(el).slideUp();});
                });
            }
            button = document.querySelector('.element-type-select > .form-wrap');
            if(button) {
                PROMPTS.sortable = new thisClass.Sortable(button, {
                    animation: 150,
                    dragoverBubble: false,
                    handle: '.popup_step__header',
                    easing: "cubic-bezier(1, 0, 0, 1)"
                });
            }
            button = document.querySelector('.save-this-popup');
            if(button) {
                button.addEventListener('click', (event)=>{
                    event.preventDefault();
                    button.setAttribute('disabled', true);
                    form = document.querySelector('[name="add-new-element-type-select"]');
                    if(!PROMPTS.do_order(form)) {return;}
                    data = [];
                    document.querySelectorAll('.element-type-select .form-wrap .popup_step').forEach((form)=>{
                        data.push(
                            thisClass.transformObjectKeys(Object.fromEntries(new FormData(form)))
                        );
                    });
                    data = data.map((row)=>{
                        row.fieldID = parseInt(row.fieldID);
                        if((row?.options??false)) {
                            row.options = Object.values(row.options);
                            row.options = row.options.map((opt)=>{
                                opt.next = (opt.next!='')?parseInt(opt.next):false;
                                return opt;
                            });
                        }
                        return row;
                    });
                    var formdata = new FormData();
                    formdata.append('action', 'futurewordpress/project/ajax/save/product');
                    formdata.append('product_id', thisClass.config?.product_id??'');
                    formdata.append('dataset', JSON.stringify(data));
                    formdata.append('_nonce', thisClass.ajaxNonce);
                    thisClass.sendToServer(formdata);
                    setTimeout(() => {button.removeAttribute('disabled');}, 20000);
                });
            }
            // Close all card after generating
            jQuery('.popup_step .popup_step__body').slideUp();
        }, 300);
    },
    do_field: (field, data) => {
        var header, fields, form, fieldset, input, label, level, hidden, span, option, head, others, body, div, remove, img, preview, cross, node;PROMPTS.currentFieldID++;
        div = document.createElement('form');div.classList.add('popup_step');header = true;
        div.action = '';div.method = 'post';div.id = 'popupstepform_'+PROMPTS.currentFieldID;
        // head = document.createElement('h2');head.innerHTML=field;div.appendChild(head);
        if(header) {
            head = document.createElement('div');head.classList.add('popup_step__header');
            input = document.createElement('input');input.type='number';input.name = 'fieldID';
            input.setAttribute('value', data?.fieldID??PROMPTS.currentFieldID);input.classList.add('field_id');
            span = document.createElement('span');span.classList.add('popup_step__header__text');
            span.innerHTML = (data?.type??field.type).toUpperCase();span.dataset.type = data?.type??field.type;
            head.appendChild(span);head.appendChild(input);
            remove = document.createElement('span');remove.title = 'Remove';
            remove.classList.add('popup_step__header__remove', 'dashicons-before', 'dashicons-trash');
            input = document.createElement('input');input.type='hidden';input.name = 'type';input.setAttribute('value', data?.type??field.type);
            head.appendChild(input);head.appendChild(remove);div.appendChild(head);
            body = div;div = document.createElement('div');div.classList.add('popup_step__body');
        }
        if(field.steptitle) {
            PROMPTS.lastfieldID++;
            fieldset = document.createElement('div');fieldset.classList.add('form-group');
            input = document.createElement('input');input.classList.add('form-control');
            input.name = 'steptitle';input.type = 'text';input.maxlength = 10;
            input.id = 'thefield'+PROMPTS.lastfieldID;input.setAttribute('value', data?.steptitle??'');
            input.placeholder=PROMPTS.i18n?.popup_subheading_text??'PopUp Step text';
            label = document.createElement('label');label.classList.add('form-label');
            label.setAttribute('for', input.id);
            label.innerHTML = PROMPTS.i18n?.popup_subheading??'PopUp Step text not more then 10 characters.';
            
            fieldset.appendChild(label);fieldset.appendChild(input);div.appendChild(fieldset);
        }
        if(field.headerbg) {
            PROMPTS.lastfieldID++;
            fieldset = document.createElement('div');fieldset.classList.add('form-group');
            input = document.createElement('button');input.classList.add('btn', 'button', 'imglibrary');
            
            input.type='button';input.innerHTML = PROMPTS.i18n?.select_image??'Select image';
            input.innerHTML = ((data?.headerbgurl??'')=='')?input.innerHTML:input.innerHTML+' ('+(data?.headerbgurl??'').split(/[\\/]/).pop()+')';
            input.placeholder=PROMPTS.i18n?.popup_subheading_text??'PopUp Sub-heading text';
            input.name = 'headerbgurl';input.setAttribute('value', data?.headerbgurl??'');
            input.dataset.innertext = PROMPTS.i18n?.select_image??'Select image';
            label = document.createElement('p');label.classList.add('text-muted');
            label.setAttribute('for', 'thefield'+PROMPTS.lastfieldID);input.id = 'thefield'+PROMPTS.lastfieldID;
            label.innerHTML = PROMPTS.i18n?.select_image_desc??'Select an image for popup header. It should be less weight, vertical and optimized.';
            hidden = document.createElement('input');hidden.type='hidden';hidden.name ='headerbg';hidden.setAttribute('value', data?.headerbg??'');
            
            fieldset.appendChild(label);fieldset.appendChild(input);fieldset.appendChild(hidden);fieldset.appendChild(PROMPTS.imagePreview((data?.headerbgurl??''), thisClass));
            div.appendChild(fieldset);
        }
        if(field.heading) {
            PROMPTS.lastfieldID++;
            fieldset = document.createElement('div');fieldset.classList.add('form-group');
            input = document.createElement('input');input.type='text';
            input.id = 'thefield'+PROMPTS.lastfieldID;input.classList.add('form-control');
            input.name = 'heading';input.setAttribute('value', data?.heading??'');
            input.placeholder=PROMPTS.i18n?.popup_heading_text??'PopUp Heading text';
            label = document.createElement('label');label.classList.add('form-label');
            label.setAttribute('for', 'thefield'+PROMPTS.lastfieldID);
            label.innerHTML = PROMPTS.i18n?.popup_heading??'PopUp Heading';
            
            fieldset.appendChild(label);fieldset.appendChild(input);div.appendChild(fieldset);
        }
        if(field.subtitle) {
            PROMPTS.lastfieldID++;
            fieldset = document.createElement('div');fieldset.classList.add('form-group');
            input = document.createElement('input');input.classList.add('form-control');
            input.name = 'subtitle';input.type = 'text';
            input.id = 'thefield'+PROMPTS.lastfieldID;input.setAttribute('value', data?.subtitle??'');
            input.placeholder=PROMPTS.i18n?.popup_subheading_text??'PopUp Sub-heading text';
            label = document.createElement('label');label.classList.add('form-label');
            label.setAttribute('for', input.id);
            label.innerHTML = PROMPTS.i18n?.popup_subheading??'PopUp Sub Heading';
            
            fieldset.appendChild(label);fieldset.appendChild(input);div.appendChild(fieldset);
        }
        PROMPTS.lastfieldID++; // field.required
        if(true) {
            PROMPTS.lastfieldID++;
            fieldset = document.createElement('div');fieldset.classList.add('form-group', 'checkbox-reverse');
            input = document.createElement('input');input.classList.add('form-control');
            input.name = 'required';input.type = 'checkbox';
            input.id = 'thefield'+PROMPTS.lastfieldID;input.setAttribute('value', data?.required??'1');
            input.placeholder=PROMPTS.i18n?.popup_subheading_text??'PopUp Sub-heading text';
            label = document.createElement('label');label.classList.add('form-label');
            label.setAttribute('for', input.id);
            label.innerHTML = PROMPTS.i18n?.required??'Required';
            
            fieldset.appendChild(label);fieldset.appendChild(input);div.appendChild(fieldset);
        }
        PROMPTS.lastfieldID++;
        switch (field.type) {
            case 'textarea':
                fieldset = document.createElement('div');fieldset.classList.add('form-group');
                input = document.createElement('textarea');input.classList.add('form-control', 'form-control-'+field.type);input.setAttribute('value', data?.placeholder??'');
                input.name = 'placeholder';input.placeholder = PROMPTS.i18n?.placeholder_text??'Placeholder text';input.id = 'thefield'+PROMPTS.lastfieldID;
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.innerHTML = PROMPTS.i18n?.placeholder_text??'Placeholder text';
                fieldset.appendChild(label);fieldset.appendChild(input);div.appendChild(fieldset);
                break;
            case 'input':case 'text':case 'number':case 'date':case 'time':case 'local':case 'color':case 'range':
                fieldset = document.createElement('div');fieldset.classList.add('form-group');
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type = data?.type??field.type;
                input.id = 'thefield'+PROMPTS.lastfieldID;
                input.setAttribute('value', data?.placeholder??'');
                input.name = 'placeholder';input.placeholder = PROMPTS.i18n?.placeholder_text??'Placeholder text';
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);
                label.innerHTML = PROMPTS.i18n?.placeholder_ordefault??'Placeholder / Default value';
                fieldset.appendChild(label);fieldset.appendChild(input);div.appendChild(fieldset);
                break;
            case 'outfit':
                fieldset = document.createElement('div');fieldset.classList.add('form-group');
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type='text';input.name = 'label';input.id='thefield'+PROMPTS.lastfieldID;input.setAttribute('value', data?.label??'');
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.innerHTML = PROMPTS.i18n?.input_label??'Input label';
                fieldset.appendChild(label);fieldset.appendChild(input);
                
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.id='thefield'+PROMPTS.lastfieldID;input.name = 'description';input.setAttribute('value', data?.description??'');
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.innerHTML = PROMPTS.i18n?.placeholder_text??'Field descriptions.';
                fieldset.appendChild(label);fieldset.appendChild(input);
                PROMPTS.lastfieldID++;
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type='text';input.name = 'placeholder';input.id='thefield'+PROMPTS.lastfieldID;input.setAttribute('value', data?.placeholder??'');
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.innerHTML = PROMPTS.i18n?.placeholder_text??'Placeholder text';
                fieldset.appendChild(label);fieldset.appendChild(input);
                /**
                 * Reapeter fields
                 */
                input = document.createElement('button');input.classList.add('btn', 'button', 'my-3', 'do_repeater_group');input.type='button';input.dataset.order=0;
                input.innerHTML = PROMPTS.i18n?.add_new_group??'Add new group';input.dataset.optionGroup=field.type;
                fieldset.appendChild(input);
                
                (data?.groups??[]).forEach(group => {
                    PROMPTS.do_group_repeater(fieldset.querySelector('.do_repeater_group'), group);
                });
                /**
                 * Reapeter fields
                 */
                
                div.appendChild(fieldset);
                break;
            case 'doll':case 'select':case 'radio':case 'checkbox':
                fieldset = document.createElement('div');fieldset.classList.add('form-group');
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type='text';input.name = 'label';input.id='thefield'+PROMPTS.lastfieldID;input.setAttribute('value', data?.label??'');
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.innerHTML = PROMPTS.i18n?.input_label??'Input label';
                fieldset.appendChild(label);fieldset.appendChild(input);
                
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.id='thefield'+PROMPTS.lastfieldID;input.name = 'description';input.setAttribute('value', data?.description??'');
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.innerHTML = PROMPTS.i18n?.placeholder_text??'Field descriptions.';
                fieldset.appendChild(label);fieldset.appendChild(input);
                PROMPTS.lastfieldID++;
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type='text';input.name = 'placeholder';input.id='thefield'+PROMPTS.lastfieldID;input.setAttribute('value', data?.placeholder??'');
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.innerHTML = PROMPTS.i18n?.placeholder_text??'Placeholder text';
                fieldset.appendChild(label);fieldset.appendChild(input);
                /**
                 * Reapeter fields
                 */
                input = document.createElement('button');input.classList.add('btn', 'button', 'my-3', 'do_repeater_field');input.type='button';input.dataset.order=0;
                input.innerHTML = PROMPTS.i18n?.add_new_option??'Add new option';input.dataset.optionGroup=field.type;
                fieldset.appendChild(input);
                
                (data?.options??[]).forEach(option => {
                    PROMPTS.do_repeater(fieldset.querySelector('.do_repeater_field'), option, false);
                });
                /**
                 * Reapeter fields
                 */
                
                div.appendChild(fieldset);
                break;
            case 'voice':
                fieldset = document.createElement('div');fieldset.classList.add('form-group');
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type = data?.type??field.type;
                input.id = 'thefield'+PROMPTS.lastfieldID;input.type = 'number';
                input.setAttribute('value', data?.cost??'');input.name = 'cost';
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);
                label.innerHTML = PROMPTS.i18n?.placeholder_ordefault??'Additional cost';
                fieldset.appendChild(label);fieldset.appendChild(input);div.appendChild(fieldset);
                break;
            case 'info':
                fieldset = document.createElement('div');fieldset.classList.add('form-group');

                // Teddy Name
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type = 'checkbox';
                input.id = 'thefield'+PROMPTS.lastfieldID;
                input.setAttribute('value', 'on');input.name = 'teddy_name';
                if((data?.teddy_name??'')=='on') {input.setAttribute('checked', true);}
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.appendChild(input);
                label.innerHTML += PROMPTS.i18n?.teddy_name??'Teddy name';
                fieldset.appendChild(label);PROMPTS.lastfieldID++;

                // Teddy Birth
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type = 'checkbox';
                input.id = 'thefield'+PROMPTS.lastfieldID;
                input.setAttribute('value', 'on');input.name = 'teddy_birth';
                if((data?.teddy_birth??'')=='on') {input.setAttribute('checked', true);}
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.appendChild(input);
                label.innerHTML += PROMPTS.i18n?.teddy_birth??'Teddy birth';
                fieldset.appendChild(label);PROMPTS.lastfieldID++;

                // Sender's Name
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type = 'checkbox';
                input.id = 'thefield'+PROMPTS.lastfieldID;
                input.setAttribute('value', 'on');input.name = 'teddy_sender';
                if((data?.teddy_sender??'')=='on') {input.setAttribute('checked', true);}
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.appendChild(input);
                label.innerHTML += PROMPTS.i18n?.teddy_sender??'Sender\'s Name';
                fieldset.appendChild(label);PROMPTS.lastfieldID++;

                // Reciever's Name
                input = document.createElement('input');input.classList.add('form-control', 'form-control-'+field.type);input.type = 'checkbox';
                input.id = 'thefield'+PROMPTS.lastfieldID;
                input.setAttribute('value', 'on');input.name = 'teddy_reciever';
                if((data?.teddy_reciever??'')=='on') {input.setAttribute('checked', true);}
                label = document.createElement('label');label.classList.add('form-label');
                label.setAttribute('for', input.id);label.appendChild(input);
                label.innerHTML += PROMPTS.i18n?.teddy_reciever??'Reciever\'s Name';
                fieldset.appendChild(label);
                
                node = document.createElement('div');node.classList.add('info-permission-wrap');
                node.appendChild(fieldset);div.appendChild(node);
                break;
            default:
                input = level = false;
                console.log('type', field.type);
                break;
        }
        if(header) {
            body.appendChild(div);div = body;
        }
        // console.log(data, div);
        return div;
    },
    doto_field: (type) => {
        var field;
        switch (type) {
            case 'select':case 'radio':case 'checkbox':
                field = {
                    type: type,
                    steptitle: true,
                    headerbg: true,
                    heading: true,
                    title: true,
                    subtitle: true,
                    label: '',
                    label_extra: '',
                    options: []
                };
                break;
            default:
                field = {
                    type: type,
                    steptitle: true,
                    headerbg: true,
                    heading: true,
                    title: true,
                    subtitle: true,
                    label: '',
                    label_extra: ''
                };
                break;
        }
        return field;
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
        } catch (error) {
            thisClass.openai_error( error );
        }
    },
    do_pagination: async (plus) => {
        var step, root;PROMPTS.currentStep = PROMPTS?.currentStep??0;
        root = '.fwp-swal2-popup .popup_body .popup_step';
        if(await PROMPTS.beforeSwitch(thisClass, plus)) {
            PROMPTS.currentStep = (plus)?(
                (PROMPTS.currentStep < PROMPTS.totalSteps)?(PROMPTS.currentStep+1):PROMPTS.currentStep
            ):(
                (PROMPTS.currentStep > 0)?(PROMPTS.currentStep-1):PROMPTS.currentStep
            );
            document.querySelectorAll(root+'.step_visible').forEach((el)=>{el.classList.add('d-none');el.classList.remove('step_visible');});
            step = document.querySelector(root+'[data-step="'+PROMPTS.currentStep+'"]');
            if(step) {
                if(!plus) {step.classList.add('popup2left');}
                step.classList.remove('d-none');setTimeout(()=>{step.classList.add('step_visible');},300);
                if(!plus) {setTimeout(()=>{step.classList.remove('popup2left');},1500);}
            }
        } else {
            console.log('Proceed failed');
        }
    },
    beforeSwitch: (thisClass, plus) => {
        var back;
        back = document.querySelector('.popup_foot .button[data-react="back"]');
        if(back && back.classList) {
            if(!plus && PROMPTS.currentStep<=1) {back.classList.add('invisible');} else {back.classList.remove('invisible');}
        }
        if(plus && PROMPTS.totalSteps!=0 && PROMPTS.totalSteps<=PROMPTS.currentStep) {
            // Submitting popup!
            return (PROMPTS.totalSteps != PROMPTS.currentStep);
        }
        if(plus) {
            var data = thisClass.generate_formdata( document.querySelector('.popup_body') );
            var step = document.querySelector('.popup_step.step_visible'), prev = [];
            if(!step) {return (PROMPTS.currentStep<=0);}
            step.querySelectorAll('input, select').forEach((el,ei)=>{
                // el is the element input
                if(!prev.includes(el.name) && data[el.name] && data[el.name]==el.value) {
                    // item is the fieldset
                    var item = PROMPTS.lastJson.product.custom_fields.find((row, i)=>row.slug==el.name);
                    if(item) {
                        // opt is the options
                        var opt = (item?.options??[]).find((opt)=>opt.name==data[el.name]);
                        if(opt) {
                            prev.push(el.name);
                            // if(item.required) {return false;}
                            if(!item.is_conditional && opt.forward && opt.forward!='') {
                                var forward2 = PROMPTS.lastJson.product.custom_fields.find((row, i)=>row.slug==opt.forward);
                                if(forward2) {
                                    forward2.returnStep = item.orderAt;
                                    PROMPTS.currentStep = (forward2.orderAt-1);
                                    return true;
                                }
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
            var returnStep = PROMPTS.lastJson.product.custom_fields[PROMPTS.currentStep]?.returnStep??'';
            var forwardd2 = PROMPTS.lastJson.product.custom_fields[returnStep];
            if(forwardd2) {
                PROMPTS.currentStep = (parseInt(returnStep)+1);
                PROMPTS.lastJson.product.custom_fields[PROMPTS.currentStep].returnStep='';
                return true;
            }
        }
        return true;
        // setTimeout(()=>{return true;},100);
    },
    get_fields: () => {
        var fields;
        fields = {
            types: ['text', 'number', 'date', 'time', 'local', 'color', 'range', 'textarea', 'select', 'radio', 'checkbox', 'doll', 'voice', 'outfit', 'info'],
        };
        return fields;
    },
    init_intervalevent: (thisClass) => {
        document.querySelectorAll('.imglibrary:not([data-handled])').forEach((el, ei)=>{
            el.dataset.handled = true;
            el.addEventListener('click', (event) => {
                event.preventDefault();
                if(typeof wp.media!=='undefined') {
                    var mediaUploader = wp.media({
                        title: 'Select or Upload Media',
                        button: {text: 'Use this Media'},
                        multiple: false
                    });
                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        var url = attachment.url;el.value = url; // attachment.filename;
                        el.innerHTML = el.dataset.innertext+' ('+attachment.filename+')';
                        var hidden = el.nextElementSibling;
                        if(hidden) {hidden.value = attachment.id;}

                        var img, node, cross;
                        if((el.nextElementSibling?.nextElementSibling??false) && el.nextElementSibling.nextElementSibling.classList.contains('imgpreview')) {
                            el.nextElementSibling.nextElementSibling.remove();
                        }
                        cross = document.createElement('div');cross.classList.add('dashicons-before', 'dashicons-dismiss');
                        cross.title = PROMPTS.i18n?.remove??'Remove';
                        cross.addEventListener('click', (event) => {
                            event.preventDefault();cross.parentElement.remove();el.value = '';
                            el.nextElementSibling.value = '';el.innerHTML = el.dataset.innertext;
                        });
                        node = document.createElement('div');node.classList.add('imgpreview');
                        img = document.createElement('img');img.src = url;img.alt = attachment.id;
                        node.appendChild(img);node.appendChild(cross);
                        // el.parentElement.appendChild(node);
                        PROMPTS.insertAfter(el.nextElementSibling, node);
                        // jQuery('#img_url').val(url);
                        // jQuery('.upload_img').html('<img src="'+url+'">');
                    });
                    mediaUploader.open();
                } else {
                    thisClass.toastify({text: "WordPress media library not initialized.",className: "info",style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
                }
            });
        });
        document.querySelectorAll('.popup_step__header:not([data-handled])').forEach((el, ei)=>{
            el.dataset.handled = true;
            el.addEventListener('click', (event) => {
                event.preventDefault();
                if(el.nextElementSibling) {
                    switch(el.dataset.status) {
                        case 'shown':
                            el.dataset.status = 'hidden';
                            jQuery(el.nextElementSibling).slideUp();
                            break;
                        default:
                            el.dataset.status = 'shown';
                            jQuery(el.nextElementSibling).slideDown();
                            break;
                    }
                }
            });
        });
        document.querySelectorAll('.popup_step__header__remove:not([data-handled])').forEach((el, ei)=>{
            el.dataset.handled = true;
            el.addEventListener('click', (event) => {
                event.preventDefault();
                if(confirm(PROMPTS.i18n?.areusure??'Are you sure?')) {
                    if(el.parentElement&&el.parentElement.parentElement) {
                        el.parentElement.parentElement.remove();
                    } else {
                        thisClass.toastify({text: "Operation falied!",className: "error",style: {background: "linear-gradient(to right, #ffb8b8, #ff7575)"}}).showToast();
                    }
                }
            });
        });
        document.querySelectorAll('.do_repeater_field:not([data-handled])').forEach((el, ei)=>{
            el.dataset.handled = true;
            el.addEventListener('click', (event) => {
                event.preventDefault();
                PROMPTS.do_repeater(el, {}, (el.dataset?.isGroup??false));
            });
        });
        document.querySelectorAll('.do_repeater_group:not([data-handled])').forEach((el, ei)=>{
            el.dataset.handled = true;
            el.addEventListener('click', (event) => {
                event.preventDefault();
                PROMPTS.do_group_repeater(el, []);
            });
        });
        document.querySelectorAll('.single-repeater-option .input-group-append:not([data-handled])').forEach((trash)=>{
            trash.dataset.handled = true;
            trash.addEventListener('click', (event)=>{
                if(trash.parentElement && confirm(PROMPTS.i18n?.rusure??'Are you sure?')) {
                    jQuery(trash.parentElement.parentElement).slideUp();
                    setTimeout(() => {trash.parentElement.parentElement.remove();}, 1500);
                }
            });
        });
        document.querySelectorAll('.single-repeater-option .input-group-prepend:not([data-handled])').forEach((condition)=>{
            condition.dataset.handled = true;
            condition.addEventListener('click', (event)=>{
                if(condition.parentElement && condition.parentElement.parentElement) {
                    condition.parentElement.parentElement.classList.toggle('show-configs');
                }
                // input = condition.parentElement.nextElementSibling;
                // input.setAttribute('type', (input.getAttribute('type')=='number')?'hidden':'number');
                // if(input) {input.type = 'number';}
            });
        });
        document.querySelectorAll('.imgpreview .dashicons-dismiss:not([data-handled])').forEach((cross)=>{
            cross.dataset.handled = true;
            cross.addEventListener('click', (event) => {
                event.preventDefault();
                var hidden = cross.parentElement.previousElementSibling;
                cross.parentElement.remove();hidden.value = '';
                var input = hidden.previousElementSibling;
                if(input.nodeName == 'INPUT') {input.value = '';}
                else {input.removeAttribute('value');}
                input.innerHTML = input.dataset.innertext;
            });
        });
    },
    insertAfter: (referenceNode, newNode) => {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    },
    do_order: (form) => {
        var obj={}, sort;
        form.querySelectorAll('[name*="[]"], [data-input-name*="[]"]').forEach((el,ei)=>{
            if(!el.dataset.inputName) {el.dataset.inputName=el.name;}
            sort = el.dataset.inputName.replaceAll('[]','');
            if(!obj[sort]) {obj[sort]=[];}
            obj[sort].push(true);
            el.name = el.dataset.inputName.replaceAll('[]','['+(obj[sort].length-1)+']');
        });
        return true;
    },
    do_repeater: (el, row, groupAt) => {
        var wrap, config, group, input, hidden, marker, remover, order, prepend, append, text, image, preview;
        if(!el.dataset.order) {el.dataset.order=0;}
        order = parseInt(el.dataset.order);
        wrap = document.createElement('div');wrap.classList.add('single-repeater-option');
        group = document.createElement('div');group.classList.add('input-group', 'mb-2', 'mr-sm-2');
        prepend = document.createElement('div');prepend.classList.add('input-group-prepend');
        text = document.createElement('div');text.classList.add('input-group-text');
        marker = document.createElement('span');marker.classList.add('dashicons-before', 'dashicons-edit');marker.title = 'Condition';text.appendChild(marker);
        prepend.appendChild(text);group.appendChild(prepend);

        input = document.createElement('input');input.classList.add('form-control');
        input.placeholder = 'Input the Label here.';input.name = ((groupAt !== false)?'groups.'+groupAt+'.options.':'options.')+order+'.label';
        input.setAttribute('value', row?.label??'');input.type = 'text';
        group.appendChild(input);

        append = document.createElement('div');append.classList.add('input-group-append');
        text = document.createElement('div');text.classList.add('input-group-text');
        remover = document.createElement('span');remover.classList.add('dashicons-before', 'dashicons-trash');remover.title = 'Remove';text.appendChild(remover);
        append.appendChild(text);group.appendChild(append);

        config = document.createElement('div');config.classList.add('form-controls-config');
        
        hidden = document.createElement('input');hidden.classList.add('form-control', 'w-half');
        hidden.placeholder = 'Next step ID';hidden.name = ((groupAt !== false)?'groups.'+groupAt+'.options.':'options.')+order+'.next';
        hidden.setAttribute('value', row?.next??'');hidden.type = 'number';
        config.appendChild(hidden);

        hidden = document.createElement('input');hidden.classList.add('form-control', 'w-half');
        hidden.placeholder = 'Additional cost';hidden.name = ((groupAt !== false)?'groups.'+groupAt+'.options.':'options.')+order+'.cost';
        hidden.setAttribute('value', row?.cost??'');hidden.type = 'number';
        config.appendChild(hidden);

        image = document.createElement('button');image.classList.add('form-control', 'w-half', 'imglibrary');
        image.placeholder = 'Image';image.name = ((groupAt !== false)?'groups.'+groupAt+'.options.':'options.')+order+'.imageUrl';
        image.setAttribute('value', row?.imageUrl??'Select Image');image.type = 'button';
        image.dataset.innertext = image.innerHTML = PROMPTS.i18n?.select_image??'Select image';
        image.innerHTML = ((row?.imageUrl??'')=='')?image.innerHTML:image.innerHTML+' ('+(row?.imageUrl??'').split(/[\\/]/).pop()+')';
        config.appendChild(image);
        
        hidden = document.createElement('input');hidden.classList.add('form-control');
        hidden.name = ((groupAt !== false)?'groups.'+groupAt+'.options.':'options.')+order+'.image';hidden.type = 'hidden';
        hidden.setAttribute('value', row?.image??'');config.appendChild(hidden);

        // Preview
        config.appendChild(PROMPTS.imagePreview((row?.imageUrl??''), thisClass));

        image = document.createElement('button');image.classList.add('form-control', 'w-half', 'imglibrary');
        image.placeholder = 'Thumbnail';image.name = ((groupAt !== false)?'groups.'+groupAt+'.options.':'options.')+order+'.thumbUrl';
        image.setAttribute('value', row?.thumbUrl??'Select a thumbnail');image.type = 'button';
        image.dataset.innertext = image.innerHTML = PROMPTS.i18n?.select_thumbnail??'Select thumbnail';
        image.innerHTML = ((row?.thumbUrl??'')=='')?image.innerHTML:image.innerHTML+' ('+(row?.thumbUrl??'').split(/[\\/]/).pop()+')';
        config.appendChild(image);
        
        hidden = document.createElement('input');hidden.classList.add('form-control');
        hidden.name = ((groupAt !== false)?'groups.'+groupAt+'.options.':'options.')+order+'.thumb';hidden.type = 'hidden';
        hidden.setAttribute('value', row?.thumb??'');config.appendChild(hidden);

        config.appendChild(PROMPTS.imagePreview((row?.thumbUrl??''), thisClass));
        // el.dataset.optionGroup;
        // label = document.createElement('label');label.classList.add('form-label');label.innerHTML = 'Label';
        
        // group.appendChild(label);
        el.dataset.order = (order+1);
        wrap.appendChild(group);wrap.appendChild(config);
        el.parentElement.insertBefore(wrap, el);
        setTimeout(() => {PROMPTS.init_intervalevent(window.thisClass);}, 300);
    },
    do_group_repeater: (el, group) => {
        var wrap, config, input, select, order, groupAt, option, label, image, trash, header, body;
        if(!el.dataset.order) {el.dataset.order=0;}
        groupAt = order = parseInt(el.dataset.order);
        wrap = document.createElement('div');
        wrap.classList.add('single-repeater-option', 'single-repeater-group');
        
        header = document.createElement('div');header.classList.add('single-repeater-header');
        body = document.createElement('div');body.classList.add('single-repeater-body');
        
        label = document.createElement('label');label.classList.add('form-label');
        label.innerHTML = PROMPTS.i18n?.field_type??'Field type';
        label.setAttribute('for', 'group-type-'+order);header.appendChild(label);

        trash = document.createElement('div');
        trash.classList.add('popup_step__header__remove', 'dashicons-before', 'dashicons-trash');
        header.appendChild(trash);

        select = document.createElement('select');select.classList.add('form-control');
        select.name='groups.'+groupAt+'.type';select.id = 'group-type-'+groupAt;
        ['Checkbox', 'Radio'].forEach((value) => {
            option = document.createElement('option');option.value = value.toLowerCase();
            if((group?.type??'') == option.value) {option.setAttribute('selected', 'selected');}
            option.innerHTML = value;select.appendChild(option);
        });
        body.appendChild(select);

        label = document.createElement('label');label.classList.add('form-label');
        label.innerHTML = PROMPTS.i18n?.layer_order??'Layer Order';
        label.setAttribute('for', 'group-layer-'+order);body.appendChild(label);
        input = document.createElement('input');input.classList.add('form-control');
        input.name='groups.'+groupAt+'.layer';input.id = 'group-layer-'+groupAt;
        input.type = 'number';body.appendChild(input);


        input = document.createElement('button');input.classList.add('btn', 'button', 'my-3', 'do_repeater_field');input.type='button';input.dataset.order=0;input.dataset.isGroup=groupAt;
        input.innerHTML = PROMPTS.i18n?.add_new_option??'Add new option';input.dataset.optionGroup=group?.type??'';
        body.appendChild(input);

        Object.values(group?.options??[]).forEach(option => {PROMPTS.do_repeater(input, option, groupAt);});

        wrap.appendChild(header);wrap.appendChild(body);

        // group.appendChild(label);
        el.dataset.order = (order+1);
        // wrap.appendChild(group);wrap.appendChild(config);
        el.parentElement.insertBefore(wrap, el);
        
        setTimeout(()=>{PROMPTS.init_intervalevent(window.thisClass);},300);
    
    },
    imagePreview: (src, thisClass) => {
        var cross, preview, image, name = '';
        preview = document.createElement('div');preview.classList.add('imgpreview');
        if(!src || src == '') {return preview;}
        name = src.split('/');name = name[(name.length-1)];
        
        cross = document.createElement('div');cross.classList.add('dashicons-before', 'dashicons-dismiss');
        cross.title = PROMPTS.i18n?.remove??'Remove';
        image = document.createElement('img');image.src = src;image.alt = name;
        preview.appendChild(image);preview.appendChild(cross);return preview;
    }
};
export default PROMPTS;