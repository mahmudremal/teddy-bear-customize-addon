import axios from 'axios';
import { useEffect, useState, useRef } from 'react';
import PreviewCanvas from './blocks/PreviewCanvas';
import Voice from './blocks/voice';
import Outfit from './blocks/outfit';
import Radio from './blocks/radio';
import Info from './blocks/info';
import { Check } from 'lucide-react';
import Loading from './blocks/Loading';
import PriceBlock from './blocks/priceBlock';
import Checkbox from './blocks/checkbox';
import Input from './blocks/input';
import Confirmation from './Confirmation';
const ProductPage = ({ product, updateProductData, closePopup, setAllowClose }) => {
    if (! product || ! product?.custom_fields ) {
        return (
            <div className="tb_App">
              <div className="tb_bg-white tb_p-8 tb_rounded-lg tb_shadow-lg tb_max-w-full md:tb_w-[450px] md:tb_min-w-4xl tb_mx-auto">
                <div className="tb_text-center">
                  <svg className="tb_mx-auto tb_h-12 tb_w-12 tb_text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <h3 className="tb_mt-2 tb_text-lg tb_font-medium tb_text-gray-900">{__('somethingwentwrong', 'Product Not Found')}</h3>
                  {/* <p className="tb_mt-1 tb_text-sm tb_text-gray-500">{error}</p> */}
                </div>
              </div>
            </div>
        );
    }
    
    const iFRows = product.custom_fields[product.custom_data.product_type].map(f => {
        const nf = { ...f };
        if (f.options) {
            nf.options = [];
        }
        if (f.groups) {
            nf.groups = f.groups.map(g => ({
                ...g,
                options: []
            }));
        }
        return nf;
    });
    
    const [canvasBlob, setCanvasBlob] = useState(null);
    // const [objRows, setObjRows] = useState(iFRows);
    const objRows = useRef(iFRows);
    const [activeTab, setActiveTab] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const [currentStep, setCurrentStep] = useState(0);
    const [error, setError] = useState(null);
    const [selectedOutfit, setSelectedOutfit] = useState(null);
    const [imgLayers, setImgLayers] = useState({});
    const [canvasImages, setCanvasImages] = useState([]);
    const [selectedType, setSelectedType] = useState(null);
    const [discountTotal, setDiscountTotal] = useState(0);
    const [inTotal, setInTotal] = useState(0);
    const [add2CartLoading, setAdd2CartLoading] = useState(false);
    const blobFiles = useRef(null);
    const [visitedTabs, setVisitedTabs] = useState(new Set());
    const [confirmation, setConfirmation] = useState(null);
    const [isSingleTab, setIsSingleTab] = useState(
        product.custom_fields[product.custom_data.product_type]?.length === 1
    );

    const setBlobFiles = (data) => {
        // console.log('Adding blob file...')
        blobFiles.current = data;
    }

    useEffect(() => {
        setSelectedType(product.custom_data.product_type);
        setIsSingleTab(product.custom_fields[product.custom_data.product_type]?.length === 1);
        setCanvasImages([]);
        setIsLoading(false);
    }, [product]);

    const updateCanvasImages = (images) => {
        setCanvasImages(images);
    };

    const handleSelectProductType = (type) => {
        setSelectedType(type);
        setIsSingleTab(product.custom_fields[type]?.length === 1);
        setActiveTab(0);
        setVisitedTabs(prev => new Set(prev).add(0));
        setImgLayers({});
    };

    const handleTabClick = (tabIndex) => {
        if (activeTab === tabIndex) {
            setActiveTab(null);
        } else {
            setActiveTab(tabIndex);
            setVisitedTabs(prev => new Set(prev).add(tabIndex));
            setCurrentStep(tabIndex);
        }
    };

    const handleNextStep = () => {
        setActiveTab(null);
        // if (currentStep < (product.custom_fields[selectedType]?.length || 0) - 1) {
        //     setCurrentStep(currentStep + 1);
        //     setActiveTab(currentStep + 1);
        // }
    };

    const handleDone = () => {
        setActiveTab(null);
    };

    const addToCart = async () => {
        setAdd2CartLoading(true);
        const formData = new FormData();
        formData.append('action', 'teddy/ajax/cart/add');
        formData.append('_nonce', fwpSiteConfig.ajax_nonce);
        formData.append('product_id', product.id);
        formData.append('quantity', 1);

        formData.append('dataset', JSON.stringify(objRows.current));

        if (blobFiles.current) {
            formData.append('_blobs', blobFiles.current, blobFiles.current?.name??'voice.mp3');
        }
        
        formData.append('_canvas', canvasBlob);
        
        try {
            const response = await axios.post(fwpSiteConfig.ajaxUrl, formData, {
                withCredentials: true
            });
            if (response.data.success) {
                const data = response.data.data;
                if (data?.confirmation && data.confirmation?.title) {
                    setConfirmation(data.confirmation);
                    setAllowClose(true);
                } else {
                    setError(data.message);
                }
            } else {
                throw new Error(response.data?.data);
            }
        } catch (error) {
            console.error('Error:', error);
            setError(error.message || 'Something went wrong');
        } finally {
            setAdd2CartLoading(false);
        }
    };

    const updateObjRows = (field, selectedData) => {
        const existingRowIndex = objRows.current.findIndex(row => row.id === field.id);
        const newRow = { ...field, ...selectedData };
        // console.log('newRow', newRow, existingRowIndex, objRows[existingRowIndex]);
        const newObj = existingRowIndex !== -1 ? objRows.current.map((row, index) => index === existingRowIndex ? newRow : row) : [...objRows, newRow];
        // setObjRows(newObj);
        objRows.current = newObj;
        // console.log('objRows', objRows);
        setTotalCost(newObj);
    };

    const handleOptionChange = (e, option, currentField, groupid = false) => {
        // console.log('handleOptionChange', e, option, currentField);
        const selectedInput = e.target;
        if (selectedInput) {
            let imageChanged = false;
            let newImgLayers = { ...imgLayers };

            if (currentField?.groups) {
                currentField.groups.forEach(group => {
                    if (group.type === 'radio' || group.type === 'select') {
                        newImgLayers[group.title] = newImgLayers[group.title] || [];
                        if (option?.imageUrl && option.imageUrl !== '') {
                            if (selectedInput.type === 'radio') {
                                newImgLayers[group.title] = [];
                            }
                            if (selectedInput.checked) {
                                newImgLayers[group.title].push(option.imageUrl);
                            } else {
                                newImgLayers[group.title] = newImgLayers[group.title].filter(img => img !== option.imageUrl);
                            }
                            imageChanged = true;
                        }
                    } else if (group.type === 'checkbox') {
                        newImgLayers[group.title] = newImgLayers[group.title] || [];
                        if (selectedInput.checked) {
                            newImgLayers[group.title].push(option.imageUrl);
                        } else {
                            newImgLayers[group.title] = newImgLayers[group.title].filter(img => img !== option.imageUrl);
                        }
                        imageChanged = true;
                    }
                });
            } else if (currentField?.options) {
                newImgLayers[option.id] = newImgLayers[option.id] || [];
                if (currentField.type === 'radio') {
                    if (option?.thumbUrl && option?.imageUrl) {
                        newImgLayers[option.id] = [];
                        newImgLayers[option.id].push(option.imageUrl);
                        imageChanged = true;
                    }
                }
            }

            if (imageChanged) {
                setImgLayers(newImgLayers);
                updateCanvasImages(Object.values(newImgLayers).flat());
            }

            switch (currentField.type) {
                case 'radio':
                    updateObjRows(currentField, {
                        options: [option]
                    });
                    break;
                    
                case 'checkbox':
                    // updateObjRows(currentField, {
                    //     options: selectedOptions
                    // });
                    break;
                    
                case 'outfit':
                    const group = currentField.groups.find(g => g.id == groupid);
                    if (group) {
                        // console.log(group)
                        const updatedGroups = objRows.current.find(f => f.id == currentField.id).groups.map(g => {
                            if (g.id == groupid) {
                                // console.log('option found', group.options.filter(o => o.id === option.id))
                                g.options = group.options.filter(o => o.id === option.id)
                            }
                            return g;
                        });
                        updateObjRows(currentField, {
                            groups: updatedGroups
                        });
                        // console.log('updatedBlock', updatedGroups.map(g => g.options));
                    }
                    break;
            }
        }
    };
    // 
    const getTotalCost = (obj = false) => {
        let totalCostEstimate = 0;
        if (! obj) {obj = objRows;}
        // console.log('getTotalCost', obj);
        obj.forEach(field => {
            switch (field.type) {
                case 'radio':
                case 'select':
                case 'checkbox':
                    totalCostEstimate += field.options.reduce((acc, opt) => acc + (parseFloat(opt.cost) || 0), 0);
                    break;
                case 'outfit':
                    totalCostEstimate += field.groups.reduce((acc, group) => acc + group.options.reduce((acc, opt) => acc + (parseFloat(opt.cost) || 0), 0), 0);
                    break;
                case 'voice':
                    totalCostEstimate += (field?.attached?.later || field?.attached?.blob) ? (parseFloat(field?.cost) || 0) : 0;
                    break;
                default:
                    // console.log('default', field);
                    break;
            }
        });
        // console.log('totalCostEstimate', totalCostEstimate, objRows);
        return totalCostEstimate;
    };
    const setTotalCost = (obj = false) => setInTotal(getTotalCost(obj));

    const currentFields = product.custom_fields?.[selectedType] || [];

    const combinedCart = {
        discountTotal, inTotal, setDiscountTotal, setInTotal, setBlobFiles, getTotalCost, setTotalCost
    };

    return (
        <div className="tb_mx-auto tb_h-full tb_p-0 tb_select-none">
            <link rel="stylesheet" type="text/css" charSet="UTF-8" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" />
            <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" />
        
            <div className={`tb_h-full ${confirmation && 'tb_hidden'}`}>
                <div className="tb_flex tb_justify-between tb_items-center tb_w-full tb_flex-nowrap tb_h-[60px] tb_overflow-hidden tb_mx-auto tb_px-[15px] tb_py-[10px] tb_border-b tb_border-[#eee] tb_box-border height-500-640:tb_h-12">
                    <div className="tb_w-1/3 tb_flex tb_justify-start">
                        <button 
                            onClick={closePopup}
                            className="tb_text-gray-500 tb_hover:text-gray-700"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="tb_h-6 tb_w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div className="tb_w-1/3 tb_flex tb_justify-center">
                        <img 
                            src={ fwpSiteConfig.siteLogo }
                            alt="DubiDo Factory Logo"
                            className="!tb_h-[40px] tb_w-auto"
                        />
                    </div>
                    <div className="tb_w-1/3 tb_flex tb_justify-end">
                        <div className="tb_text-sm tb_font-semibold price_amount tb_rounded tb_px-6 tb_py-2 tb_text-black tb_bg-primary-100">
                            <PriceBlock price_html={product.priceHtml} inTotal={inTotal} discountTotal={discountTotal} />
                        </div>
                    </div>
                </div>

                { isLoading ? ( <div className="tb_flex tb_justify-center tb_items-center tb_h-auto"><Loading /> </div> ) : (
                    <div className="tb_relative tb_min-h-96 tb_flex tb_flex-col tb_justify-between tb_h-[calc(100%-60px)]">
                        {!['standing', 'sitting'].includes(selectedType) && (
                            <div className="tb_flex tb_justify-between tb_mb-8">
                                <div
                                    className="tb_cursor-pointer tb_p-4 tb_border-2 tb_border-gray-300 tb_rounded-md tb_hover:bg-gray-100"
                                    onClick={() => handleSelectProductType('standing')}
                                >
                                    <img
                                        src={product.positions.standing}
                                        alt={__('standingplushies', 'Standing')}
                                        className="tb_w-full tb_h-auto tb_rounded-md"
                                    />
                                    <p className="tb_text-center tb_mt-2 tb_font-semibold">{__('standingplushies', 'Standing')}</p>
                                </div>
                                <div
                                    className="tb_cursor-pointer tb_p-4 tb_border-2 tb_border-gray-300 tb_rounded-md tb_hover:bg-gray-100"
                                    onClick={() => handleSelectProductType('sitting')}
                                >
                                    <img
                                        src={product.positions.sitting}
                                        alt={__('sittingplushies', 'Sitting')}
                                        className="tb_w-full tb_h-auto tb_rounded-md"
                                    />
                                    <p className="tb_text-center tb_mt-2 tb_font-semibold">{__('sittingplushies', 'Sitting')}</p>
                                </div>
                            </div>
                        )}

                        {['standing', 'sitting'].includes(selectedType) && (
                            <div className={ `tb_m-auto tb_mb-4 tb_h-auto ${activeTab === null ? 'height-500-640:tb_w-52 tb_w-96 md:tb_w-[320px]' : 'height-500-640:tb_w-48 tb_w-48 md:tb_w-52'}` }>
                                <PreviewCanvas images={canvasImages} baseImage={product.custom_data._canvas} setCanvasBlob={setCanvasBlob} activeTab={activeTab} />
                            </div>
                        )}

                        {activeTab === null && !isSingleTab && ( <div className="tb_border-t tb_border-gray-300 tb_mb-2"></div> )}

                        {currentFields.map((field, idx) => (
                            <div 
                                key={idx}
                                className={`tb_border tb_border-gray-200 tb_px-6 height-500-640:tb_px-3 tb_pt-4 height-500-640:tb_pt-2 tb_pb-2 tb_rounded-lg tb_w-[90%] tb_m-auto ${ isSingleTab || currentFields[currentStep]?.type === 'info' ? '' : 'tb_shadow-md'} tb_mb-0 ${isSingleTab ? '' : activeTab === idx ? '' : 'tb_hidden'}`}
                                style={{ boxShadow: '0 -2px 15px #33333324' }}
                            >
                                {error && (
                                    <div className="tb_bg-primary-100 tb_border tb_border-primary-400 tb_text-primary-700 tb_px-4 tb_py-3 tb_rounded tb_relative tb_mb-2" role="alert">
                                        {/* <strong className="tb_font-bold">Error: </strong> */}
                                        <span className="tb_block tb_sm:inline">{error}</span>
                                        {(() => {
                                            setTimeout(() => setError(null), 10000)
                                        })()}
                                    </div>
                                )}
                                <div className="tb_flex tb_justify-between tb_items-start">
                                    <div>
                                        <h3 className="tb_text-lg tb_font-bold">{field.steptitle}</h3>
                                        {field.subtitle !== '' && <p className="tb_text-sm tb_text-gray-500">{field.subtitle}</p>}
                                        {field.heading !== '' && <p className="tb_text-sm tb_text-gray-500">{field.heading}</p>}
                                    </div>
                                    {!isSingleTab && (
                                        <button
                                            onClick={currentStep === (product.custom_fields[selectedType]?.length || 0) - 1 ? handleDone : handleNextStep}
                                            className="tb_text-primary tb_font-medium tb_px-2 tb_rounded-md"
                                        >
                                            {currentStep === (product.custom_fields[selectedType]?.length || 0) - 1 ? __('done', 'Done') : __('next', 'Next')}
                                        </button>
                                    )}
                                </div>
                                {/*  */}
                                {(() => {
                                    switch (field.type) {
                                        case 'radio':
                                            return <Radio setError={setError} currentField={field} handleOptionChange={handleOptionChange} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                        case 'checkbox':
                                            return <Checkbox setError={setError} currentField={field} handleOptionChange={handleOptionChange} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                        case 'outfit':
                                            return <Outfit setError={setError} currentField={field} handleOptionChange={handleOptionChange} selectedOutfit={selectedOutfit} setSelectedOutfit={setSelectedOutfit} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                        case 'voice':
                                            return <Voice setError={setError} currentField={field} handleOptionChange={handleOptionChange} setActiveTab={setActiveTab} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                        case 'info':
                                            return <Info setError={setError} currentField={field} handleOptionChange={handleOptionChange} setActiveTab={setActiveTab} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                        default:
                                            return <Input setError={setError} currentField={field} handleOptionChange={handleOptionChange} setActiveTab={setActiveTab} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                    }
                                })()}
                                {/*  */}
                            </div>
                        ))}

                        <div className="">
                            <div className={`tb_flex tb_justify-center tb_gap-2 tb_px-4 ${activeTab !== null || isSingleTab ? 'tb_hidden' : ''}`}>
                                {currentFields.map((field, idx) => (
                                    <div
                                        key={idx}
                                        className={`tb_relative tb_cursor-pointer tb_p-2 tb_rounded-md tb_border ${activeTab === idx ? 'tb_border-blue-500 tb_text-white' : 'tb_border-gray-200'}`}
                                        onClick={() => handleTabClick(idx)}
                                    >
                                        <img
                                            src={field.stepicon}
                                            alt={field.steptitle}
                                            className="tb_w-8 tb_h-8 tb_mx-auto"
                                        />
                                        {visitedTabs.has(idx) && (
                                            <div className="tb_absolute tb_top-0 tb_right-0">
                                                <Check color='#e63f51' />
                                            </div>
                                        )}
                                        <p className="tb_text-center tb_mt-2 tb_text-sm">{field.steptitle}</p>
                                    </div>
                                ))}
                            </div>
                            { (activeTab === null || isSingleTab) && (
                                <div className="tb_flex tb_justify-center tb_p-4">
                                    <button onClick={addToCart} className="tb_w-full tb_bg-primary tb_text-white tb_px-4 tb_py-2 tb_rounded-lg tb_font-medium hover:tb_bg-primary-dark" disabled={add2CartLoading}>
                                        {add2CartLoading ? __('adding_', 'Adding...') : __('add_to_cart', 'Add to Cart')}
                                    </button>
                                </div>
                            )}
                        </div>

                    </div>
                ) }
            </div>
            <div className={`tb_h-full ${!confirmation && 'tb_hidden'}`}>
                {confirmation && (
                    <Confirmation data={confirmation} closePopup={closePopup} selectedType={selectedType} canvasImages={canvasImages} setCanvasBlob={setCanvasBlob} activeTab={activeTab} product={product} />
                )}
            </div>
        </div>
    );
};

export default ProductPage;
