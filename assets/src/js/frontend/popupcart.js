const popupCart = {
    basePrice: 0,
    priceSign: '$',
    additionalPrices: [],

    setBasePrice: (price) => {
        popupCart.basePrice = price;
    },
    addAdditionalPrice: (item, price, unique = true) => {
        const existingIndex = popupCart.additionalPrices.findIndex(p => p.item === item);
        if(!unique || existingIndex === -1) {
            popupCart.additionalPrices.push({ item, price });
        } else {
            // Item already exists, update the price
            popupCart.additionalPrices[existingIndex].price = price;
        }
        popupCart.updateTotalPrice();
    },
    wc_addAdditionalPrice: (el, thisClass) => {
        const price = parseFloat(el.dataset?.cost??'0');
        var formdata = new FormData();
        formdata.append('action', 'futurewordpress/project/ajax/update/cart');
        formdata.append('_product', parseInt(el.dataset.product));
        formdata.append('_nonce', thisClass.ajaxNonce);
        formdata.append('_key', thisClass.cartItemKey);
        formdata.append('_price', price);
        formdata.append('_quantity', 1);
        formdata.append('_mode', 'add');
        thisClass.sendToServer(formdata);
    },
    wc_removeAdditionalPrice: (el, thisClass) => {
        const price = parseFloat(el.dataset?.cost??'0');
        var formdata = new FormData();
        formdata.append('action', 'futurewordpress/project/ajax/update/cart');
        formdata.append('_product', parseInt(el.dataset.product));
        formdata.append('_nonce', thisClass.ajaxNonce);
        formdata.append('_key', thisClass.cartItemKey);
        formdata.append('_price', price);
        formdata.append('_mode', 'del');
        formdata.append('_quantity', 1);
        thisClass.sendToServer(formdata);
    },
    removeAdditionalPrice: (itemName, itemPrice = false) => {
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
        const priceAlt = document.querySelector('.calculated-prices .price_amount');
        if(priceAlt) {
            priceAlt.innerHTML = popupCart.priceSign +''+ parseFloat(popupCart.getTotalPrice()).toFixed(2) + popupCart.cartIcon;
        }
    }
};
export default popupCart;