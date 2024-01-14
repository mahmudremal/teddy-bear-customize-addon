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
			document.querySelectorAll('.fwp-outfit__certificate .btn[data-certificate]').forEach((button) => {
				button.addEventListener('click', (event) => {
					if (window.teddyNameRequired.find(row => row.item_id == button.dataset.certificate)) {
						event.preventDefault();
						askClass.askForTeddyInfo(window.teddyNameRequired, thisClass);
					}
				});
			});
			
		}
	}
	askForTeddyInfo(items, thisClass) {
		const askClass = this;
		const updateBtn = document.querySelector('#poststuff #woocommerce-order-actions .inside button[type="submit"]');
		if(updateBtn) {updateBtn.classList.add('disabled');updateBtn.disabled = true;}
		thisClass.Swal.fire({
			title: 'Information Required',
			// input: 'text',
			// inputAttributes: {autocapitalize: 'off'},
			showCancelButton: true,
			confirmButtonText: 'Confirm',
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
				{label: 'Teddy name', type: 'text', name: 'item-' + item.item_id + '[teddyname]', default: item.info?.teddyname??''},
				{label: 'Birth date', type: 'date', name: 'item-' + item.item_id + '[teddybirth]', default: item.info?.teddybirth??''},
				{label: 'Reciever\'s Name', type: 'text', name: 'item-' + item.item_id + '[recievername]', default: item.info?.recievername??''},
				{label: 'Created with love by', type: 'text', name: 'item-' + item.item_id + '[createdby]', default: item.info?.createdby??''},
			];

			if(item?.prod_name != '') {
				var heading = document.createElement('h3');
				heading.classList.add('product_name');
				heading.innerHTML = '#' + item.prod_name;
				body.appendChild(heading);
			}
			fields.forEach((row, rowIndex) => {
				var field = document.createElement('div');
				var label = document.createElement('label');
				label.for = 'item-' + item.item_id + '-' + row.rowIndex;
				label.classList.add('form-label');label.innerHTML = row.label;
				var input = document.createElement('input');input.type = row.type;
				input.name = row.name;input.setAttribute('value', row.default);
				input.classList.add('form-control');input.id = label.for;
				label.appendChild(input);field.appendChild(label);body.appendChild(field);
			});
			form.appendChild(body);
		});
		container.appendChild(form);
		return container;
	}
	submit_ask_pops(thisClass) {
		const askClass = this;
		const form = document.querySelector('.askteddyinfo__form');
		const action = 'futurewordpress/project/ajax/update/orderitem';
		if(form) {
			var formdata = new FormData(form);
			formdata.append('action', 'futurewordpress/project/ajax/update/orderitem');
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