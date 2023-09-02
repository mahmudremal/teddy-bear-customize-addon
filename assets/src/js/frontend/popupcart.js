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