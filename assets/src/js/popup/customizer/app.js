import axios from 'axios';
import { Toaster } from 'react-hot-toast';

function removeAllClickListeners(element) {
  // Get all click event listeners and remove them
  const clone = element;
  
  // Remove standard click event listeners
  clone.removeEventListener('click', null);
  clone.removeEventListener('click', null, true);
  
  // Clear onclick property
  clone.onclick = null;
  
  // Clear any jQuery click events if jQuery exists
  if (typeof jQuery !== 'undefined') {
    jQuery(clone).off('click');
  }

  return clone;
}

function App() {
  const { useState, useCallback, useEffect } = React;
  const ProductPage = require('./ProductPage').default;
  const [productData, setProductData] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [productId, setProductId] = useState(null);
  const [visiblePopup, setVisiblePopup] = useState(false);

  useEffect(() => {
    
    const handleButtonClick = async (e) => {
      e.preventDefault();e.stopPropagation();
      const button = e.currentTarget;
      // 
      try {
        let product_id = 0;
        if (button.dataset?.config) {
          const config = JSON.parse(button.dataset.config);
          product_id = config.id;
        } else {
          product_id = parseInt(button.dataset.product_id || 0);
        }
        setProductId(product_id);
        setIsLoading(true);
        setVisiblePopup(true);

        // Ensure fwpSiteConfig exists
        if (!fwpSiteConfig || !fwpSiteConfig.ajaxUrl) {
          throw new Error('Site configuration is missing');
        }

        const formData = new FormData();
        formData.append('product_id', product_id);
        formData.append('_nonce', fwpSiteConfig.ajax_nonce || '');
        formData.append('action', 'teddybear/project/ajax/search/product');
        
        const response = await axios.post(fwpSiteConfig.ajaxUrl, formData);

        if (!response.data || !response.data.data || !response.data.data.product) {
          throw new Error('Invalid response format');
        }

        setProductData(response.data.data.product);
        setError(null);
      } catch (err) {
        console.error('Error:', err);
        setError(err.message === 'Invalid button configuration' 
          ? 'Invalid button configuration'
          : 'Product not found or there was an error loading the data'
        );
        setProductData(null);
      } finally {
        setIsLoading(false);
      }
      // Prevent default action
      return false;
    };

    const buttons = document.querySelectorAll(
      (fwpSiteConfig.onatc && fwpSiteConfig.onatc !== '') ? '.add_to_cart_button' : '.init_cusomizeaddtocartbtn'
    );
    buttons.forEach(button => {
      removeAllClickListeners(button).addEventListener('click', handleButtonClick);
    });

    // Cleanup listeners
    return () => {
      buttons.forEach(button => {
        button.removeEventListener('click', handleButtonClick);
      });
    };
  }, []);

  const updateProductData = useCallback(newData => {
    setProductData((prevData) => ({
      ...prevData,
      ...newData,
    }));
  }, []);

  const closePopup = (showConfirmation = true) => {
    const userConfirmed = showConfirmation ? window.confirm("Are you sure you want to close it? Any unsaved changes will be lost.") : true;
    if (userConfirmed) {
      setProductId(null);
      setProductData(null);
      setVisiblePopup(false);
    }
  };

  if (error) {
    return (
      <div className="tb_App">
        <div className="tb_bg-white tb_p-8 tb_rounded-lg tb_shadow-lg tb_max-w-full md:tb_w-[450px] md:tb_min-w-4xl tb_mx-auto">
          <div className="tb_text-center">
            <svg className="tb_mx-auto tb_h-12 tb_w-12 tb_text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 className="tb_mt-2 tb_text-lg tb_font-medium tb_text-gray-900">Product Not Found</h3>
            <p className="tb_mt-1 tb_text-sm tb_text-gray-500">{error}</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="tb_App">
      {visiblePopup && (
        <div className='tb_absolute tb_inset-0 tb_z-[99999]'>
          <Toaster position="top-right" reverseOrder={true} />
          <div className="tb_fixed tb_inset-0 tb_bg-gray-900 tb_bg-opacity-50 tb_z-40" onClick={closePopup} ></div>
          <div className="tb_w-[90vw] tb_min-h-[400px] md:tb_min-h-[500px] tb_max-h-[95vh] tb_overflow-hidden tb_overflow-y-auto tb_fixed tb_top-1/2 tb_left-1/2 tb_transform tb_-translate-x-1/2 tb_-translate-y-1/2 tb_z-50 tb_bg-white tb_p-0 tb_rounded-lg tb_shadow-lg tb_max-w-full md:tb_w-[450px] md:tb_min-w-4xl">
            { isLoading ? (
              <div className="tb_p-8 tb_text-center tb_flex tb_absolute tb_w-full tb_h-full tb_items-center tb_justify-center tb_flex-col">
                <div className="tb_animate-spin tb_rounded-full tb_h-8 tb_w-8 tb_border-b-2 tb_border-gray-900 tb_mx-auto"></div>
                <p className="tb_mt-4 tb_text-gray-600">Loading product data...</p>
              </div>
            ) : productData ? (
              <ProductPage 
                React={React} 
                ReactDOM={ReactDOM} 
                product_id={productId} 
                product={productData} 
                updateProductData={updateProductData} 
                closePopup={closePopup} 
              />
            ) : null }
          </div>
        </div>
      )}
      {/* <style jsx>{`.tb_App button:hover {background-color: none;}`}</style> */}
    </div>
  );
}

export default App;
