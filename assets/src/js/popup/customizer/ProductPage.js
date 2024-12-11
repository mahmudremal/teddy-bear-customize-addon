import axios from 'axios';
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
const ProductCustomization = ({ product, updateProductData, closePopup }) => {
    const { useEffect, useState } = React;
    const [canvasBlob, setCanvasBlob] = useState(null);
    const [objRows, setObjRows] = useState([]);
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
    const [blobFiles, setBlobFiles] = useState([]);
    const [visitedTabs, setVisitedTabs] = useState(new Set());
    const [confirmation, setConfirmation] = useState(null);
    const [isSingleTab, setIsSingleTab] = useState(
        product.custom_fields[product.custom_data.product_type]?.length === 1
    );

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

        formData.append('dataset', JSON.stringify(objRows));

        formData.append('_blobs', blobFiles);
        formData.append('_canvas', canvasBlob);
        
        try {
            const response = await axios.post(fwpSiteConfig.ajaxUrl, formData, {
                withCredentials: true
            });
            if (response.data.success) {
                const data = response.data.data;
                if (data?.confirmation && data.confirmation?.title) {
                    setConfirmation(data.confirmation);
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
        setObjRows(prevRows => {
            // Find existing row with same fieldID
            const existingRowIndex = prevRows.findIndex(row => row.fieldID === field.fieldID);
            const newRow = {
                fieldID: field.fieldID,
                type: field.type,
                steptitle: field.steptitle,
                headerbg: field.headerbg,
                ...selectedData
            };

            if (existingRowIndex !== -1) {
                // Update existing row
                const updatedRows = [...prevRows];
                updatedRows[existingRowIndex] = newRow;
                return updatedRows;
            } else {
                // Add new row
                return [...prevRows, newRow];
            }
        });
    };

    const handleOptionChange = (e, option, currentField) => {
        console.log(e, option, currentField);
        if (e.target.checked) {
            let imageChanged = false;
            let newImgLayers = { ...imgLayers };

            // Handle outfit section with groups
            if (currentField.groups) {
                currentField.groups.forEach(group => {
                    if (group.type === 'radio' || group.type === 'select') {
                        newImgLayers[group.title] = newImgLayers[group.title] || [];
                        const selectedInput = document.querySelector(`input[name="${group.title}"]:checked`);
                        if (selectedInput) {
                            const selectedLabel = selectedInput.id.replace('option-', '');
                            const selectedOption = group.options.find(opt => opt.label === selectedLabel);
                            if (selectedOption?.thumbUrl && selectedOption?.imageUrl) {
                                if (selectedInput.type === 'radio') {
                                    newImgLayers[group.title] = [];
                                }
                                newImgLayers[group.title].push(selectedOption.imageUrl);
                                imageChanged = true;
                            }
                        }
                    } else if (group.type === 'checkbox') {
                        newImgLayers[group.title] = newImgLayers[group.title] || [];
                        const checkedInputs = document.querySelectorAll(`input[name="${group.title}"]:checked`);
                        newImgLayers[group.title] = []; // Reset checkbox group
                        checkedInputs.forEach(input => {
                            const checkedLabel = input.id.replace('option-', '');
                            const checkedOption = group.options.find(opt => opt.label === checkedLabel);
                            if (checkedOption?.thumbUrl && checkedOption?.imageUrl) {
                                newImgLayers[group.title].push(checkedOption.imageUrl);
                                imageChanged = true;
                            }
                        });
                    }
                });
            }
            // Handle other sections with direct options (like heart selection)
            else if (currentField.options) {
                newImgLayers[option.fieldID] = newImgLayers[option.fieldID] || [];
                if (currentField.type === 'radio') {
                    if (option?.thumbUrl && option?.imageUrl) {
                        newImgLayers[option.fieldID] = [];
                        newImgLayers[option.fieldID].push(option.imageUrl);
                        imageChanged = true;
                    }
                }
            }

            // Update canvas with all selected images
            if (imageChanged) {
                console.log(newImgLayers);
                setImgLayers(newImgLayers);
                updateCanvasImages(Object.values(newImgLayers).flat());
            }

            // Track selection based on field type
            switch (currentField.type) {
                case 'radio':
                    updateObjRows(currentField, {
                        options: [option]
                    });
                    break;
                    
                case 'checkbox':
                    const checkedOptions = document.querySelectorAll(`input[name="${currentField.title}"]:checked`);
                    const selectedOptions = Array.from(checkedOptions).map(input => {
                        const label = input.id.replace('option-', '');
                        return currentField.options.find(opt => opt.label === label);
                    });
                    updateObjRows(currentField, {
                        options: selectedOptions
                    });
                    break;
                    
                case 'outfit':
                    const group = currentField.groups.find(g => g.title === option.fieldID);
                    if (group) {
                        updateObjRows(currentField, {
                            groups: currentField.groups.map(g => ({
                                type: g.type,
                                layer: g.layer,
                                title: g.title,
                                rowtype: g.rowtype,
                                options: g.title === option.fieldID ? [option] : []
                            }))
                        });
                    }
                    break;
            }
        }
    };

    const currentFields = product.custom_fields?.[selectedType] || [];
    const currentField = currentFields[currentStep];

    const combinedCart = {
        discountTotal, inTotal, setDiscountTotal, setInTotal, setBlobFiles
    };

    return (
        <div className="tb_mx-auto tb_p-0">
            <link rel="stylesheet" type="text/css" charSet="UTF-8" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css" />
            <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css" />
            
            { confirmation === null ? (
                <div>
                    <div className="tb_flex tb_justify-between tb_items-center tb_w-full tb_flex-nowrap tb_h-[50px] tb_overflow-hidden tb_mx-auto tb_px-[15px] tb_py-[10px] tb_border-b tb_border-[#eee] tb_box-border">
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
                            <div className="tb_text-sm tb_font-semibold price_amount tb_px-2 tb_py-1 tb_text-primary tb_bg-primary-100">
                                <PriceBlock price_html={product.priceHtml} inTotal={inTotal} discountTotal={discountTotal} />
                            </div>
                        </div>
                    </div>

                    { isLoading ? ( <div className="tb_flex tb_justify-center tb_items-center tb_h-auto"><Loading /> </div> ) : (
                        <div className="tb_relative">
                            {!['standing', 'sitting'].includes(selectedType) && (
                                <div className="tb_flex tb_justify-between tb_mb-8">
                                    <div
                                        className="tb_cursor-pointer tb_p-4 tb_border-2 tb_border-gray-300 tb_rounded-md tb_hover:bg-gray-100"
                                        onClick={() => handleSelectProductType('standing')}
                                    >
                                        <img
                                            src={product.positions.standing}
                                            alt="Standing"
                                            className="tb_w-full tb_h-auto tb_rounded-md"
                                        />
                                        <p className="tb_text-center tb_mt-2 tb_font-semibold">Standing</p>
                                    </div>
                                    <div
                                        className="tb_cursor-pointer tb_p-4 tb_border-2 tb_border-gray-300 tb_rounded-md tb_hover:bg-gray-100"
                                        onClick={() => handleSelectProductType('sitting')}
                                    >
                                        <img
                                            src={product.positions.sitting}
                                            alt="Sitting"
                                            className="tb_w-full tb_h-auto tb_rounded-md"
                                        />
                                        <p className="tb_text-center tb_mt-2 tb_font-semibold">Sitting</p>
                                    </div>
                                </div>
                            )}

                            {['standing', 'sitting'].includes(selectedType) && (
                                <div className="tb_m-auto tb_mb-8 tb_w-full tb_h-auto md:tb_w-[350px] md:tb_h-[350px]">
                                    <PreviewCanvas images={canvasImages} baseImage={product.custom_data._canvas} setCanvasBlob={setCanvasBlob} activeTab={activeTab} />
                                </div>
                            )}

                            {activeTab === null && !isSingleTab && ( <div className="tb_border-t tb_border-gray-300 tb_mb-2"></div> )}

                            {currentFields.map((field, idx) => (
                                <div 
                                    key={field.fieldID}
                                    className={`tb_bg-gray-50 tb_p-6 tb_rounded-lg ${ isSingleTab ? '' : 'tb_shadow-md'} tb_mb-0 ${isSingleTab ? '' : activeTab === idx ? '' : 'tb_hidden'}`}
                                >
                                    {error && (
                                        <div className="tb_bg-primary-100 tb_border tb_border-primary-400 tb_text-primary-700 tb_px-4 tb_py-3 tb_rounded tb_relative tb_mb-4" role="alert">
                                            <strong className="tb_font-bold">Error: </strong>
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
                                                {currentStep === (product.custom_fields[selectedType]?.length || 0) - 1 ? 'Done' : 'Next'}
                                            </button>
                                        )}
                                    </div>

                                    {(() => {
                                        switch (field.type) {
                                            case 'radio':
                                                return <Radio setError={setError} currentField={field} handleOptionChange={handleOptionChange} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                            case 'checkbox':
                                                return <Checkbox setError={setError} currentField={field} handleOptionChange={handleOptionChange} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                            case 'outfit':
                                                return <Outfit setError={setError} currentField={field} selectedOutfit={selectedOutfit} setSelectedOutfit={setSelectedOutfit} handleOptionChange={handleOptionChange} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                            case 'voice':
                                                return <Voice setError={setError} currentField={field} setActiveTab={setActiveTab} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                            case 'info':
                                                return <Info setError={setError} currentField={field} setActiveTab={setActiveTab} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                            default:
                                                return <Input setError={setError} currentField={field} setActiveTab={setActiveTab} updateProductData={updateProductData} combinedCart={combinedCart} updateObjRows={updateObjRows} />
                                        }
                                    })()}
                                    
                                </div>
                            ))}

                            <div className={`tb_flex tb_justify-evenly ${activeTab !== null || isSingleTab ? 'tb_hidden' : ''}`}>
                                {currentFields.map((field, idx) => (
                                    <div
                                        key={field.fieldID}
                                        className={`tb_relative tb_cursor-pointer tb_p-2 tb_rounded-md ${activeTab === idx ? 'tb_bg-blue-500 tb_text-white' : 'tb_bg-gray-200'}`}
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
                                        {add2CartLoading ? 'Adding...' : 'Add to Cart'}
                                    </button>
                                </div>
                            )}

                        </div>
                    ) }
                </div>
            ) : (
                <div className="tb_h-full">
                    <Confirmation data={confirmation} closePopup={closePopup} selectedType={selectedType} canvasImages={canvasImages} setCanvasBlob={setCanvasBlob} activeTab={activeTab} product={product} />
                </div>
            ) }

        </div>
    );
};

export default ProductCustomization;
