import React from 'react';
import { LoaderCircle } from 'lucide-react';

const Loading = () => {
    return (
        <div className="tb_flex tb_flex-col tb_justify-center tb_items-center tb_h-[300px] tb_text-primary">
            <LoaderCircle className="tb_animate-spin tb_h-12 tb_w-12 tb_text-primary-light" />
            <p className="tb_mt-4 tb_text-gray-500">Loading...</p>
        </div>
    );
};

export default Loading;
