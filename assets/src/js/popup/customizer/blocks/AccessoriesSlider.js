import React, { useEffect, useState } from 'react';
import Slider from 'react-slick';
import { X } from 'lucide-react';
// import "slick-carousel/slick/slick.css";
// import "slick-carousel/slick/slick-theme.css";


export default function AccessoriesSlider({ currentField, selectedOutfit, handleOptionChange, combinedCart, updateObjRows }) {
    const { setInTotal } = combinedCart;
    const [selectedOptions, setSelectedOptions] = useState(new Set());
    
    const settings = {
      dots: false,
      speed: 500,
      infinite: true,
      slidesToShow: 3,
      slidesToScroll: 3,
      nextArrow: <button type="button" className="slick-next tb_-translate-y-5" slidecount={3}>→</button>,
      prevArrow: <button type="button" className="slick-prev tb_-translate-y-5" slidecount={3}>←</button>
    };

    const handleChange = (e, option, field) => {
      const currentGroup = field.groups[0];
      
      if (currentGroup.type === 'checkbox') {
        // For checkbox - allow multiple selections
        if (e.target.checked) {
          // Add new option
          if (parseFloat(option.cost) > 0) {
            setInTotal(prevTotal => prevTotal + parseFloat(option.cost));
          }
          setSelectedOptions(prev => new Set([...prev, option.label]));
          
          // Get all currently selected options
          const checkedInputs = document.querySelectorAll(`input[name="${currentGroup.title}"]:checked`);
          const selectedOpts = Array.from(checkedInputs).map(input => {
            const label = input.id.replace('option-', '');
            return currentGroup.options.find(opt => opt.label === label);
          });
          
          updateObjRows(field, {
            groups: [{
              ...currentGroup,
              options: selectedOpts
            }]
          });
          
        } else {
          // Remove unchecked option
          if (parseFloat(option.cost) > 0) {
            setInTotal(prevTotal => prevTotal - parseFloat(option.cost));
          }
          setSelectedOptions(prev => {
            const newSet = new Set(prev);
            newSet.delete(option.label);
            return newSet;
          });
          
          // Get remaining selected options
          const checkedInputs = document.querySelectorAll(`input[name="${currentGroup.title}"]:checked`);
          const remainingOpts = Array.from(checkedInputs).map(input => {
            const label = input.id.replace('option-', '');
            return currentGroup.options.find(opt => opt.label === label);
          });
          
          updateObjRows(field, {
            groups: [{
              ...currentGroup,
              options: remainingOpts
            }]
          });
        }
      } else { 
        // For radio/select - only one selection allowed
        const previousOption = currentGroup.options.find(opt => selectedOptions.has(opt.label));
        
        // Remove cost of previous selection if any
        if (previousOption && parseFloat(previousOption.cost) > 0) {
          setInTotal(prevTotal => prevTotal - parseFloat(previousOption.cost));
        }
        
        // Add cost of new selection
        if (parseFloat(option.cost) > 0) {
          setInTotal(prevTotal => prevTotal + parseFloat(option.cost));
        }
        
        // Update selected options to only the new selection
        setSelectedOptions(new Set([option.label]));
        
        // Update objRows with single selected option
        updateObjRows(field, {
          groups: [{
            ...currentGroup,
            options: [option]
          }]
        });
      }
      
      handleOptionChange(e, option, field);
    };
    
    const handleReset = () => {
      const currentGroup = currentField.groups[0];
      const checkedInputs = document.querySelectorAll(`input[name="${currentGroup.title}"]:checked`);
      
      checkedInputs.forEach(input => {
        input.checked = false;
        const label = input.id.replace('option-', '');
        const option = currentGroup.options.find(opt => opt.label === label);
        
        // Create a synthetic event
        const event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);
        
        handleChange(event, option, currentField);
      });
      
      setSelectedOptions(new Set());
    };
    
    return (
      <div className="tb_w-[calc(100%-40px)] tb_m-auto">
        <div className="slider-container tb_relative">
          {/* Reset button */}
          <div className="tb_absolute tb_right-0 tb_-top-8 tb_z-10 tb_group">
            <button
              onClick={handleReset}
              className="tb_p-1 tb_rounded-full tb_text-gray-400 hover:tb_text-gray-600 tb_transition-colors"
            >
              <X className="tb_w-4 tb_h-4" />
              <span className="tb_absolute tb_left-full tb_ml-2 tb_top-1/2 tb_-translate-y-1/2 tb_whitespace-nowrap tb_bg-gray-800 tb_text-white tb_text-xs tb_px-2 tb_py-1 tb_rounded tb_opacity-0 tb_invisible group-hover:tb_opacity-100 group-hover:tb_visible tb_transition-all">
                Reset
              </span>
            </button>
          </div>

          <Slider {...settings}>
            {currentField.groups[0].options.map((option) => {
              const group = currentField.groups[0];
              const inputId = `${group.title}-${option.label}-${currentField.fieldID}`;
              
              return (
                <div key={option.label} className="tb_px-2">
                    <div className="tb_cursor-pointer tb_text-center">
                        {/* Product Image */}
                        <div className="tb_relative tb_pb-[100%] tb_bg-white tb_border tb_border-gray-200 tb_rounded-lg has-[:checked]:tb_border-primary-500 has-[:checked]:tb_text-primary-900">
                          {group.type === 'radio' && (
                            <input 
                              type="radio"
                              id={inputId}
                              name={group.title}
                              className="tb_hidden peer"
                              onChange={e => handleChange(e, option, currentField)}
                            />
                          )}
                          {group.type === 'checkbox' && (
                            <input 
                              type="checkbox"
                              id={inputId}
                              name={group.title}
                              className="tb_hidden peer"
                              onChange={e => handleChange(e, option, currentField)}
                            />
                          )}
                          {group.type === 'select' && (
                            <input 
                              type="radio"
                              id={inputId}
                              name={group.title}
                              className="tb_hidden peer"
                              onChange={e => handleChange(e, option, currentField)}
                            />
                          )}
                          <label 
                            htmlFor={inputId}
                            className="tb_absolute tb_inset-0 tb_cursor-pointer"
                          >
                            <img
                              src={option.thumbUrl || option.imageUrl}
                              alt={option.label}
                              className="tb_absolute tb_inset-0 tb_w-full tb_h-full tb_object-contain tb_p-2"
                            />
                          </label>
                        </div>
                        {/* Price */}
                        <div className="tb_mt-2">
                            <p className="tb_text-sm tb_text-gray-900">
                                ${option.cost}
                            </p>
                        </div>
                    </div>
                </div>
              );
            })}
          </Slider>
          <style jsx>{`.slick-next:before, .slick-prev:before {color: #ff4545;}.slider-container .slick-next {right: -15px;}.slider-container .slick-prev {left: -15px;}`}</style>
        </div>
      </div>
    );
}