class Ask {
	constructor(thisClass) {
		this.setup_hooks(thisClass);
	}
	setup_hooks(thisClass) {
		this.init_events_4_teddy_info(thisClass);
		this.init_ask_4_teddy_info(thisClass);
	}
	init_events_4_teddy_info(thisClass) {
		document.body.addEventListener('order_item_update_success', (event) => {
			location.reload();
		});
	}
	init_ask_4_teddy_info(thisClass) {
		const askClass = this;
		if(window?.teddyNameRequired) {
			document.querySelectorAll('#actions select[name="wc_order_action"]').forEach((select) => {
				select.addEventListener('change', (event) => {
					if(event.target.value == 'send_birth_certificates') {askClass.askForTeddyInfo(window.teddyNameRequired, thisClass);}
				});
			});
			document.querySelectorAll('#order_status[name="order_status"]').forEach((select) => {
				select.classList.remove('select2-hidden-accessible');
				jQuery(select).on('select2:select', (event) => {
					if(['wc-completed', 'wc-shipped'].includes(event.target.value)) {askClass.askForTeddyInfo(window.teddyNameRequired, thisClass);}
				});
			});
			document.querySelectorAll('.fwp-outfit__info a[data-certificate]').forEach((button) => {
				button.addEventListener('click', (event) => {
					if (window.teddyNameRequired.find(row => row.item_id == button.dataset.certificate)) {
						event.preventDefault();
						askClass.askForTeddyInfo(window.teddyNameRequired, thisClass);
					}
				});
			});
			
		} else {
			// document.querySelectorAll('#teddybear_meta_data > .postbox-header > h2').forEach(boxHeaderH2 => {
			// 	var boxHeader = boxHeaderH2.parentElement;
			// 	var block = document.createElement('div');
			// 	block.appendChild(boxHeaderH2);
			// 	var printout = document.createElement('a');printout.target = '_blank';
			// 	printout.innerHTML = thisClass.i18n?.printoutcerts??'Printout Certificates';
			// 	printout.href = `${location.origin}/certificates/${fwpSiteConfig.config.product_id}/print/`;
			// 	block.appendChild(printout);block.style.display = 'flex';block.style.alignItems = 'center';
			// 	block.style.justifyContent = 'space-between';block.style.minWidth = '90%';
			// 	if (boxHeader.children[0]) {
			// 		boxHeader.insertBefore(block, boxHeader.children[0]);
			// 	} else {
			// 		boxHeader.appendChild(block);
			// 	}
			// });
		}
	}
	askForTeddyInfo(items, thisClass) {
		const askClass = this;
		const updateBtn = document.querySelector('#poststuff #woocommerce-order-actions .inside button[type="submit"]');
		if(updateBtn) {updateBtn.classList.add('disabled');updateBtn.disabled = true;}
		thisClass.Swal.fire({
			title: thisClass.i18n?.info_required??'Information Required',
			// input: 'text',
			// inputAttributes: {autocapitalize: 'off'},
			showCancelButton: true,
			confirmButtonText: thisClass.i18n?.confirm??'Confirm',
			cancelButtonText: thisClass.i18n?.cancel??'Cancel',
			showLoaderOnConfirm: true,
			html: '<div class="askteddyinfo__popup"></div>',
			didOpen: async () => {
				const pop = document.querySelector('.askteddyinfo__popup');
				if(!pop) {return;}
				pop.appendChild(askClass.get_ask_template(items, thisClass));
            },
			// get_ask_template
			preConfirm: () => {
				return askClass.submit_ask_pops(thisClass);
			},
			allowOutsideClick: () => !thisClass.Swal.isLoading()
		}).then((result) => {
			// if (result.isConfirmed) {}
		})
	}
	get_ask_template(items, thisClass) {
		const container = document.createElement('div');
		container.classList.add('askteddyinfo');
		const form = document.createElement('form');
		form.classList.add('askteddyinfo__form');
		[
			{name: 'askedteddyinfo', value: items.map(row => row.item_id).join(',')},
			{name: 'order_id', value: items.map(row => row.order_id)?.[0]??false}
		].forEach((field) => {
			var hidden = document.createElement('input');
			hidden.type = 'hidden';hidden.name = field?.name;
			hidden.setAttribute('value', field?.value);
			form.appendChild(hidden);
		});

		items.forEach((item) => {
			const body = document.createElement('div');
			body.classList.add('askteddyinfo__body');
			const fields = [
				{label: thisClass.i18n?.teddy_name??'Teddy name', type: 'text', name: 'item-' + item.item_id + '[teddy_name]', default: item.info?.teddy_name??''},
				{label: thisClass.i18n?.teddybirth??'Birth date', type: 'date', name: 'item-' + item.item_id + '[teddy_birth]', default: item.info?.teddy_birth??''},
				{label: thisClass.i18n?.teddy_reciever??"Reciever's Name", type: 'text', name: 'item-' + item.item_id + '[teddy_reciever]', default: item.info?.teddy_reciever??''},
				{label: thisClass.i18n?.sendersname??'Created with love by', type: 'text', name: 'item-' + item.item_id + '[teddy_sender]', default: item.info?.teddy_sender??''},
				{label: thisClass.i18n?.printoutcerts??'Attached Printout', type: 'checkbox', name: 'item-' + item.item_id + '[teddy_print]', default: item.info?.teddy_print??false},
			];
			if(item?.prod_name != '') {
				var heading = document.createElement('h3');
				heading.classList.add('product_name');
				heading.innerHTML = '#' + item.prod_name;
				body.appendChild(heading);
			}
			fields.forEach((row, rowIndex) => {
				var field = document.createElement('div');
				var label = document.createElement('label');label.innerHTML = row.label;
				label.for = 'item-' + item.item_id + '-' + rowIndex;
				label.classList.add('form-label', 'form-label-' + row.type);
				var input = document.createElement('input');input.type = row.type;
				input.name = row.name;input.setAttribute('value', row.default);
				input.classList.add('form-control');input.id = label.for;
				// 
				if (row.type == 'checkbox' && row?.teddy_print) {input.checked = true;}
				// 
				label.appendChild(input);
				if (rowIndex == 0 && window?.teddySuggestedNames) {
					// <span class="dashicons dashicons-times" aria-hidden="true"></span>
					var reChoice  = document.createElement('span');
					reChoice.classList.add('dashicons', 'dashicons-controls-repeat');
					reChoice.setAttribute('aria-hidden', 'true');
					reChoice.addEventListener('click', (event) => {
						event.preventDefault();event.stopPropagation();
						input.value = teddySuggestedNames[Math.floor(Math.random() * teddySuggestedNames.length)];
					});
					label.style.position = 'relative';
					reChoice.style.position = 'absolute';
					reChoice.style.cursor = 'pointer';
					reChoice.style.right = '10px';
					reChoice.style.bottom = '7px';
					label.appendChild(reChoice);
				}
				// 
				field.appendChild(label);body.appendChild(field);
			});
			form.appendChild(body);
		});
		container.appendChild(form);
		return container;
	}
	submit_ask_pops(thisClass) {
		const askClass = this;
		const form = document.querySelector('.askteddyinfo__form');
		const action = 'teddybear/project/ajax/update/orderitem';
		if(form) {
			var formdata = new FormData(form);
			formdata.append('action', 'teddybear/project/ajax/update/orderitem');
			formdata.append('_nonce', thisClass.ajaxNonce);
			thisClass.sendToServer(formdata);

			return new Promise((resolve, reject) => {
				let intval = 0;
				// Simulating an asynchronous task
				setInterval(() => {
					intval++;
					if (intval >= 50) {
						reject(new Error('Something wrong!'));
					}
					if (thisClass.lastJson?.hooks) {
						['success', 'failed'].forEach((hook, index) => {
							if (thisClass.lastJson.hooks.includes('order_item_update_' + hook)) {
								switch (index) {
									case 0:
										resolve();
										break;
									default:
										reject(new Error("Update operation failed"));
										break;
								}
							}
						});
					}
				}, 1000);
			});
		} else {
			return false;
		}
	}
}

export default Ask;