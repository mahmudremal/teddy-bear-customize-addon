import React, { useState } from 'react';
import AccessoriesSlider from './AccessoriesSlider';


export default function Outfit({ currentField, selectedOutfit, setSelectedOutfit, handleOptionChange, updateProductData, combinedCart, updateObjRows }) {
    // Track selected options for all groups
    const [selectedGroupOptions, setSelectedGroupOptions] = useState({});

    // Function to handle updating options from AccessoriesSlider
    const handleUpdateOptions = (group, options) => {
        // Update the options for this specific group while preserving others
        setSelectedGroupOptions(prev => ({
            ...prev,
            [group.title]: options
        }));

        // Combine all selected options from all groups
        const allGroups = currentField.groups.map(g => ({
            ...g,
            options: g.title === group.title ? options : selectedGroupOptions[g.title] || []
        }));

        // Update objRows with all groups' selected options
        updateObjRows(currentField, {
            groups: allGroups
        });
    };

    return (
        <div className="tb_mt-4 tb_space-y-6">

            <div className="tb_flex tb_justify-center tb_gap-2.5 tb_border-b tb_border-gray-300">
                {!selectedOutfit && currentField.groups[0]?.title && setSelectedOutfit(currentField.groups[0].title)}
                {currentField.groups.map((group, idx) => (
                    <h4
                        key={idx}
                        className={`tb_text-xl tb_cursor-pointer tb_py-2 tb_px-2.5 tb_text-primary tb_rounded-t-lg tb_border-solid tb_border tb_border-b-0 ${
                            selectedOutfit === group.title
                                ? 'tb_border-primary'
                                : 'tb_border-gray-300'
                        }`}
                        onClick={() => setSelectedOutfit(group.title)}
                    >
                        {group.title}
                    </h4>
                ))}
            </div>

            
            {currentField.groups.map((group, idx) => (
                <div key={idx} className={`${selectedOutfit === group.title ? 'tb_block' : 'tb_hidden'} tb_min-h-[150px]`}>
                    <AccessoriesSlider 
                        currentField={{
                            ...currentField,
                            groups: [group] // Pass only the current group
                        }}
                        selectedOutfit={group.title}
                        handleOptionChange={handleOptionChange}
                        combinedCart={combinedCart}
                        updateObjRows={(field, updates) => handleUpdateOptions(group, updates.groups[0].options)}
                    />
                </div>
            ))}
        </div>
    )
}
