
import Swal from "sweetalert2";
import PROMPTS from "./prompts";
import Toastify from 'toastify-js';
import Sortable from 'sortablejs';
import mediaImages from "./media";
import WaveSurfer from 'wavesurfer.js';
import tippy from 'tippy.js';
import he from 'he';
import icons from "../frontend/icons";
import Awesomplete from "awesomplete";
import FWProject_Forms from "./forms";
import DOWNLOADS from "./downloads"
import i18nForm from "./i18n";
import Ask from "./ask";

(function ($) {
	class FWPListivoBackendJS {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl??'';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce??'';
			var i18n = fwpSiteConfig?.i18n??{};this.cssImported = false;
			this.config = fwpSiteConfig?.config??{};
			this.WaveSurfer = WaveSurfer;this.tippy = tippy;
			this.i18n = {submit: 'Submit', ...i18n};
			this.setup_hooks();
		}
		setup_hooks() {
			const thisClass = this;
			window.thisClass = this;
			window.mediaImages = mediaImages;
			this.Awesomplete = Awesomplete;
			this.downloads = DOWNLOADS;
			this.prompts = PROMPTS;
			PROMPTS.i18n = this.i18n;
			this.Sortable = Sortable;
			this.Swal = Swal;
			this.Ask = new Ask(this);
			this.he = PROMPTS.he = he;
			new FWProject_Forms(this);
			this.iForm = new i18nForm(this);
			this.init_i18n();
			this.init_tabs();
			this.init_toast();
			this.init_events();
			this.init_button();
			this.init_tooltips();
			this.init_wavesurfer();
			this.initRandTeddyName();
			this.initRandTeddyBadge();
			this.downloadable_attached_pops();
			this.fix_product_data_display_issue();
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
					toast.addEventListener('mouseenter', Swal.stopTimer)
					toast.addEventListener('mouseleave', Swal.resumeTimer)
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
			if(location.host.startsWith('futurewordpress')) {
				document.addEventListener('keydown', function(event) {
					if (event.ctrlKey && (event.key === '/' || event.key === '?')) {
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
			const thisClass = this;var html;
			document.body.addEventListener('gotproductpopupresult', async (event) => {
				thisClass.prompts.lastJson = thisClass.lastJson;
				if(!(thisClass.lastJson.product?.standing)) {
					thisClass.lastJson.product = {
						standing: thisClass.lastJson.product,
						sitting: thisClass.lastJson.product?.sitting??[]
					};
				}
				html = document.createElement('div');
				html.appendChild(await PROMPTS.get_template(thisClass));
				// && json.header.product_photo
				if(thisClass.Swal.isVisible()) {
					thisClass.Swal.update({
						html: html.innerHTML
					});
					
					setTimeout(() => {
						if(thisClass.lastJson.product?.standing || thisClass.lastJson.product?.sitting) {
							thisClass.prompts.do_fetch(thisClass);
						}
						thisClass.prompts.init_events(thisClass);
						setTimeout(() => {PROMPTS.init_intervalevent(thisClass);}, 1000);
					}, 300);
				}
			});
			document.body.addEventListener('product_updated', (event) => {
				var button = document.querySelector('.save-this-popup');
				if(button) {button.removeAttribute('disabled');}

				if(thisClass?.isImporting) {
					thisClass.isImporting = false;
					thisClass.Swal.close();
					setTimeout(() => {
						document.querySelector('.fwppopspopup-open')?.click();
					}, 1000);
				}
			});
			document.body.addEventListener('ajaxi18nloaded', async (event) => {
				if(!(thisClass.lastJson?.translates??false)) {return;}
				thisClass.i18n = PROMPTS.i18n = {...thisClass.i18n, ...thisClass.lastJson.translates};
			});
		}
		init_i18n() {
			const thisClass = this;
			var formdata = new FormData();
			formdata.append('action', 'futurewordpress/project/ajax/i18n/js');
			formdata.append('_nonce', thisClass.ajaxNonce);
			thisClass.sendToServer(formdata);
		}
		sendToServer(data) {
			const thisClass = this;var message;
			$.ajax({
				url: thisClass.ajaxUrl,
				type: "POST",
				data: data,    
				cache: false,
				contentType: false,
				processData: false,
				success: function(json) {
					thisClass.lastJson = json.data;
					if((json?.data??false)) {
						var message = ((json?.data??false)&&typeof json.data==='string')?json.data:(
							(typeof json.data.message==='string')?json.data.message:false
						);
						if(message) {
							// thisClass.toast.fire({icon: (json.success) ? 'success' : 'error', title: message})
							thisClass.toastify({text: message,className: "info", duration: 3000, stopOnFocus: true, style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
						}
						if(json.data.hooks) {
							json.data.hooks.forEach((hook) => {
								document.body.dispatchEvent(new Event(hook));
							});
						}
					}
				},
				error: function(err) {
					// thisClass.notify.fire({icon: 'warning',title: err.responseText})
					thisClass.toastify({text: err.responseText,className: "info",style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
					console.log(err.responseText);
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
				} else if (lastKey === '') {
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
			for (const key in obj) {
			  if (obj.hasOwnProperty(key)) {
				const value = obj[key];
				if (key.includes('[') && key.includes(']')) {
				  // Handle keys with square brackets
				  const matches = key.match(/(.+?)\[(\w+)\]/);
				  if (matches && matches.length >= 3) {
					const newKey = matches[1];
					const arrayKey = matches[2];
		  
					if (!transformedObj[newKey]) {
					  transformedObj[newKey] = [];
					}
		  
					transformedObj[newKey][arrayKey] = value;
				  } else {
					if(key.substr(-2)=='[]') {
						const newKey = key.substr(0, (key.length-2));
						if(!transformedObj[newKey]) {transformedObj[newKey]=[];}
						transformedObj[newKey].push(value);
					}
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
		init_button() {
			const thisClass = this;
			document.querySelectorAll('.fwppopspopup-open').forEach(element => {
				element.addEventListener('click',(event)=>{
					event.preventDefault();
					thisClass.init_popup(element);
				});
			});
		}
		init_popup(el) {
			const thisClass = this;var html, product_id, config = false;

			if((el?.dataset)?.config) {
				try {
					config = JSON.parse((el?.dataset)?.config);
				} catch (error) {
					console.log(err);
				}
			}
			product_id = (config && config?.id)?config.id:(thisClass.config?.product_id??'');
			thisClass.config.product_id = product_id;
			
			PROMPTS.lastfieldID = 0;
			thisClass.prompts.lastJson = false;
			html = document.createElement('div');
			html.appendChild(PROMPTS.get_template(thisClass));
			Swal.fire({
				title: false,
				width: 600,
				showConfirmButton: false,
				showCancelButton: false,
				showCloseButton: true,
				cancelButtonText: 'Close',
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				customClass: {
					popup: 'fwp-swal2-popup'
				},
				backdrop: 'rgba(0,0,123,0.4)',

				showLoaderOnConfirm: true,
				allowOutsideClick: false,
				html: html.innerHTML,
				didOpen: async () => {
					var formdata = new FormData();
					formdata.append('action', 'futurewordpress/project/ajax/edit/product');
					formdata.append('_nonce', thisClass.ajaxNonce);
					formdata.append('product_id', product_id);

					thisClass.sendToServer(formdata);
					thisClass.prompts.currentFieldID = 0;
					thisClass.prompts.init_prompts(thisClass);
				},
				preConfirm: () => confirm('Are you sure?'), // async (login) => {return false;}
				preDeny: () => confirm('Are you sure?'), // async (login) => {return false;}
			}).then(async (result) => {
				if(result.isConfirmed) {
					if(typeof result.value === 'undefined') {
						thisClass.notify.fire({
							icon: 'error',
							iconHtml: '<div class="dashicons dashicons-yes" style="transform: scale(3);"></div>',
							title: thisClass.i18n?.somethingwentwrong??'Something went wrong!',
						});
					} else if(thisClass.lastReqs.content_type == 'text') {
						// result.value.data 
						thisClass.handle_completion();
					} else {
						const selectedImages = await thisClass.choose_image();
					}
				}
			})
		}
		init_wavesurfer__() {
			document.querySelectorAll('.fwp-outfit__player[data-audio]').forEach((el)=>{
				const wavesurfer = WaveSurfer.create({
					container: el,
					waveColor: '#4F4A85',
					progressColor: '#383351',
					url: el.dataset.audio,
				  })
				  
				  wavesurfer.on('interaction', () => {
					wavesurfer.play()
				  })
			});
		}
		init_wavesurfer() {
			document.querySelectorAll('.fwp-outfit__player[data-audio]').forEach((el) => {
			  // Web Audio example
			  const audio = new Audio();
			  audio.controls = true;
			  audio.src = el.dataset.audio;
			  // Create a WaveSurfer instance and pass the media element
				const wavesurfer = WaveSurfer.create({
					container: el,
					media: audio,
					waveColor: 'rgb(200, 0, 200)',
					progressColor: 'rgb(100, 0, 100)',
					interact: false, // Disable user interactions with the waveform
					height: 20, // Set the desired canvas height in pixels
					barWidth: 2,
					barGap: 1,
					barRadius: 2,
				});
				// Optionally, add the audio to the page to see the controls
				el.parentElement.insertBefore(audio, el);
				audio.addEventListener('play', () => {wavesurfer.play();});
				audio.addEventListener('pause', () => {wavesurfer.pause();});
		  
				// Create Web Audio context
				const audioContext = new AudioContext();

				// Define the equalizer bands
				const eqBands = [32, 64, 125, 250, 500, 1000, 2000, 4000, 8000, 16000];

				// Create a biquad filter for each band
				const filters = eqBands.map((band) => {
					const filter = audioContext.createBiquadFilter();
					filter.type = band <= 32 ? 'lowshelf' : band >= 16000 ? 'highshelf' : 'peaking'; // Fixed 'hishelf' to 'highshelf'
					filter.gain.value = Math.random() * 40 - 20;
					filter.Q.value = 1; // resonance
					filter.frequency.value = band; // the cut-off frequency
					return filter;
				});
		  
			  // Connect the audio to the equalizer
			//   audio.addEventListener(
			// 	'canplay',
			// 	() => {
			// 	  // Create a MediaElementSourceNode from the audio element
			// 	  const mediaNode = audioContext.createMediaElementSource(audio);
		  
			// 	  // Connect the filters and media node sequentially
			// 	  const equalizer = filters.reduce((prev, curr) => {
			// 		prev.connect(curr);
			// 		return curr;
			// 	  }, mediaNode);
		  
			// 	  // Connect the filters to the audio output
			// 	  equalizer.connect(audioContext.destination);
			// 	},
			// 	{ once: true }
			// );
		  
			  // Create a vertical slider for each band
			//   const container = document.createElement('p');
			//   filters.forEach((filter) => {
			// 	const slider = document.createElement('input');
			// 	slider.type = 'range';
			// 	slider.orient = 'vertical';
			// 	slider.style.appearance = 'slider-vertical';
			// 	slider.style.width = '8%';
			// 	slider.step = 0.1;
			// 	slider.min = -40;
			// 	slider.max = 40;
			// 	slider.value = filter.gain.value;
			// 	slider.oninput = (e) => (filter.gain.value = e.target.value);
			// 	container.appendChild(slider);
			//   });
			//   el.parentElement.insertBefore(container, el);
			});
		}
		init_tooltips() {
			const thisClass = this;
			document.querySelectorAll('.fwp-outfit__image:not([data-handled-tippy])').forEach((el)=>{
				el.dataset.handledTippy = true;
				tippy(el, {
					allowHTML: true,
					content: '\
					<div class="fwp-image__tippy">\
						<img src="'+el.src+'" alt="" class="fwp-image__tippy__image">\
						<strong class="fwp-image__tippy__price">'+el.dataset.item+' ('+el.dataset.price+')</strong>\
						<span class="fwp-image__tippy__title">'+el.dataset.product+'</span>\
					</div>'
				});
			});
			document.querySelectorAll('.fwppopspopup-open:disabled').forEach((el)=>{
				tippy(el.parentElement, {
					content: thisClass.i18n?.globallydefined??'This product is globally defined and until disabling forceful definition, you can\'t customize this popup.'
				});
			});
			document.querySelectorAll('.tippy-tooltip:not([data-handled-tippy])').forEach((el)=>{
				el.dataset.handledTippy = true;
				tippy(el, {content: el.dataset?.tippyContent??false});
			});
		}
		initRandTeddyName() {
			const thisClass = this;
			const do_repeater = document.querySelector('[name="teddybearsprompts[do_repeater_name]"]');
			if(do_repeater) {
				do_repeater?.addEventListener('click', (event) => {
					event.preventDefault();
					var i = do_repeater.parentElement.parentElement.parentElement.childElementCount;
					var template = document.createElement('tr');
					template.innerHTML = '<th scope="row">#'+i+'</th><td><input id="teddy-name-'+i+'" type="text" name="teddybearsprompts[teddy-name-'+i+']" placeholder="" value=""><label for="teddy-name-'+i+'"></label></td>';
					do_repeater.parentElement.parentElement.parentElement.insertBefore(template, do_repeater.parentElement.parentElement);
				});
				document.querySelectorAll('[id^="teddy-name-"]').forEach((el) => {
					el.nextElementSibling.querySelector('.description').innerHTML = icons.trash;
					el.nextElementSibling.addEventListener('click', (event) => {
						var message = thisClass.i18n?.rusure??'Are you sure?';
						if(confirm(message)) {el.parentElement.parentElement.remove();}
					});
				});
			}
		}
		initRandTeddyBadge() {
			const thisClass = this;
			const do_repeater = document.querySelector('[name="teddybearsprompts[do_repeater_badge]"]');
			if(do_repeater) {
				do_repeater?.addEventListener('click', (event) => {
					event.preventDefault();
					var i = do_repeater.parentElement.parentElement.parentElement.childElementCount;
					['enable', 'label', 'backgound', 'textcolor'].forEach((row) => {
						var template = document.createElement('tr');template.dataset.index = i;
						template.innerHTML = `<th scope="row">#${i} ${
							(row == 'textcolor')?'Text color':row
						}</th><td><input id="teddy-badge-${row}-${i}" type="${
							(row == 'enable')?'checkbox':((row == 'label')?'text':'color')
						}" name="teddybearsprompts[teddy-badge-${row}-${i}]" placeholder="" value=""><label for="teddy-badge-${row}-${i}" data-group="${i}"></label></td>`;
						do_repeater.parentElement.parentElement.parentElement.insertBefore(template, do_repeater.parentElement.parentElement);
					});
					
				});
				document.querySelectorAll('[id^="teddy-badge-"]').forEach((el) => {
					el.nextElementSibling.querySelector('.description').innerHTML = icons.trash;
					el.nextElementSibling.addEventListener('click', (event) => {
						var message = thisClass.i18n?.rusure??'Are you sure?';
						if(confirm(message)) {
							if(el.dataset?.group) {
								['enable', 'label', 'backgound', 'textcolor'].forEach((row) => {
									document.querySelector('label[for^="teddy-badge-' + row + '-' + el.dataset.group + '"]')?.parentElement.parentElement.remove();
								});
							}
						}
					});
				});
			}
		}
		init_tabs() {
			const thisClass = this;var theInterval, selector;
			selector = '.fwp-tabs__navs';
			theInterval = setInterval(() => {
				document.querySelectorAll(selector + ':not([data-handled])').forEach((el, i) => {
					el.dataset.handled = true;
					el.querySelectorAll('.fwp-tabs__nav-item').forEach((tabEl, tabI) => {
						tabEl.addEventListener('click', function(event) {
							if(this.dataset.target) {
								el.querySelector('.active').classList.remove('active');
								this.classList.add('active');
								document.querySelector(this.dataset.target).parentElement.querySelector('.active').classList.remove('active');
								document.querySelector(this.dataset.target).classList.add('active');
							}
						});
					});
				});
			}, 1000);
		}
		fix_product_data_display_issue() {
			document.querySelectorAll('#woocommerce-product-data .hidden').forEach((el) => {
				var blockade = false;
				['lower', 'higher'].forEach((txt) => {
					if(el.id == 'woocommerce-product-data-handle-order-' + txt + '-description') {
						blockade = true;
					}
				});
				if(! blockade) {el.classList.remove('hidden');}
				
			});
			// product-data-wrapper type_box 
		}
		downloadable_attached_pops() {
			const thisClass = this;
			DOWNLOADS.init_events(this);
			document.querySelectorAll('.launch_orderd_arrachments:not([data-handled])').forEach((el) => {
				el.dataset.handled = true;
				el.addEventListener('click',(event) => {
					event.preventDefault();
					DOWNLOADS.init_downloadable_attached_popup(el, thisClass);
				});
			});
		}
	}

	new FWPListivoBackendJS();
})(jQuery);
