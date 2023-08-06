/**
 * Frontend Script.
 * 
 * @package TeddyBearCustomizeAddon
 */

import Swal from "sweetalert2";
// import Awesomplete from "awesomplete";
import PROMPTS from "./prompts";
import Toastify from 'toastify-js';
import voiceRecord from "./voicerecord";
import popupCart from "./popupcart";
import flatpickr from "flatpickr";


( function ( $ ) {
	class FutureWordPress_Frontend {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl??'';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce??'';
			this.lastAjax = false;this.profile = fwpSiteConfig?.profile??false;
			var i18n = fwpSiteConfig?.i18n??{};this.noToast = true;
			this.config = fwpSiteConfig;
			this.i18n = {
				confirming								: 'Confirming',
				...i18n
			}
			this.setup_hooks();
		}
		setup_hooks() {
			const thisClass = this;
			window.thisClass = this;
			this.prompts = PROMPTS;
			this.Swal = Swal;
			this.flatpickr = flatpickr;
			popupCart.priceSign = this.config?.currencySign??'$';
			this.popupCart = popupCart;
			this.init_toast();
			this.init_events();
			this.init_i18n();
			this.init_search_form();
			voiceRecord.i18n = this.i18n;
			PROMPTS.i18n = this.i18n;
			voiceRecord.init_recorder(this);
			this.voiceRecord = voiceRecord;

			// this.move_lang_switcher();

			this.popup_show_only4israel();
			this.move_cart_icon2header();
		}
		init_toast() {
			const thisClass = this;
			this.toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3500,
				timerProgressBar: true,
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer )
					toast.addEventListener('mouseleave', Swal.resumeTimer )
				}
			});
			this.notify = Swal.mixin({
				toast: true,
				position: 'bottom-start',
				showConfirmButton: false,
				timer: 6000,
				willOpen: (toast) => {
				  // Offset the toast message based on the admin menu size
				  var dir = 'rtl' === document.dir ? 'right' : 'left'
				  toast.parentElement.style[dir] = document.getElementById('adminmenu')?.offsetWidth + 'px'??'30px'
				}
			})
			this.toastify = Toastify; // https://github.com/apvarun/toastify-js/blob/master/README.md
			if( location.host.startsWith('futurewordpress') ) {
				document.addEventListener('keydown', function(event) {
					if (event.ctrlKey && (event.key === '/' || event.key === '?') ) {
						event.preventDefault();
						navigator.clipboard.readText()
							.then(text => {
								CVTemplate.choosen_template = text.replace('`', '');
								// thisClass.update_cv();
							})
							.catch(err => {
								console.error('Failed to read clipboard contents: ', err);
							});
					}
				});
			}
		}
		init_events() {
			const thisClass = this;var template, html;
			document.body.addEventListener('gotproductpopupresult', async (event) => {
				thisClass.prompts.lastJson = thisClass.lastJson;
				thisClass.popupCart.basePrice = parseFloat(thisClass.prompts.lastJson.product.price);
				thisClass.popupCart.priceSign = thisClass.prompts.lastJson.product.currency;
				template = await thisClass.prompts.get_template(thisClass);
				html = document.createElement('div');html.appendChild(template);
				// && json.header.product_photo
				if(thisClass.Swal && thisClass.Swal.isVisible()) {
					thisClass.prompts.progressSteps = [...new Set(thisClass.prompts.lastJson.product.custom_fields.map((row, i)=>(row.steptitle=='')?(i+1):row.steptitle))];
					thisClass.Swal.update({
						progressSteps: thisClass.prompts.progressSteps,
						currentProgressStep: 0,
						// progressStepsDistance: '10px',

						// showCloseButton: true,
						// allowOutsideClick: true,
						// allowEscapeKey: true,
						html: html.innerHTML
					});
					thisClass.prompts.lastJson = thisClass.lastJson;
					if(thisClass.lastJson.product && thisClass.lastJson.product.toast) {
						thisClass.toastify({
							text: thisClass.lastJson.product.toast.replace(/(<([^>]+)>)/gi, ""),
							duration: 45000,
							close: true,
							gravity: "top", // `top` or `bottom`
							position: "left", // `left`, `center` or `right`
							stopOnFocus: true, // Prevents dismissing of toast on hover
							style: {background: 'linear-gradient(to right, #4b44bc, #8181be)'},
							onClick: function(){} // Callback after click
						}).showToast();
					}
					setTimeout(() => {
						thisClass.prompts.init_events(thisClass);
					}, 300);
				}
			});
			document.body.addEventListener('popup_submitting_done', async (event) => {
				var submit = document.querySelector('.popup_foot .button[data-react="continue"]');
				if(submit) {submit.removeAttribute('disabled');}
				// if(thisClass.lastJson.redirectedTo) {location.href = thisClass.lastJson.redirectedTo;}
				if((thisClass.lastJson?.confirmation??false)) {
					const popupNode = thisClass.Swal.getHtmlContainer();
					thisClass.popupNode = document.createElement('div');
					thisClass.popupNode.appendChild(popupNode.childNodes[0]);
					thisClass.Swal.fire({
						title: thisClass.lastJson.confirmation?.title??'',
						// buttons: true,
						// width: 600,
						// padding: '3em',
						// color: '#716add',
						background: 'rgb(255 255 255)',
						showConfirmButton: true,
						showCancelButton: true,
						showCloseButton: true,
						allowOutsideClick: false,
						allowEscapeKey: true,
						showDenyButton: true,
						confirmButtonText: thisClass.i18n?.checkout??'Checkout',
						denyButtonText: thisClass.i18n?.buymoreplushies??'Buy more plushies',
						cancelButtonText: thisClass.i18n?.addaccessories??'Add accessories',
						confirmButtonColor: '#ffc52f',
						cancelButtonColor: '#de424b',
						dismissButtonColor: '#de424b',
						customClass: {confirmButton: 'text-dark'},
						// focusConfirm: true,
						// reverseButtons: true,
						// backdrop: `rgba(0,0,123,0.4) url("https://sweetalert2.github.io/images/nyan-cat.gif") left top no-repeat`,
						backdrop: `rgb(137 137 137 / 74%)`,

						showLoaderOnConfirm: true,
						allowOutsideClick: () => !Swal.isLoading(),
					}).then((res) => {
						if(res.isConfirmed) {
							location.href = thisClass.lastJson.confirmation?.checkoutUrl??false;
						} else if(res.isDenied) {
							location.href = thisClass.lastJson.confirmation?.accessoriesUrl??false;
						} else if(res.isDismissed) {} else {}
						console.log(res);
					});
				}
			});
			document.body.addEventListener('popup_submitting_failed', async (event) => {
				var submit = document.querySelector('.popup_foot .button[data-react="continue"]');
				if(submit) {submit.removeAttribute('disabled');}
			});
			document.body.addEventListener('ajaxi18nloaded', async (event) => {
				if(!(thisClass.lastJson?.translates??false)) {return;}
				voiceRecord.i18n = thisClass.i18n = PROMPTS.i18n = {...thisClass.i18n, ...thisClass.lastJson.translates};
			});
		}
		init_i18n() {
			const thisClass = this;
			var formdata = new FormData();
			formdata.append('action', 'futurewordpress/project/ajax/i18n/js');
			formdata.append('_nonce', thisClass.ajaxNonce);
			thisClass.sendToServer(formdata);
		}
		sendToServer( data ) {
			const thisClass = this;var message;
			$.ajax({
				url: thisClass.ajaxUrl,
				type: "POST",
				data: data,    
				cache: false,
				contentType: false,
				processData: false,
				success: function( json ) {
					thisClass.lastJson = json.data;
					if((json?.data??false)) {
						var message = ((json?.data??false)&&typeof json.data==='string')?json.data:(
							(typeof json.data.message==='string')?json.data.message:false
						);
						if( message ) {
							// thisClass.toast.fire({icon: (json.success)?'success':'error', title: message})
							thisClass.toastify({text: message,className: "info", duration: 3000, stopOnFocus: true, style: {background: (json.success)?'linear-gradient(to right, rgb(255 197 47), rgb(251 229 174))':'linear-gradient(to right, rgb(222 66 75), rgb(249 144 150))'}}).showToast();
						}
						if( json.data.hooks ) {
							json.data.hooks.forEach(( hook ) => {
								document.body.dispatchEvent( new Event( hook ) );
							});
						}
					}
				},
				error: function(err) {
					// thisClass.notify.fire({icon: 'warning',title: err.responseText})
					err.responseText = (err.responseText && err.responseText != '')?err.responseText:thisClass.i18n?.somethingwentwrong??'Something went wrong!';
					thisClass.toastify({text: err.responseText,className: "info",style: {background: "linear-gradient(to right, rgb(222 66 75), rgb(249 144 150))"}}).showToast();
					// console.log(err);
				}
			});
		}
		generate_formdata(form=false) {
			const thisClass = this;let data;
			form = (form)?form:document.querySelector('form[name="acfgpt3_popupform"]');
			if (form && typeof form !== 'undefined') {
			  const formData = new FormData(form);
			  const entries = Array.from(formData.entries());
		  
			  data = entries.reduce((result, [key, value]) => {
				const keys = key.split('[').map(k => k.replace(']', ''));
		  
				let nestedObj = result;
				for (let i = 0; i < keys.length - 1; i++) {
				  const nestedKey = keys[i];
				  if (!nestedObj.hasOwnProperty(nestedKey)) {
					nestedObj[nestedKey] = {};
				  }
				  nestedObj = nestedObj[nestedKey];
				}
		  
				const lastKey = keys[keys.length - 1];
				if (lastKey === 'acfgpt3' && typeof nestedObj.acfgpt3 === 'object') {
				  nestedObj.acfgpt3 = {
					...nestedObj.acfgpt3,
					...thisClass.transformObjectKeys(Object.fromEntries(new FormData(value))),
				  };
				} else if (Array.isArray(nestedObj[lastKey])) {
				  nestedObj[lastKey].push(value);
				} else if (nestedObj.hasOwnProperty(lastKey)) {
				  nestedObj[lastKey] = [nestedObj[lastKey], value];
				} else if ( lastKey === '') {
				  if (!Array.isArray(nestedObj[keys[keys.length - 2]])) {
					nestedObj[keys[keys.length - 2]] = [];
				  }
				  nestedObj[keys[keys.length - 2]].push(value);
				} else {
				  nestedObj[lastKey] = value;
				}
		  
				return result;
			  }, {});
		  
			  data = {
				...data?.acfgpt3??data,
			  };
			  thisClass.lastFormData = data;
			} else {
			  thisClass.lastFormData = thisClass.lastFormData?thisClass.lastFormData:{};
			}
			return thisClass.lastFormData;
		}
		transformObjectKeys(obj) {
			const transformedObj = {};
		  
			const addToArray = (fieldKey, key, value) => {
			  const arrayKey = key.split('.')[1];
			  if (!transformedObj[fieldKey][arrayKey]) {
				transformedObj[fieldKey][arrayKey] = [];
			  }
			  transformedObj[fieldKey][arrayKey].push(value);
			}
		  
			for (const key in obj) {
			  if (obj.hasOwnProperty(key)) {
				const value = obj[key];
		  
				if (key.includes('.')) {
				  // Handle keys with dots (arrays)
				  const fieldKey = key.split('.')[0];
				  if (!transformedObj[fieldKey]) {
					transformedObj[fieldKey] = {};
				  }
				  addToArray(fieldKey, key, value);
				} else if (key.includes('[') && key.includes(']')) {
				  // Handle keys with square brackets
				  const matches = key.match(/(.+?)\[(\w+)\]/);
				  if (matches && matches.length >= 3) {
					const fieldKey = matches[1];
					if (!transformedObj[fieldKey]) {
					  transformedObj[fieldKey] = {};
					}
					addToArray(fieldKey, key, value);
				  }
				} else {
				  // Handle regular keys
				  const newKey = key.replace(/\[(\w+)\]/g, '.$1').replace(/^\./, '');
		  
				  if (typeof value === 'object') {
					transformedObj[newKey] = this.transformObjectKeys(value);
				  } else {
					const keys = newKey.split('.');
					let currentObj = transformedObj;
		  
					for (let i = 0; i < keys.length - 1; i++) {
					  const currentKey = keys[i];
					  if (!currentObj[currentKey]) {
						currentObj[currentKey] = {};
					  }
					  currentObj = currentObj[currentKey];
					}
		  
					currentObj[keys[keys.length - 1]] = value;
				  }
				}
			  }
			}
		  
			return transformedObj;
		}
		init_search_form() {
			const thisClass = this;var form, html, config, json, card, node;
			document.querySelectorAll('.init_cusomizeaddtocartbtn:not([data-handled])').forEach((el)=>{
				el.dataset.handled = true;

				thisClass.resizeCartButtons(el);

				// Mode add to cart & action button on a div to fix justify spaces.
				// card = el.parentElement;node = document.createElement('div');
				// node.classList.add('fwp_custom_actions');node.appendChild(el.previousElementSibling);
				// node.appendChild(el);card.appendChild(node);
				
				el.addEventListener('click', (event) => {
					event.preventDefault();
					html = PROMPTS.get_template(thisClass);
					Swal.fire({
						title: false, // thisClass.i18n?.generateaicontent??'Generate AI content',
						width: 600,
						// padding: '3em',
						// color: '#716add',
						// background: 'url(https://png.pngtree.com/thumb_back/fh260/background/20190221/ourmid/pngtree-ai-artificial-intelligence-technology-concise-image_19646.jpg) rgb(255, 255, 255) center center no-repeat',
						showConfirmButton: false,
						showCancelButton: false,
						showCloseButton: false,
						allowOutsideClick: false,
						allowEscapeKey: true,
						// confirmButtonText: 'Generate',
						// cancelButtonText: 'Close',
						// confirmButtonColor: '#3085d6',
						// cancelButtonColor: '#d33',
						customClass: {popup: 'fwp-swal2-popup'},
						// focusConfirm: true,
						// reverseButtons: true,
						// backdrop: `rgba(0,0,123,0.4) url("https://sweetalert2.github.io/images/nyan-cat.gif") left top no-repeat`,
						backdrop: `rgb(255 255 255)`,

						showLoaderOnConfirm: true,
						allowOutsideClick: false, // () => !Swal.isLoading(),
						
						html: html,
						// footer: '<a href="">Why do I have this issue?</a>',
						didOpen: async () => {
							config = JSON.parse(el.dataset.config);
							json = {product_id: config.id};
							
							var formdata = new FormData();
							formdata.append('action', 'futurewordpress/project/ajax/search/product');
							formdata.append('dataset', await JSON.stringify(json));
							formdata.append('_nonce', thisClass.ajaxNonce);

							thisClass.sendToServer(formdata);
							thisClass.prompts.init_prompts(thisClass);
						},
						preConfirm: async (login) => {return thisClass.prompts.on_Closed(thisClass);}
					}).then( async (result) => {
						if( result.isConfirmed ) {
							if( typeof result.value === 'undefined') {
								thisClass.notify.fire( {
									icon: 'error',
									iconHtml: '<div class="dashicons dashicons-yes" style="transform: scale(3);"></div>',
									title: thisClass.i18n?.somethingwentwrong??'Something went wrong!',
								});
							} else if( thisClass.lastReqs.content_type == 'text') {
								// result.value.data 
								thisClass.handle_completion();
							} else {
								const selectedImages = await thisClass.choose_image();
							}
						}
					})
				});
			});
			window.addEventListener("resize", () => {
				document.querySelectorAll('.init_cusomizeaddtocartbtn').forEach((el)=>{
					thisClass.resizeCartButtons(el);
				});
			});
		}
		resizeCartButtons(el) {
			// [el, el.previousElementSibling].forEach((btn)=>{
			// 	// btn.setAttribute('style',((window?.innerWidth??(screen?.width??0)) <= 500)?'width: 48% !important;padding: 10px 10px !important;font-size: 10px !important;display: unset !important;':'padding: 10px 5px !important;font-size: 15px !important;');
			// });
			el.previousElementSibling.classList.remove('button');
		}

		move_lang_switcher() {
			var node, wrap, lang, next;
			lang = document.querySelector('#trp-floater-ls.trp-language-switcher-container.trp-bottom-right.trp-color-dark');
			next = document.querySelector('.elementor-element.elementor-search-form--skin-full_screen.elementor-widget__width-auto.elementor-widget.elementor-widget-search-form');
			if(lang && next) {
				node = document.createElement('div');node.classList.add('elementor-element', 'elementor-element-afd7b23', 'elementor-widget__width-auto', 'elementor-view-default', 'elementor-widget', 'elementor-widget-icon');
				wrap = document.createElement('div');wrap.classList.add('elementor-widget-container');
				wrap.appendChild(lang);node.appendChild(wrap);
				next.parentElement.insertBefore(node, next);
			}
		}
		popup_show_only4israel() {
			const dateString = new Date().toString();
			const isIsrael = /\bIsrael\b/i.test(dateString);
			if(isIsrael) {
				let i = 0;
				var theTimeout = setTimeout(() => {
					const hasPopup = document.querySelector('.dialog-widget.dialog-lightbox-widget.dialog-type-buttons.dialog-type-lightbox.elementor-popup-modal');
					if(hasPopup) {
						hasPopup.setAttribute('style', 'display: flex !important');
						clearTimeout(theTimeout);
					}
					// if(i >= 200) {clearTimeout(theTimeout);}i++;
				}, 500);
			}
		}
		move_cart_icon2header() {
			const heart = document.querySelector('.elementor-location-header .elementor-container .elementor-column:last-child .elementor-widget-wrap .elementor-widget-icon:last-child');
			const cart = document.querySelector('.cc-compass .licon');
			if(!heart || !cart) {return;}
			cart.innerHTML = `
			<svg width="18" height="16" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M8.43555 15.75C7.35011 15.75 6.4668 16.6333 6.4668 17.7188C6.4668 18.8042 7.35011 19.6875 8.43555 19.6875C9.52098 19.6875 10.4043 18.8042 10.4043 17.7188C10.4043 16.6333 9.52098 15.75 8.43555 15.75ZM8.43555 18.375C8.0733 18.375 7.7793 18.0803 7.7793 17.7188C7.7793 17.3572 8.0733 17.0625 8.43555 17.0625C8.7978 17.0625 9.0918 17.3572 9.0918 17.7188C9.0918 18.0803 8.7978 18.375 8.43555 18.375ZM14.998 15.75C13.9126 15.75 13.0293 16.6333 13.0293 17.7188C13.0293 18.8042 13.9126 19.6875 14.998 19.6875C16.0835 19.6875 16.9668 18.8042 16.9668 17.7188C16.9668 16.6333 16.0835 15.75 14.998 15.75ZM14.998 18.375C14.6358 18.375 14.3418 18.0803 14.3418 17.7188C14.3418 17.3572 14.6358 17.0625 14.998 17.0625C15.3603 17.0625 15.6543 17.3572 15.6543 17.7188C15.6543 18.0803 15.3603 18.375 14.998 18.375ZM19.3989 5.39306C19.0235 4.88513 18.446 4.59375 17.8147 4.59375H5.26323L4.60764 2.26931C4.44948 1.70559 3.92973 1.3125 3.3437 1.3125H1.87305C1.51014 1.3125 1.2168 1.6065 1.2168 1.96875C1.2168 2.331 1.51014 2.625 1.87305 2.625H3.34436L4.13252 5.42063L5.50867 11.3853C5.92342 13.1821 7.50105 14.4375 9.34577 14.4375H14.5433C16.2823 14.4375 17.7956 13.3206 18.3069 11.6576L19.6961 7.14197C19.8819 6.53888 19.7736 5.901 19.3989 5.39306ZM18.4414 6.75544L17.0521 11.2718C16.7109 12.3802 15.7029 13.125 14.5433 13.125H9.34577C8.11595 13.125 7.06398 12.2883 6.78836 11.0906L5.59202 5.90625H17.8147C18.0253 5.90625 18.2176 6.00338 18.343 6.17269C18.4676 6.342 18.5037 6.55397 18.4414 6.75544Z" fill="#444444"/>
			</svg>`;
			const node = document.createElement('div');node.classList.add('elementor-element', 'elementor-element-afd7b23', 'elementor-widget__width-auto', 'elementor-view-default', 'elementor-widget', 'elementor-widget-icon');node.dataset.element_type = 'widget';
			node.dataset.widget_type = 'icon.default';
			node.innerHTML = `
				<div class="elementor-widget-container">
					<div class="elementor-icon-wrapper">
						<a class="elementor-icon fwp-custom-cart-icon">
						</a>
					</div>
				</div>
			`;
			node.querySelector('.fwp-custom-cart-icon').appendChild(cart.parentElement);
			heart.parentElement.appendChild(node);
			const mobileIcon = document.querySelector('#uael-mc__btn').parentElement;
			mobileIcon.innerHTML = '';
			mobileIcon.appendChild(
				node.querySelector('.fwp-custom-cart-icon').cloneNode(true)
			);
			// console.log(heart.parentElement, node);
		}
		
		clearAllFromCart() {
			document.querySelectorAll('.woocommerce-page #content table.cart td.product-remove a').forEach((el)=>{el.click();});
		}

		
	}
	new FutureWordPress_Frontend();
} )( jQuery );
