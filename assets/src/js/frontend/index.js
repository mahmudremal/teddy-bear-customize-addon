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
import KeenSlider from 'keen-slider';
import tippy from 'tippy.js';
// import Splide from '@splidejs/splide';

// import icons from "./icons";
import { keenSliderNavigation } from "./slider";
import Exim from "./exim"
import Tidio_Chat from "./tidio";
// import Twig from 'twig';

( function ( $ ) {
	class FutureWordPress_Frontend {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl??'';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce??'';this.tippy = tippy;
			this.lastAjax = false;this.profile = fwpSiteConfig?.profile??false;
			var i18n = fwpSiteConfig?.i18n??{};this.noToast = true;
			this.config = fwpSiteConfig;this.KeenSlider = KeenSlider;
			this.i18n = {confirming: 'Confirming', ...i18n};
			this.Exim = new Exim(this);// this.Splide = Splide;
			this.setup_hooks();
		}
		setup_hooks() {
			const thisClass = this;
			window.thisClass = this;
			this.prompts = PROMPTS;
			this.Swal = Swal;
			// this.Twig = Twig;
			this.flatpickr = flatpickr;
			popupCart.priceSign = this.config?.currencySign??'$';
			popupCart.local = this.config?.local??'en-US';
			popupCart.ajaxUrl = this.ajaxUrl;
			this.popupCart = popupCart;
			this.init_toast();
			this.init_events();
			this.init_i18n();
			this.init_search_form();
			this.init_remove_acc_form();
			voiceRecord.i18n = this.i18n;
			PROMPTS.i18n = this.i18n;
			voiceRecord.init_recorder(this);
			this.voiceRecord = voiceRecord;
			new Tidio_Chat();

			// this.move_lang_switcher();

			this.show_add_to_wishlist();
			this.popup_show_only4israel();
			this.move_cart_icon2header();
			this.init_wrapping_on_checkout();
			this.single_product_input_stepper();
			this.remove_my_account_points_tab();
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

				if(typeof woocs_convert_price_filter == 'function') {
					const product = thisClass.lastJson.product;
					product.price = woocs_convert_price_filter(parseFloat(product.price));
					thisClass.lastJson.product.custom_fields = thisClass.lastJson.product.custom_fields.map((row) => {
						if(row?.cost) {row.cost = woocs_convert_price_filter(parseFloat(row.cost));}
						if(row?.options) {
							row.options = Object.values(row.options).map((crow) => {
								if(crow?.cost) {crow.cost = woocs_convert_price_filter(parseFloat(crow.cost));}
								return crow;
							});
						}
						if(row?.groups) {
							row.groups = Object.values(row.groups).map((grow) => {
								if(grow?.cost) {grow.cost = woocs_convert_price_filter(parseFloat(grow.cost));}
								if(grow?.options) {
									grow.options = Object.values(grow.options).map((crow) => {
										if(crow?.cost) {crow.cost = woocs_convert_price_filter(parseFloat(crow.cost));}
										return crow;
									});
								}
								return grow;
							});
						}
						return row;
					});
				}
				// thisClass.prompts.lastJson.product.price

				thisClass.prompts.lastJson = thisClass.lastJson;
				thisClass.popupCart.additionalPrices = [];
				thisClass.popupCart.basePrice = parseFloat(thisClass.prompts.lastJson.product.price);
				thisClass.popupCart.priceSign = thisClass.prompts.lastJson.product.currency;
				var custom_fields = PROMPTS.get_data(thisClass, true);
				if(custom_fields.sitting?.length <= 0 || custom_fields.standing?.length <= 0) {
					PROMPTS.currentGroups = (custom_fields.sitting?.length >= 1)?'sitting':'standing';
					PROMPTS.groupSelected = true;
				}
				template = await thisClass.prompts.get_template(thisClass);
				html = document.createElement('div');html.appendChild(template);
				// && json.header.product_photo
				var current_groups = PROMPTS.get_data(thisClass);
				if (thisClass.Swal && thisClass.Swal.isVisible()) {
					var steps = (current_groups)?current_groups.map((row, i)=>(row.steptitle=='')?(i+1):(
						`${(row?.stepicon)?`<div class="swal2-progress-step__icon">${row.stepicon}</div>`:``}
						<span>${row.steptitle}</span>`
					)):[];
					// thisClass.prompts.progressSteps = [...new Set(thisClass.prompts.lastJson.product.custom_fields.map((row, i)=>(row.steptitle=='')?(i+1):row.steptitle))];
					thisClass.prompts.progressSteps = [...new Set(steps)];
					thisClass.Swal.update({
						html: html.innerHTML,
						currentProgressStep: 0,
						progressSteps: thisClass.prompts.progressSteps
					});
					thisClass.prompts.lastJson = thisClass.lastJson;
					if(thisClass.lastJson.product && thisClass.lastJson.product.toast) {
						thisClass.toastify({
							text: thisClass.lastJson.product.toast.replace(/(<([^>]+)>)/gi, ""),
							duration: 45000,
							close: true,
							gravity: "top", // 'top' or 'bottom'
							position: "left", // 'left', 'center' or 'right'
							stopOnFocus: true, // Prevents dismissing of toast on hover
							style: {background: 'linear-gradient(to right, #4b44bc, #8181be)'},
							onClick: function(){} // Callback after click
						}).showToast();
					}
					setTimeout(() => {
						var fields = thisClass.prompts.get_data(thisClass);
						if(fields) {
							var voice = fields.find((row)=>row.type == 'voice');
							if(voice) {
								voice.cost = (!(voice?.cost) || voice.cost == '')?0:voice.cost;
								// voiceRecord.meta_tag = voice.steptitle;
								voiceRecord.product_id = (voice?.product && parseInt(voice.product) !== NaN)?parseInt(voice.product):false;
								
								voiceRecord.duration = parseFloat((voice.duration == '')?'20':voice.duration);
								// popupCart.addAdditionalPrice(voice.steptitle, parseFloat(voice.cost));
							}
							if(PROMPTS.groupSelected) {
								thisClass.prompts.init_events(thisClass);
							} else {
								thisClass.prompts.init_group_select_events(thisClass);
							}
						} else {
							var imageUrl = thisClass.lastJson.product?.empty_image;
							if(imageUrl) {
								var popup = document.querySelector('.dynamic_popup');
								popup.querySelectorAll('.dynamic_popup__error').forEach(el => el.remove());
								var template = document.createElement('div');
								var image = document.createElement('img');
								image.src = imageUrl;image.alt = 'Error happens';
								image.style.width = '100%';image.style.padding = '10px';
								template.classList.add('dynamic_popup__error');
								template.appendChild(image);popup.appendChild(template);
							}
							
						}
						/**
						 * Enabling close button event.
						 */
						document.querySelectorAll('.popup_close:not([data-handled])').forEach((el) => {
							el.dataset.handled = true;
							el.addEventListener('click', (event) => {
								event.preventDefault();
								if(confirm(PROMPTS.i18n?.rusure2clspopup??'Are you sure you want to close this popup?')) {
									thisClass.Swal.close();
								}
							});
						});
					}, 300);
				}
			});
			document.body.addEventListener('popup_submitting_done', async (event) => {
				var submit = document.querySelector('.popup_foot .button[data-react="continue"]');
				if(submit) {submit.removeAttribute('disabled');}
				// if(thisClass.lastJson.redirectedTo) {location.href = thisClass.lastJson.redirectedTo;}
				if((thisClass.lastJson?.confirmation??false)) {
					thisClass.confirmation = thisClass.lastJson.confirmation;
					const popupNode = thisClass.Swal.getHtmlContainer();
					thisClass.popupNode = document.createElement('div');
					thisClass.popupNode.appendChild(popupNode.childNodes[0]);
					thisClass.cartItemKey = thisClass.confirmation?.cartItemKey??'';
					thisClass.Swal.fire({
						title: thisClass.confirmation?.title??'',
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
						denyButtonText: thisClass.i18n?.addaccessories??'Add accessories',
						cancelButtonText: thisClass.i18n?.buymoreplushies??'Buy more plushies',
						confirmButtonColor: '#ffc52f',
						cancelButtonColor: '#de424b',
						customClass: {popup: 'fwp-confirmed_popup', confirmButton: 'text-dark'},
						// focusConfirm: true,
						// reverseButtons: true,
						// dismissButtonColor: '#de424b',
						backdrop: 'rgb(137 137 137 / 74%)',
						html: '<div class="dynamic_popup"></div>',
						showLoaderOnConfirm: true,
						footer: false,
						didOpen: async () => {
							document.querySelector('.dynamic_popup')?.appendChild(
								thisClass.popupNode.querySelector('.header_image')
							);
							const addition = document.createElement('div');
							addition.innerHTML = `
							${(thisClass.lastJson?.wrapping)?`
								<div class="swal2-footer__wraping">
									<label class="swal2-footer__wraping__tooltip">
										<input type="checkbox" name="add_wraping" value="true" onchange="document.querySelector('#addwrapping_checkbox')?.click();"/>
										${
											(((thisClass.lastJson?.wrapping)?.thumbnail??'').trim() != '')?`
											<img class="swal2-footer__wraping__thumbnail" src="${(thisClass.lastJson?.wrapping)?.thumbnail??''}" alt="Packaging" />
											`:''
										}
										${(thisClass.lastJson?.wrapping)?.title??(thisClass.i18n?.addwrappingpaper??'Add wrapping paper')} (${thisClass.config?.currencySign??''} ${((thisClass.lastJson?.wrapping)?.price??0).toFixed(2)})

										<button class="btn btn-primary button" id="addwrapping_checkbox" data-mode="add">
											<span>${thisClass.i18n?.addwrapping??'Add wrapping'}</span>
											<div class="spinner-circular-tube"></div>
										</button>
									</label>
								</div>
							`:''}
							<div class="swal2-footer__footer">
								<h3 class="swal2-footer__footer__title">${thisClass.i18n?.youmayalsolike??'You may also like'}</h3>
								<div class="keen-slider keen-slider__extras">
									${thisClass.confirmation.suggestion.map((product, i)=>`
									<div class="keen-slider__slide number-slide${(i + 1)}" title="${thisClass.esc_attr(product.title)}" data-cost="${product.price}" data-product="${product.ID}">
										${product.thumbnail} ${product.priceHtml}
									</div>
									`).join('')}
								</div>
							</div>
							`;
							
							setTimeout(() => {
								Object.values(addition.children).forEach((child) => {
									document.querySelector('.dynamic_popup')?.appendChild(child);
								});
								document.querySelectorAll('.swal2-footer__wraping__tooltip:not([data-tooltip-handled])').forEach((el) => {
									el.dataset.tooltipHandled = true;
									thisClass.tippy(el, {
										content: thisClass.i18n?.obtainplushieswraps??'Obtain plushies that come in wrapped packaging.',
										animation: 'perspective-extreme',
										theme: 'site-theme'
									});
								});
								const slider = new thisClass.KeenSlider('.keen-slider__extras:not([data-slides-handled])', {
									loop: false, mode: "free",
									slides: {perView: 5, spacing: 5},
								}, [
									slider => {
										slider.on('created', () => {
											slider.container.dataset.slidesHandled = true;
										})
									}
								]);
								setTimeout(() => {
									document.querySelectorAll('.keen-slider .keen-slider__slide').forEach((el) => {
										el.addEventListener('click', (e) => {
											if(el.classList.contains('active')) {
												el.classList.remove('active');
												popupCart.wc_removeAdditionalPrice(el, thisClass);
											} else {
												el.classList.add('active');
												popupCart.wc_addAdditionalPrice(el, thisClass);
											}
										});
									});
									document.querySelectorAll('.swal2-footer__wraping .btn').forEach((el) => {
										if(!(thisClass?.addWrappingBtn)) {
											thisClass.addWrappingBtn = el;
										}
										el.addEventListener('click', (event) => {
											event.preventDefault();el.disabled = true;
											// thisClass.addWrappingBtn = el;
											var formdata = new FormData();
											formdata.append('action', 'futurewordpress/project/ajax/add/wrapping');
											formdata.append('_quantity', 1);
											formdata.append('_mode', el.dataset?.mode);
											formdata.append('cartitemkey', thisClass.cartItemKey);
											formdata.append('_nonce', thisClass.ajaxNonce);
											thisClass.sendToServer(formdata);
											setTimeout(() => {el.disabled = false;}, 20000);
										});
									});
								}, 1200);
							}, 300);
						},
						allowOutsideClick: () => !Swal.isLoading(),
					}).then((res) => {

						console.log(res.isConfirmed, res.isDenied, res.isDismissed);
						
						if(res.isConfirmed) {
							/**
							 * Confirm is Checkout
							 */
							if(thisClass.confirmation?.checkoutUrl) {
								location.href = thisClass.confirmation?.checkoutUrl??false;
							}
						} else if(res.isDenied) {
							/**
							 * Denied is accessories
							 */
							if(thisClass.confirmation?.accessoriesUrl) {
								location.href = thisClass.confirmation?.accessoriesUrl??false;
							}
						} else if(res.isDismissed) {
							/**
							 * Dismiss is Buy more plushies
							 */
						} else {}
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
			document.body.addEventListener('namesuggestionloaded', async (event) => {
				if(!(thisClass.lastJson?.names??false)) {return;}
				PROMPTS.names = thisClass.lastJson.names;
			});
			document.body.addEventListener('wrapping_adding_success', async (event) => {
				if(thisClass?.addWrappingBtn) {
					// thisClass.addWrappingBtn.disabled = false;
					// thisClass.addWrappingBtn.querySelector('i.fa')?.classList.remove('fa-circle-o-notch', 'fa-spin');
					// location.reload();
					thisClass.addWrappingBtn.dataset.mode = 'del';thisClass.addWrappingBtn.disabled = false;
					thisClass.addWrappingBtn.querySelector('span').innerHTML = thisClass.i18n?.removewrapping??'Remove Wrapping';
				}
			});
			document.body.addEventListener('wrapping_removing_success', async (event) => {
				if(thisClass?.addWrappingBtn) {
					// thisClass.addWrappingBtn.disabled = false;
					// thisClass.addWrappingBtn.querySelector('i.fa')?.classList.remove('fa-circle-o-notch', 'fa-spin');
					// location.reload();
					thisClass.addWrappingBtn.dataset.mode = 'add';thisClass.addWrappingBtn.disabled = false;
					thisClass.addWrappingBtn.querySelector('span').innerHTML = thisClass.i18n?.addwrapping??'Add Wrapping';
				}
			});
		}
		init_i18n() {
			const thisClass = this;
			var formdata = new FormData();
			formdata.append('action', 'futurewordpress/project/ajax/i18n/js');
			formdata.append('_nonce', thisClass.ajaxNonce);
			thisClass.sendToServer(formdata);

			var formdata = new FormData();
			formdata.append('action', 'futurewordpress/project/ajax/suggested/names');
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
			const thisClass = this;var form, html, config, json, card, node, image;
			document.querySelectorAll('.fwp_custom_actions[data-gallery]:not([data-gallery="[]"]):not([data-handled-gallery])').forEach((el) => {
				el.dataset.handledGallery = true;
				var container = el.parentElement.parentElement?.querySelector('.uael-woo-products-thumbnail-wrap .woocommerce-loop-product__link');
				if(container) {
					var preview = [...container.childNodes].find((el) => el.nodeName.toLowerCase() == 'img');
					if(preview) {
						preview.removeAttribute('srcset');container.classList.add('keen-slider');
						container.classList.add('uael-gallery', 'navigation-wrapper');
						
						// container.parentElement.classList.add('');
						// var node = document.createElement('div');
						container.classList.add('uael-gallery__row');
						var gallery = JSON.parse(el.dataset.gallery);

						var isExists = gallery.find((row) => row.image_url[0] == preview.src);
						if(!isExists) {
							gallery = [
								{
									id			: 0,
									thumb_title : preview.src.split('/').pop(),
									image_url	: [preview.src, 300, 300, true],
									thumb_url	: [preview.src, 300, 300, true],
								},
								...gallery
							];
							// console.log();
						}
						
						gallery.forEach((row, i) => {
							var card = document.createElement('div');
							card.classList.add('uael-gallery__item', 'keen-slider__slide', 'number-slide'+ (i + 1));
							image = document.createElement('img');image.className = preview.className;
							image.classList.add('uael-gallery__image');image.src = row.image_url[0];
							card.dataset.imageFull = row.image_url[0];
							image.alt = row.thumb_title;image.title = row.thumb_title;
							image.width = row.image_url[1];image.height = row.image_url[2];
							card.appendChild(image);container.appendChild(card);
							// card.addEventListener('click', (event) => {
							// 	event.stopPropagation();event.preventDefault();
							// 	if(preview && preview.src != card.dataset.imageFull) {
							// 		preview.src = card.dataset.imageFull;
							// 		console.log(preview.src);
							// 	}
							// });
						});
						/**
						 * Remove preview Items
						 */
						preview.remove();

						/**
						 * Init Keen Slider
						 */
						const slider = new thisClass.KeenSlider(container, {}, [keenSliderNavigation]);
						// slider => {
						// 	// slider.on('slideChanged', () => {
						// 	// 	console.log('slide changed', slider);
						// 	// })
						// }
						// container.appendChild(node);

						/**
						 * Adding Arrows on Slider
						 */
						// var arrows = document.createElement('div');arrows.classList.add('keen-slider__arrows');
						// arrows.innerHTML = icons.left;
						// arrows.innerHTML += icons.right;
						// container.parentElement.insertBefore(arrows, container);
                        // arrows.querySelectorAll('.svg_icon').forEach((elem) => {
                        //     elem.addEventListener('click', (event) => {
                        //         var arrow_mode = ((elem?.querySelector('svg'))?.dataset)?.arrow;
                        //         switch(arrow_mode) {
                        //             case 'left':
                        //                 slider.prev();
                        //                 break;
                        //             case 'right':
                        //                 slider.next();
                        //                 break;
                        //             default:
                        //                 break;
                        //         }
                        //     });
                        // });

					}
				}

			});
			document.querySelectorAll('.init_cusomizeaddtocartbtn:not([data-handled])').forEach((el) => {
				el.dataset.handled = true;
				thisClass.resizeCartButtons(el);
				// Mode add to cart & action button on a div to fix justify spaces.

				// card = el.parentElement;node = document.createElement('div');
				// node.classList.add('fwp_custom_actions');node.appendChild(el.previousElementSibling);
				// node.appendChild(el);card.appendChild(node);
				
				el.addEventListener('click', (event) => {
					event.preventDefault();
					PROMPTS.groupSelected = false;
					PROMPTS.currentGroups = 'standing';
					html = PROMPTS.get_template(thisClass);
					Swal.fire({
						title: false, width: 600,
						showConfirmButton: false,
						showCancelButton: false,
						showCloseButton: false,
						allowOutsideClick: false,
						allowEscapeKey: true,
						customClass: {popup: 'fwp-swal2-popup'},
						// backdrop: 'rgb(255 255 255 / 90%)',
						showLoaderOnConfirm: true,
						allowOutsideClick: false, // () => !Swal.isLoading(),
						
						html: html,
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
				document.querySelectorAll('.init_cusomizeaddtocartbtn').forEach((el) => {
					thisClass.resizeCartButtons(el);
				});
			});
		}
		resizeCartButtons(el) {
			// [el, el.previousElementSibling].forEach((btn) => {
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
			// const isIsrael = /\bBangladesh\b/i.test(dateString);
			if(isIsrael) {
				let i = 0;
				var theInterval = setInterval(() => {
					const hasPopup = document.querySelector('.dialog-widget.dialog-lightbox-widget.dialog-type-buttons.dialog-type-lightbox.elementor-popup-modal#elementor-popup-modal-788');
					if(hasPopup) {
						hasPopup.setAttribute('style', 'display: flex !important');
						clearInterval(theInterval);
					} else {
						i++;
					}
					if(i >= 200) {clearInterval(theInterval);}i++;
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
			const mobileIcon = document.querySelector('#uael-mc__btn')?.parentElement;
			if(!mobileIcon) {return;}mobileIcon.innerHTML = '';
			const clonedNode = node.querySelector('.fwp-custom-cart-icon').cloneNode(true);
			clonedNode.onclick = null;
			mobileIcon.appendChild(clonedNode);
		}
		show_add_to_wishlist() {
			const store = document.createElement('div');
			setInterval(() => {
				document.querySelectorAll('.uael-woo-product-wrapper .yith-wcwl-add-to-wishlist:not([data-handled])').forEach((el) => {
					el.dataset.handled = true;
					var btn = el.querySelector('.yith-wcwl-add-button a');
					var icon = btn?.querySelector('i');
					if(btn && icon) {
						store.appendChild(icon);
						btn.innerHTML = '';
						btn.appendChild(store.querySelector('i'));
					}
					tippy(el, {
						content: (btn)?btn.dataset.title:(
							thisClass?.add2wishlist??'Add to wishlist'
						)
					});
				});
			}, 1500);
		}
		init_wrapping_on_checkout() {
			/**
			 * Add wraping widget for all product on checkout page
			 */
			const thisClass = this;
			const wrapping = document.querySelector('.wc_add_wrapping');
			if(wrapping) {
				const button = wrapping?.querySelector('.btn-rounded');
				button?.addEventListener('click', (event) => {
					event.preventDefault();
					button.disabled = true;button.querySelector('i.fa')?.classList.add('fa-circle-o-notch', 'fa-spin');
					var formdata = new FormData();
					formdata.append('action', 'futurewordpress/project/ajax/add/wrapping');
					formdata.append('_nonce', thisClass.ajaxNonce);
					formdata.append('_quantity', 1);
					formdata.append('_mode', button.dataset?.mode);
					thisClass.sendToServer(formdata);

					setTimeout(() => {
						button.disabled = false;button.querySelector('i.fa')?.classList.remove('fa-circle-o-notch', 'fa-spin');
						setTimeout(() => {location.reload();}, 2000);
					}, 20000);
				});
			}
		}
		single_product_input_stepper() {
			document.querySelectorAll('#content .elementor-location-single.product.type-product.ast-article-single .elementor-widget-uael-woo-add-to-cart form.cart .quantity input[type="number"][name="quantity"]').forEach((el) => {
				var minus = document.createElement('div');var plus = document.createElement('div');
				minus.classList.add('cc_item_quantity_update', 'cc_item_quantity_minus');
				plus.classList.add('cc_item_quantity_update', 'cc_item_quantity_plus');
				minus.innerHTML = '-';plus.innerHTML = '+';
				plus.addEventListener('click', () => {
					let currentValue = parseFloat(el.value);
					const step = (el?.step)?parseFloat(el.step):1;
					const max = (el?.max)?parseFloat(el.max):Number.MAX_SAFE_INTEGER;
					if(!isNaN(currentValue) && (isNaN(max) || currentValue < max)) {
						currentValue += step;el.value = currentValue;
					}
				});
				minus.addEventListener('click', () => {
					let currentValue = parseFloat(el.value);
					const step = (el?.step)?parseFloat(el.step):1;
					const min = (el?.min)?parseFloat(el.min):1;
					if(!isNaN(currentValue) && (isNaN(min) || currentValue > min)) {
						currentValue -= step;el.value = currentValue;
					}
				});
				
				el.classList.add('cc_item_quantity');
				el.parentElement.classList.add('cc_item_quantity_wrap');
				el.parentElement.style.display = 'flex';
				
				el.parentElement.insertBefore(minus, el);el.parentElement.appendChild(plus);
			});
		}
		
		clearAllFromCart() {
			document.querySelectorAll('.woocommerce-page #content table.cart td.product-remove a').forEach((el) => {el.click();});
		}
		esc_attr(text) {
			return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}

		remove_my_account_points_tab() {
			document.querySelectorAll('body.myac-points:not(.is-role-member) .woocommerce-MyAccount-content').forEach((el) => {el.remove();});
			/** Move scroll top btn from right to left */
			document.querySelector('#ast-scroll-top.ast-scroll-to-top-right')?.classList.toggle('ast-scroll-to-top-right', 'ast-scroll-to-top-left');
		}

		init_checkout_metadata() {
			const thisClass = this;
		}

		init_remove_acc_form() {
			const thisClass = this;
			document.querySelectorAll('.woocommerce-removeAccountForm').forEach((form) => {
				form.addEventListener('submit', (event) => {
					const message = thisClass.i18n?.delete_acc_confirmation??'Are you sure you want to delete all of your account information? This will permanently remove you account with all relateed informations.';
					if(confirm(message)) {
						return true;
					} else {
						event.preventDefault();
						return false;
					}
				});
			});
			if (thisClass.config?.notifications) {
				document.querySelectorAll('main#main.site-main .entry-title').forEach(entry => {
					var notifys = document.createElement('div');notifys.classList.add('notifys');
					notifys.innerHTML = thisClass.config.notifications.map((row, index) => `
					<div class="alert alert-danger" role="alert">
						${row?.message??''}
					</div>
					`).join('');
					entry.appendChild(notifys);
				});
			}
		}
		
	}
	new FutureWordPress_Frontend();
} )( jQuery );
