import React from 'react';
import Slider from 'react-slick';
// import "slick-carousel/slick/slick.css";
// import "slick-carousel/slick/slick-theme.css";


export default function Checkbox({ currentField, handleOptionChange, updateProductData, updateObjRows }) {
    return (
        <div className="tb_mt-4">
            {currentField.options.length <= 3 && currentField.options.some(opt => opt.imageUrl) ? (
                <div className={`tb_grid ${currentField.options.length === 1 ? 'tb_grid-cols-1' : currentField.options.length === 2 ? 'tb_grid-cols-2' : 'tb_grid-cols-3'} tb_gap-4 tb_justify-center tb_items-center`}>
                    {currentField.options.map((option) => (
                        <div key={option.label} className="tb_text-center">
                            <label className="tb_cursor-pointer">
                                <div className="tb_relative tb_pb-[100%] tb_bg-white tb_border tb_border-gray-200 tb_rounded-lg has-[:checked]:tb_border-primary">
                                    <input
                                        type="checkbox"
                                        name={currentField.title}
                                        id={`option-${option.label}`}
                                        className="tb_hidden"
                                        onChange={e => handleOptionChange(e, option, currentField)}
                                    />
                                    {option.imageUrl && (
                                        <img
                                            src={option.imageUrl}
                                            alt={option.label}
                                            className="tb_absolute tb_inset-0 tb_w-full tb_h-full tb_object-contain tb_p-2"
                                        />
                                    )}
                                </div>
                                <div className="tb_mt-2">
                                    <p className="tb_font-medium">{option.label}</p>
                                    {option.cost && (
                                        <p className="tb_text-sm tb_text-gray-900">${option.cost}</p>
                                    )}
                                </div>
                            </label>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="tb_space-y-4">
                    {currentField.options.map((option) => (
                        <label key={option.label} className="tb_flex tb_items-center tb_space-x-4">
                            <input
                                type="checkbox"
                                name={currentField.title}
                                id={`option-${option.label}`}
                                className="tb_form-checkbox tb_text-blue-500"
                                onChange={e => handleOptionChange(e, option, currentField)}
                            />
                            <span className="tb_font-medium">{option.label}</span>
                            {option.imageUrl && (
                                <img
                                    src={option.imageUrl}
                                    alt={option.label}
                                    className="tb_w-12 tb_h-12 tb_rounded-full"
                                />
                            )}
                            <span className="tb_ml-2 tb_text-green-500">
                                {option.cost && `+$${option.cost}`}
                            </span>
                        </label>
                    ))}
                </div>
            )}
        </div>
    )
}
