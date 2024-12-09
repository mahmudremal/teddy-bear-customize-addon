import React, { useState } from 'react';

export default function Info({ currentField, setActiveTab, setError, updateProductData, combinedCart, updateObjRows }) {
    const [step, setStep] = useState(1);
    const [tName, setTname] = useState('');
    const [tBirth, setTBirth] = useState('');
    const [tReciever, setTReciever] = useState('');
    const [tSender, setTSender] = useState('');

    const names = ['Teddy', 'Treasure', 'Angel', 'Gem', 'Sweet Pea', 'Sugar', 'Sunshine', 'Angel Face', 'Bunny', 'Cuddlebug', 'Beatrix', 'Buttercup', 'Kisses', 'Precious', 'Princess', 'Lovey Dovey', 'Honeybun', 'Caramel', 'Caramel', 'Charlotte', 'Darla', 'Gracie', 'Honey', 'Maggie', 'Penny', 'Barnaby', 'Benjy', 'Bruno', 'Buttons', 'Champ', 'Chip', 'Dusty', 'Harvey', 'Jasper', 'Jupiter', 'Belle', 'Chloe', 'Dolly', 'Ellie', 'Honeybear', 'Lily', 'Lola', 'Lucy', 'Penny', 'Rosie', 'Stella', 'Candyfloss', 'Sunshine', 'Darling', 'Sweetheart', 'Pudding', 'Marshmallow', 'Cupcake', 'Gizmo'];

    const steps = [
        { id: 1, key: 'teddy_name', label: 'Teddy full Name', fieldId: '4.0', type: 'text' },
        { id: 2, key: 'teddy_birth', label: 'Birth date', fieldId: '4.2', type: 'date', readOnly: true },
        { id: 3, key: 'teddy_reciever', label: "Receiver's Name", fieldId: '4.3', type: 'text' },
        { id: 4, key: 'teddy_sender', label: 'Created with love by', fieldId: '4.4', type: 'text' }
    ];

    const activeSteps = steps.filter(step => currentField[step.key] === 'on');

    const handleNext = () => {
        setStep(prevStep => Math.min(prevStep + 1, activeSteps.length));
        handleInfoChange();
    };

    const handleSkip = () => {
        setStep(prevStep => Math.min(1, activeSteps.length));
        handleInfoChange();
        setActiveTab(null);
    };

    const handleInfoChange = () => {
        const infos = {
            teddy_name: tName,
            teddy_birth: tBirth,
            teddy_sender: tSender,
            teddy_reciever: tReciever
        };
        
        updateObjRows(currentField, { infos });
    };

    // Add useEffect to handle changes in form values
    React.useEffect(() => {
        handleInfoChange();
    }, [tName, tBirth, tSender, tReciever]);

    return (
        <div className="tb_p-6 tb_absolute tb_top-0 tb_left-0 tb_w-full tb_h-full tb_bg-white tb_flex tb_flex-col tb_justify-center">
            <div className="tb_flex tb_justify-between tb_items-center tb_mb-4">
                <h2 className="tb_text-lg tb_font-bold">Help us fill the details of your DubiDo with 4 quick questions</h2>
            </div>
            <p className="tb_mb-4">After your purchase is completed we'll send you your printable birth certificate as a pdf via email.</p>
            <fieldset className="tb_space-y-4 tb_border-none">
                {activeSteps.map((currentStep, index) => (
                    <div key={currentStep.id} className={step === index + 1 ? '' : 'tb_hidden'}>
                        <fieldset className="tb_mb-4 tb_border-none">
                            <label htmlFor={`field_${currentStep.fieldId}`} className="tb_block tb_text-sm tb_font-medium">{currentStep.label}</label>
                            <input
                                className="tb_w-full tb_mt-1 tb_p-2 tb_border tb_border-gray-300 tb_rounded"
                                type={currentStep.type}
                                value={
                                    currentStep.key === 'teddy_name' ? tName :
                                    currentStep.key === 'teddy_birth' ? tBirth :
                                    currentStep.key === 'teddy_reciever' ? tReciever :
                                    currentStep.key === 'teddy_sender' ? tSender : ''
                                }
                            onChange={e => {
                                if (currentStep.key === 'teddy_name') {
                                    setTname(e.target.value);
                                } else if (currentStep.key === 'teddy_birth') {
                                    setTBirth(e.target.value);
                                } else if (currentStep.key === 'teddy_reciever') {
                                    setTReciever(e.target.value);
                                } else if (currentStep.key === 'teddy_sender') {
                                    setTSender(e.target.value);
                                }
                            }}
                            />
                            {currentStep.key === 'teddy_name' && (
                                <label className="tb_flex tb_items-center tb_space-x-2">
                                    <input
                                        type="checkbox"
                                        onChange={(event) => {
                                            if (event.target.checked) {
                                                setTname(names[Math.floor(Math.random() * names.length)]);
                                            } else {
                                                setTname('');
                                            }
                                        }}
                                    />
                                    <span title="Choose a name for me">Choose a name for me</span>
                                </label>
                            )}
                        </fieldset>
                    </div>
                ))}
                <div className="tb_flex tb_flex-col tb_justify-end tb_gap-2 tb_p-2">
                    <button onClick={() => {
                        if (step === activeSteps.length) {
                            handleSkip();
                        } else {
                            handleNext();
                        }
                    }}
                    className="tb_block tb_bg-primary tb_text-white tb_px-4 tb_py-2 tb_rounded tb_cursor-pointer tb_border tb_border-primary">
                        {step === activeSteps.length ? "Done" : "Next"}
                    </button>
                    {step < activeSteps.length && (
                        <button onClick={() => { handleSkip(); }} className="tb_block tb_border-none tb_text-primary hover:tb_bg-primary hover:tb_text-dark tb_px-4 tb_py-2 tb_rounded tb_cursor-pointer">Skip</button>
                    )}
                </div>
            </fieldset>
        </div>
    );
}
