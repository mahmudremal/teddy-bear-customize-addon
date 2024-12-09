import React, { useState, useEffect } from 'react';
import Slider from 'react-slick';
import PriceBlock from './blocks/priceBlock';
import PreviewCanvas from './blocks/PreviewCanvas';
import { X, Plus, Minus } from 'lucide-react';
import axios from 'axios';

const Confirmation = ({ data, closePopup, selectedType, canvasImages, setCanvasBlob, activeTab, product }) => {
    const [cartItems, setCartItems] = useState({});

    const settings = {
        dots: false,
        infinite: true,
        speed: 500,
        swipeToSlide: true,
        slidesToShow: 4,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 3,
                    swipeToSlide: true
                }
            }
        ]
    };

    const handleProductClick = async (product) => {
        const price = parseFloat(product?.cost ?? '0');
        const isInCart = cartItems[product.ID];
        const mode = isInCart ? 'del' : 'add';

        const formdata = new FormData();
        formdata.append('action', 'teddybear/project/ajax/update/cart');
        formdata.append('_product', parseInt(product.ID));
        formdata.append('_mode', mode);
        formdata.append('_nonce', fwpSiteConfig.ajax_nonce);
        formdata.append('_key', isInCart ? cartItems[product.ID] : '');
        formdata.append('_price', price);
        formdata.append('_quantity', 1);

        try {
            const response = await axios.post(fwpSiteConfig.ajaxUrl, formdata);
            if (response.data.success) {
                if (mode === 'add') {
                    setCartItems(prev => ({
                        ...prev,
                        [product.ID]: response.data.data.cartItemKey
                    }));
                } else {
                    setCartItems(prev => {
                        const newItems = { ...prev };
                        delete newItems[product.ID];
                        return newItems;
                    });
                }
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    return (
        <div className="tb_inset-0 tb_flex tb_items-center tb_justify-center">
            <div className="tb_w-full tb_max-w-3xl tb_mx-auto tb_p-6">
                {/* Close button */}
                <button onClick={closePopup} className="tb_absolute tb_top-[5px] tb_right-[5px] tb_text-gray-400 hover:tb_text-gray-600 hover:tb_bg-none">
                    <X size={24} />
                </button>

                {/* Title */}
                <h2 className="tb_text-2xl tb_font-bold tb_text-center tb_mt-2 tb_mb-6">{data.title}</h2>

                {/* Preview Canvas */}
                {['standing', 'sitting'].includes(selectedType) && (
                    <div className="tb_mb-8 tb_w-[200px] tb_m-auto tb_block">
                        <PreviewCanvas 
                            images={canvasImages} 
                            baseImage={product.custom_data._canvas} 
                            setCanvasBlob={setCanvasBlob} 
                            activeTab={activeTab} 
                        />
                    </div>
                )}

                {/* You may also like section */}
                <h3 className="tb_text-2xl tb_font-semibold tb_mb-4 tb_text-center">You may also like</h3>
                
                {/* Product Carousel */}
                <div className="tb_mb-8">
                    <Slider {...settings}>
                        {data.suggestion.map((product) => (
                            <div key={product.ID}>
                                <div className="tb_p-1" title={product.title}>
                                    <div className="tb_relative tb_box-border tb_bg-[#eeeeeeba] tb_rounded-[7px] tb_border-[#c4c4c4] tb_border tb_p-1 hover:tb_cursor-pointer [&:hover::before]:tb_content-[''] [&:hover::before]:tb_absolute [&:hover::before]:tb_inset-0 [&:hover::before]:tb_bg-[rgba(81,81,81,0.22)] [&:hover::before]:tb_rounded-lg" onClick={() => handleProductClick(product)} >
                                        <div className="tb_block tb_text-center tb_mb-2" dangerouslySetInnerHTML={{__html: product.thumbnail}} />
                                        <div className="tb_block tb_text-center" dangerouslySetInnerHTML={{__html: product.priceHtml}} />
                                        <div className="tb_absolute tb_top-1/2 tb_left-1/2 tb_-translate-x-1/2 tb_-translate-y-1/2 tb_z-[1] tb_opacity-0 hover:tb_opacity-100">
                                            {cartItems[product.ID] ? 
                                                <Minus className="tb_text-[#ffcf02] tb_drop-shadow-[2px_2px_2px_#5f5f5f]" size={40} /> : 
                                                <Plus className="tb_text-[#ffcf02] tb_drop-shadow-[2px_2px_2px_#5f5f5f]" size={40} />
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </Slider>
                </div>

                {/* Action Buttons */}
                <div className="tb_flex tb_justify-center tb_gap-2">
                    <button onClick={e => closePopup(false)} className="tb_px-4 tb_py-2 tb_rounded-lg tb_bg-primary tb_text-white tb_transition-colors tb_text-nowrap">
                        Buy more plushies
                    </button>
                    
                    <a href={data.accessoriesUrl} className="tb_px-4 tb_py-2 tb_rounded-lg tb_bg-primary tb_text-white hover:tb_text-white tb_transition-colors tb_text-nowrap">
                        Add accessories
                    </a>

                    <a href={data.checkoutUrl} className="tb_px-4 tb_py-2 tb_rounded-lg tb_bg-secondary tb_text-primary tb_transition-colors tb_text-nowrap">
                        Checkout
                    </a>
                </div>
            </div>
            <style jsx>{`.slick-next:before, .slick-prev:before {color: #e63f51;} del {display: none;}`}</style>
        </div>
    );
};

export default Confirmation;
