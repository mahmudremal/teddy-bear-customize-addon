import React from 'react';

export default function Input({ setError, currentField, setActiveTab, updateProductData, combinedCart, updateObjRows }) {
    return (
        <div className="tb_mt-4">
            <div className="tb_space-y-4">
                <input
                    type={currentField.type}
                    name={`field-${currentField.fieldID}`}
                    className="tb_w-full tb_mt-1 tb_p-2 tb_border tb_border-gray-300 tb_rounded"
                    onChange={e => updateObjRows(currentField, {value: e.target.value})}
                />
            </div>
        </div>
    )
}
