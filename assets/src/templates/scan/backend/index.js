
import Swal from "sweetalert2";
import PROMPTS from "./prompts";
import Toastify from 'toastify-js';
import Sortable from 'sortablejs';
import mediaImages from "./media";
import WaveSurfer from 'wavesurfer.js';

( function ( $ ) {
	class FWPListivoBackendJS {
		constructor() {
			this.ajaxUrl = fwpSiteConfig?.ajaxUrl??'';
			this.ajaxNonce = fwpSiteConfig?.ajax_nonce??'';
			var i18n = fwpSiteConfig?.i18n??{};this.cssImported = false;
			this.config = fwpSiteConfig?.config??{};
			this.WaveSurfer = WaveSurfer;
			this.i18n = {
				submit:			'Submit',
				...i18n
			}
			this.setup_hooks();
		}
		setup_hooks() {
			const thisClass = this;
			window.thisClass = this;
			window.mediaImages = mediaImages;
			this.prompts = PROMPTS;
			PROMPTS.i18n = this.i18n;
			this.Sortable = Sortable;
			this.Swal = Swal;
			this.init_toast();
			this.init_events();
			this.init_button();
			this.init_wavesurfer();
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
			const thisClass = this;var html;
			document.body.addEventListener('gotproductpopupresult', async (event) => {
				thisClass.prompts.lastJson = thisClass.lastJson;
				html = document.createElement('div');
				html.appendChild(await PROMPTS.get_template(thisClass));
				// && json.header.product_photo
				if(thisClass.Swal.isVisible()) {
					thisClass.Swal.update({
						html: html.innerHTML
					});
					setTimeout(() => {
						if(thisClass.lastJson.product!=''&&thisClass.lastJson.product.length>=1) {
							thisClass.prompts.do_fetch(thisClass);
						}
						thisClass.prompts.init_events(thisClass);
					}, 300);
				}
			});
			document.body.addEventListener('product_updated', (event) => {
				var button = document.querySelector('.save-this-popup');
				if(button) {button.removeAttribute('disabled');}
			});
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
							// thisClass.toast.fire({icon: ( json.success ) ? 'success' : 'error', title: message})
							thisClass.toastify({text: message,className: "info", duration: 3000, stopOnFocus: true, style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
						}
						if( json.data.hooks ) {
							json.data.hooks.forEach(( hook ) => {
								document.body.dispatchEvent( new Event( hook ) );
							});
						}
					}
				},
				error: function( err ) {
					// thisClass.notify.fire({icon: 'warning',title: err.responseText})
					thisClass.toastify({text: err.responseText,className: "info",style: {background: "linear-gradient(to right, #00b09b, #96c93d)"}}).showToast();
					console.log( err.responseText );
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
					thisClass.init_popup();
				});
			});
		}
		init_popup() {
			const thisClass = this;var html;
			PROMPTS.lastfieldID = 0;
			thisClass.prompts.lastJson = false;
			html = document.createElement('div');
			html.appendChild(PROMPTS.get_template(thisClass));
			Swal.fire({
				title: false, // thisClass.i18n?.generateaicontent??'Generate AI content',
				width: 600,
				// padding: '3em',
				// color: '#716add',
				// background: 'url(https://png.pngtree.com/thumb_back/fh260/background/20190221/ourmid/pngtree-ai-artificial-intelligence-technology-concise-image_19646.jpg) rgb(255, 255, 255) center center no-repeat',
				showConfirmButton: false,
				showCancelButton: false,
				showCloseButton: true,
				// confirmButtonText: 'Generate',
				cancelButtonText: 'Close',
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				customClass: {
					popup: 'fwp-swal2-popup'
				},
				// focusConfirm: true,
				// reverseButtons: true,
				// backdrop: `rgba(0,0,123,0.4) url("https://sweetalert2.github.io/images/nyan-cat.gif") left top no-repeat`,
				backdrop: `rgba(0,0,123,0.4)`,

				showLoaderOnConfirm: true,
				allowOutsideClick: false, // () => !Swal.isLoading(),
				
				html: html.innerHTML,
				// footer: '<a href="">Why do I have this issue?</a>',
				didOpen: async () => {
					var formdata = new FormData();
					formdata.append('action', 'futurewordpress/project/ajax/edit/product');
					formdata.append('product_id', thisClass.config?.product_id??'');
					formdata.append('_nonce', thisClass.ajaxNonce);

					thisClass.sendToServer(formdata);
					thisClass.prompts.currentFieldID = 0;
					thisClass.prompts.init_prompts(thisClass);
				},
				preConfirm: () => confirm('Are you sure?'), // async (login) => {return false;}
				preDeny: () => confirm('Are you sure?'), // async (login) => {return false;}
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
		}
		init_wavesurfer() {
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
	}

	new FWPListivoBackendJS();
} )( jQuery );
