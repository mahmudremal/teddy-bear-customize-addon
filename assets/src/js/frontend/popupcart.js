import { send2Server } from "../modules/send2server";

class popupCart {
    constructor() {
        this.basePrice = 0;
        this.priceSign = '$';
        this.additionalPrices = [];
        this.ajaxUrl = false;
    }
    setBasePrice(price) {
        this.basePrice = price;
    }
    addAdditionalPrice(item, price, unique = true, product = false) {
        if(['tochoose'].includes(item)) {return;}
        const existingIndex = this.additionalPrices.findIndex(p => (product && product !== '')?(p.product === product):(p.item === item));
        if(!unique || existingIndex === -1) {
            this.additionalPrices.push({ item, price, product });
        } else {
            // Item already exists, update the price
            this.additionalPrices[existingIndex].price = price;
        }
        this.updateTotalPrice();
    }
    wc_addAdditionalPrice(element, thisClass, _remove = false) {
        const price = parseFloat(element.dataset?.cost??'0');
        var cartItemKey = (_remove)?element.dataset?.cartItemKey??'':thisClass.cartItemKey;
        element.setAttribute('disabled', true);
        var formdata = new FormData();
        formdata.append('action', 'teddybear/project/ajax/update/cart');
        formdata.append('_product', parseInt(element.dataset.product));
        formdata.append('_mode', (_remove)?'del':'add');
        formdata.append('_nonce', thisClass.ajaxNonce);
        formdata.append('_key', cartItemKey);
        formdata.append('_price', price);
        formdata.append('_quantity', 1);
        // thisClass.sendToServer(formdata);
        send2Server(thisClass.ajaxUrl, formdata).then(json => {
            console.log(json);
            element.removeAttribute('disabled');
            element.dataset.cartItemKey = json.cartItemKey;
            if (json?.message) {
                thisClass.toastify({text: json?.message,className: "info", duration: 3000, stopOnFocus: true, style: {background: (json.success)?'linear-gradient(to right, rgb(255 197 47), rgb(251 229 174))':'linear-gradient(to right, rgb(222 66 75), rgb(249 144 150))'}}).showToast();
            }
        }).catch(error => {
            console.log(error?.message??error);
            element.removeAttribute('disabled');
        });
    }
    wc_removeAdditionalPrice(el, thisClass) {
        this.wc_addAdditionalPrice(el, thisClass, true);
    }
    removeAdditionalPrice(itemName, itemPrice = false, product = false) {
        const index = this.additionalPrices.findIndex(item => item.item === itemName);
        if (index !== -1) {
            if(itemPrice !== false && ((this.additionalPrices[index]?.price??0) - itemPrice) > 0) {
                this.additionalPrices[index].price -= itemPrice;
            } else {
                this.additionalPrices.splice(index, 1);
            }
            this.updateTotalPrice();
        }
    }
    getTotalPrice() {
        const additionalPriceTotal = this.additionalPrices.reduce((total, item) => total + item.price, 0);
        let calculated = (this.basePrice + additionalPriceTotal);
        calculated += this.prompts.get_data().map(step => {
            if (['radio', 'checkbox', 'select'].includes(step.type)) {
                step = step.options.filter(opt => opt?.selected).reduce((total, item) => total + parseFloat(item.cost), 0);
            } else if (step.type == 'outfit') {
                step = Object.values(step.groups).map(row => 
                    row.options.filter(opt => opt?.selected).reduce((total, item) => total + parseFloat(item.cost), 0)
                ).reduce((total, item) => total + parseFloat(item), 0);
            } else if (step.type == 'voice') {
                step = (step?.attaced && !(step.attaced?.skip))?parseFloat(step.cost):0;
            } else if (step?.cost) {
                step = parseFloat(step.cost);
            } else {
                return 0;
            }
            return step;
        }).reduce((total, item) => total + parseFloat(item), 0);
        return (calculated !== 'undefined') ? calculated : 0;
    }
    updateTotalPrice() {
        document.querySelectorAll('.calculated-prices .price_amount').forEach(async (priceAlt) => {
            priceAlt.classList.add('animate__shakeX', 'animate__animated');
            var local = this.config.local.replace('_', '-');
            var priceAmount = parseFloat(this.getTotalPrice()).toFixed(2);

            if (local != 'en-US' && Intl && typeof Intl?.NumberFormat === 'function') {
                priceAmount = new Intl.NumberFormat(local).format(priceAmount);
            }

            // if (this?.ajaxUrl) {
            //     var res = await fetch(this.ajaxUrl + '?action=teddybear/project/ajax/i18n/number&decimal=2&number=' + priceAmount).then(res => res.json()).then(json => {
            //         priceAmount = json?.number??priceAmount;
            //     }).catch(error => console.error(error));
            // }

            priceAlt.innerHTML = this.priceSign +''+ priceAmount + this.cartIcon;
            setTimeout(() => {priceAlt.classList.remove('animate__shakeX', 'animate__animated');}, 1000);
        });
    }
}
export default popupCart;