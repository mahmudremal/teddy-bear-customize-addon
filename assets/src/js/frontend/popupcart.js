const popupCart = {
    basePrice: 0,
    priceSign: '$',
    additionalPrices: [],
    ajaxUrl: false,

    setBasePrice: (price) => {
        popupCart.basePrice = price;
    },
    addAdditionalPrice: (item, price, unique = true, product = false) => {
        if(['tochoose'].includes(item)) {return;}
        const existingIndex = popupCart.additionalPrices.findIndex(p => (product && product !== '')?(p.product === product):(p.item === item));
        if(!unique || existingIndex === -1) {
            popupCart.additionalPrices.push({ item, price, product });
        } else {
            // Item already exists, update the price
            popupCart.additionalPrices[existingIndex].price = price;
        }
        popupCart.updateTotalPrice();
    },
    wc_addAdditionalPrice: (el, thisClass, _remove = false) => {
        const price = parseFloat(el.dataset?.cost??'0');
        var formdata = new FormData();
        formdata.append('action', 'futurewordpress/project/ajax/update/cart');
        formdata.append('_product', parseInt(el.dataset.product));
        formdata.append('_mode', (_remove)?'del':'add');
        formdata.append('_nonce', thisClass.ajaxNonce);
        formdata.append('_key', thisClass.cartItemKey);
        formdata.append('_price', price);
        formdata.append('_quantity', 1);
        thisClass.sendToServer(formdata);
    },
    wc_removeAdditionalPrice: (el, thisClass) => {
        popupCart.wc_addAdditionalPrice(el, thisClass, true);
    },
    removeAdditionalPrice: (itemName, itemPrice = false, product = false) => {
        const index = popupCart.additionalPrices.findIndex(item => item.item === itemName);
        if (index !== -1) {
            if(itemPrice !== false && ((popupCart.additionalPrices[index]?.price??0) - itemPrice) > 0) {
                popupCart.additionalPrices[index].price -= itemPrice;
            } else {
                popupCart.additionalPrices.splice(index, 1);
            }
            popupCart.updateTotalPrice();
        }
    },
    getTotalPrice: () => {
        const additionalPriceTotal = popupCart.additionalPrices.reduce((total, item) => total + item.price, 0);
        return (popupCart.basePrice + additionalPriceTotal);
    },
    updateTotalPrice: () => {
        document.querySelectorAll('.calculated-prices .price_amount').forEach(async (priceAlt) => {
            priceAlt.classList.add('animate__shakeX', 'animate__animated');
            var local = popupCart.local.replace('_', '-');
            var priceAmount = parseFloat(popupCart.getTotalPrice()).toFixed(2);

            if (local != 'en-US' && Intl && typeof Intl?.NumberFormat === 'function') {
                priceAmount = new Intl.NumberFormat(local).format(priceAmount);
            }

            // if (popupCart?.ajaxUrl) {
            //     var res = await fetch(popupCart.ajaxUrl + '?action=futurewordpress/project/ajax/i18n/number&decimal=2&number=' + priceAmount).then(res => res.json()).then(json => {
            //         priceAmount = json?.number??priceAmount;
            //     }).catch(error => console.error(error));
            // }

            priceAlt.innerHTML = popupCart.priceSign +''+ priceAmount + popupCart.cartIcon;
            setTimeout(() => {priceAlt.classList.remove('animate__shakeX', 'animate__animated');}, 1000);
        });
    },
    
};
export default popupCart;