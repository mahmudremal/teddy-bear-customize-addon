import React, { useState, useRef, useLayoutEffect, useEffect } from 'react';
import WaveSurfer from 'wavesurfer.js';
import RecordPlugin from 'wavesurfer.js/dist/plugins/record.js';
import { Mic, Play, Pause, Square, Upload, Mail, SkipForward } from 'lucide-react';
import { sprintf } from 'sprintf-js';

export default function Voice({ currentField, setError, updateProductData, combinedCart, updateObjRows }) {
    const { inTotal, setInTotal, setBlobFiles } = combinedCart;
    const [isRecording, setIsRecording] = useState(false);
    const [audioFile, setAudioFile] = useState(null);
    const [recordingStatus, setRecordingStatus] = useState('');
    const [isPlaying, setIsPlaying] = useState(false);
    const [showAddLaterMessage, setShowAddLaterMessage] = useState(false);
    const waveformRef = useRef(null);
    const wavesurferRef = useRef(null);
    const recordPluginRef = useRef(null);
    const [isLoading, setIsLoading] = useState(false);
    const [timer, setTimer] = useState(0);
    const timerRef = useRef(null);
    const [hasVoiceOption, setHasVoiceOption] = useState(false);

    useEffect(() => {
        if (!hasVoiceOption && (audioFile || showAddLaterMessage)) {
            setHasVoiceOption(true);
            setInTotal(prevTotal => prevTotal + parseFloat(currentField?.cost??'0'));
        } else if (hasVoiceOption && !audioFile && !showAddLaterMessage) {
            setHasVoiceOption(false);
            setInTotal(prevTotal => prevTotal - parseFloat(currentField?.cost??'0'));
        }
    }, [audioFile, showAddLaterMessage]);

    useLayoutEffect(() => {
        if (waveformRef.current) {
            wavesurferRef.current = WaveSurfer.create({
                container: waveformRef.current,
                progressColor: '#e63f51',
                waveColor: '#fec52e',
                responsive: true,
                barRadius: 5,
                barWidth: 5,
                barGap: 1,
                height: 20,
            });

            recordPluginRef.current = RecordPlugin.create();
            wavesurferRef.current.registerPlugin(recordPluginRef.current);

            let timerInterval;
            recordPluginRef.current.on('startRecording', () => {
                let startTime = Date.now();
                timerInterval = setInterval(() => {
                    const currentTime = (Date.now() - startTime) / 1000;
                    const remainingTime = parseFloat(currentField.duration) - currentTime;
                    setTimer(Math.abs(remainingTime));
                    if (remainingTime <= 0) {
                        clearInterval(timerInterval);
                        timerInterval = null;
                        if (isRecording) {
                            stopRecording().then(() => {
                                setTimer(0);
                            }).catch(err => {
                                console.error('Error stopping recording:', err);
                            });
                        }
                    }
                }, 100);
            });

            recordPluginRef.current.on('stopRecording', () => {
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
            });

            recordPluginRef.current.on('startRecording', () => {
                setIsRecording(true);
                setRecordingStatus('Recording started...');
                startTimer();
                setTimer(parseFloat(currentField.duration));
            });

            recordPluginRef.current.on('stopRecording', async () => {
                setIsRecording(false);
                const audioUrl = recordPluginRef.current.getRecordedUrl();
                setAudioFile(audioUrl);
                setRecordingStatus('Recording saved!');
                wavesurferRef.current.load(audioUrl);
                stopTimer();
                handleVoiceRecord(audioUrl); // Call handleVoiceRecord with the recorded audio URL
            });

            wavesurferRef.current.on('play', () => {
                setIsPlaying(true);
                startTimer();
            });
            wavesurferRef.current.on('audioprocess', () => {
                const currentTime = wavesurferRef.current.getCurrentTime();
                setTimer(currentTime);
            });

            wavesurferRef.current.on('pause', () => {
                setIsPlaying(false);
                stopTimer();
            });

            wavesurferRef.current.on('finish', () => {
                setIsPlaying(false);
                stopTimer();
            });

            wavesurferRef.current.on('loading', () => {
                setIsLoading(true);
            });
            
            wavesurferRef.current.on('ready', () => {
                setIsLoading(false);
            });
            
        }

        return () => {
            if (wavesurferRef.current) {
                wavesurferRef.current.destroy();
            }
        };
    }, []);

    const startTimer = () => {
        // timerRef.current = setInterval(() => {
        //     setTimer(prevTimer => prevTimer + 1);
        // }, 1000);
    };

    const stopTimer = () => {
        // clearInterval(timerRef.current);
        // setTimer(0);
    };

    const startRecording = async () => {
        try {
            if (!recordPluginRef.current) {
                throw new Error('Recording plugin not initialized');
            }
            await recordPluginRef.current.startRecording();
            setIsRecording(true);
            setShowAddLaterMessage(false);
            setRecordingStatus(sprintf('Please record your voice up to %d seconds.', currentField.duration));
        } catch (err) {
            console.error('Error accessing microphone:', err);
            setRecordingStatus('Error accessing microphone');
        }
    };

    const stopRecording = async () => {
        if (isRecording && recordPluginRef.current) {
            try {
                await recordPluginRef.current.stopRecording();
                setIsRecording(false);
            } catch (err) {
                console.error('Error stopping the recording:', err);
                setRecordingStatus('Error stopping the recording');
            }
        }
    };

    const handleFileUpload = async (event) => {
        const file = event.target.files[0];
        if (file) {
            if (isPlaying && wavesurferRef.current) {
                wavesurferRef.current.pause();
            }

            try {
                if (file.size > (1024 * 1024 * 20)) {
                    throw new Error('Oh! The file you are trying to upload is too heavy. The file must be up to 20Mb');
                }

                if (!file.type) {
                    throw new Error('Invalid file');
                }

                if (!file.type.startsWith('video/') && !file.type.startsWith('audio/')) {
                    throw new Error('File is not audio, nor video.');
                }

                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const fileReader = new FileReader();
                fileReader.onload = function(event) {
                    audioContext.decodeAudioData(event.target.result, async (buffer) => {
                        const duration = buffer.duration;
                        if (duration && duration > 1 && duration > parseFloat(currentField.duration)) {
                            setError(sprintf('Office! The file I uploaded is too long. Note ♥ The length of the recording does not exceed %s seconds.', currentField.duration));
                        } else {
                            const audioUrl = URL.createObjectURL(file);
                            setAudioFile(audioUrl);
                            setShowAddLaterMessage(false);
                            setRecordingStatus('Audio file uploaded!');
                            setIsPlaying(false);
                            if (wavesurferRef.current) {
                                await wavesurferRef.current.load(audioUrl);
                            }
                            handleVoiceRecord(audioUrl); // Call handleVoiceRecord with the uploaded audio URL
                        }
                    });
                };
                fileReader.readAsArrayBuffer(file);
            } catch (err) {
                console.error('Error uploading file:', err);
                setError(err.message);
            }
        }
    };

    const handleSkip = () => {
        setAudioFile(null);
        setShowAddLaterMessage(false);
        setRecordingStatus('Skipped voice recording');
        setIsPlaying(false);
        if (wavesurferRef.current) {
            wavesurferRef.current.empty();
        }
        handleVoiceRecord(null);
    };

    const handleAddLater = () => {
        setAudioFile(null);
        setShowAddLaterMessage(true);
        setRecordingStatus('Will add voice later');
        setIsPlaying(false);
        if (wavesurferRef.current) {
            wavesurferRef.current.empty();
        }
        handleVoiceRecord('later');
    };

    const togglePlay = () => {
        if (wavesurferRef.current) {
            if (isPlaying) {
                wavesurferRef.current.pause();
            } else {
                wavesurferRef.current.play();
            }
        }
    };

    const handleVoiceRecord = (recordingData) => {
        // Clear any existing voice recordings and set new one if exists
        setBlobFiles(prevFiles => {
            const filteredFiles = prevFiles.filter(file => !(file instanceof Blob && file.type.startsWith('audio/')));
            const timestamp = Date.now();
            if (recordingData && recordingData !== 'later' && recordingData !== null) {
                const blobName = recordingData.includes('/') ? 
                    `${timestamp}-recording.mp3` : // For recording
                    `${timestamp}-${recordingData.split('/').pop()}`; // For upload

                // Convert blob URL to actual Blob object
                fetch(recordingData)
                    .then(response => response.blob())
                    .then(blob => {
                        // Create new Blob with audio type and name
                        const audioBlob = new Blob([blob], { type: 'audio/mpeg' });
                        Object.defineProperty(audioBlob, 'name', {
                            value: blobName
                        });
                        return [...filteredFiles, audioBlob];
                    });
            }
            return filteredFiles;
        });
        updateObjRows(currentField, {
            product: currentField.product,
            duration: currentField.duration,
            cost: currentField.cost,
            attached: recordingData === 'later' ? {later: true} : recordingData === null ? null : {
                blob: recordingData.includes('/') ? // Check if it's a URL (recording) or filename (upload)
                    `${timestamp}-recording.mp3` : // For recording
                    `${timestamp}-${recordingData.split('/').pop()}`, // For upload
                method: recordingData.includes('/') ? 'record' : 'upload'
            }
        });
    };

    return (
        <div className="tb_p-2">
            <div className="tb_space-y-4">
                <div className="tb_grid tb_grid-cols-4 tb_gap-5 tb_justify-items-center">
                    <div className="tb_flex tb_flex-col tb_items-center">
                        <button onClick={isRecording ? stopRecording : startRecording} className={`tb_flex tb_items-center tb_justify-center tb_w-16 tb_h-16 tb_rounded-lg ${isRecording ? 'tb_bg-primary-500' : 'tb_bg-gray-200'} tb_text-white tb_shadow-sm`}>
                            {isRecording ? <Square className="tb_w-6 tb_h-6" /> : <Mic className="tb_w-6 tb_h-6" />}
                        </button>
                        <span className="tb_mt-2 tb_text-xs tb_text-gray-600">Record</span>
                    </div>

                    <div className="tb_flex tb_flex-col tb_items-center">
                        <label className="tb_cursor-pointer">
                            <div className="tb_w-16 tb_h-16 tb_flex tb_items-center tb_justify-center tb_rounded-lg tb_bg-gray-200 tb_shadow-sm">
                                <Upload className="tb_w-6 tb_h-6 tb_text-gray-600" />
                            </div>
                            <span className="tb_mt-2 tb_text-xs tb_text-gray-600 tb_block tb_text-center">Upload</span>
                            <input type="file" accept="audio/*" onChange={handleFileUpload} className="tb_hidden" />
                        </label>
                    </div>

                    <div className="tb_flex tb_flex-col tb_items-center">
                        <button onClick={handleAddLater} className="tb_w-16 tb_h-16 tb_flex tb_items-center tb_justify-center tb_rounded-lg tb_bg-gray-200 tb_shadow-sm">
                            <Mail className="tb_w-6 tb_h-6 tb_text-gray-600" />
                        </button>
                        <span className="tb_mt-2 tb_text-xs tb_text-gray-600">Add Later</span>
                    </div>

                    <div className="tb_flex tb_flex-col tb_items-center">
                        <button onClick={handleSkip} className="tb_w-16 tb_h-16 tb_flex tb_items-center tb_justify-center tb_rounded-lg tb_bg-gray-200 tb_shadow-sm">
                            <SkipForward className="tb_w-6 tb_h-6 tb_text-gray-600" />
                        </button>
                        <span className="tb_mt-2 tb_text-xs tb_text-gray-600">Skip</span>
                    </div>
                </div>

                {recordingStatus && (
                    <p className="tb_text-sm tb_text-gray-600 tb_text-center">{recordingStatus}</p>
                )}

                {showAddLaterMessage ? (
                    <p className="tb_text-sm tb_text-gray-600">
                        1. Receive instructions & button in order email.<br/>
                        2. Upload audio file anytime later.<br/>
                        3. We will ship when your audio file is received.
                    </p>
                ) : null}

                <div className={`tb_flex tb_items-center tb_gap-4 ${!audioFile ? (isRecording ? '' : 'tb_hidden') : ''}`}>
                    <button onClick={togglePlay} disabled={isLoading} className={`tb_w-10 tb_h-10 tb_flex tb_items-center tb_justify-center tb_rounded-full ${isLoading ? 'tb_bg-gray-300' : 'tb_bg-gray-200'}`} >
                        {isLoading ? (
                            <div className="tb_w-5 tb_h-5 tb_border-4 tb_border-t-transparent tb_border-blue-500 tb_border-solid tb_rounded-full tb_animate-spin" />
                        ) : isPlaying ? (
                            <Pause className="tb_w-5 tb_h-5" />
                        ) : (
                            <Play className="tb_w-5 tb_h-5" />
                        )}
                    </button>
                    <div ref={waveformRef} className="tb_w-full tb_h-[20px] tb_bg-gray-50 tb_rounded" />
                    <div className="tb_flex tb_items-center tb_gap-4">
                        <span className="tb_text-sm tb_text-gray-600">
                            {`${Math.floor(timer)}:${('00' + Math.floor((timer % 1) * 1000)).slice(-2)}`}
                        </span>
                    </div>
                </div>

                {isRecording && (
                    <div className="tb_max-h-36 tb_overflow-y-auto tb_text-sm tb_text-gray-500 tb_mt-4">
                        <p>
                            You are permitted to record any message of your liking up to 20 seconds, with the exclusion of profanity or copyrighted materials, which are prohibited. Please note your recording may be reviewed and screened (discreetly) by our DubiDo staff. We will not modify or edit your recording. In the event of copyright infringement, profanity, hate speech or recordings of the sort, we reserve the right to decline your recording and we will notify you of this decision within 48h of the submission of your recording. You will be given the opportunity to record a new message for additional review. For further information on your rights and privacy, please refer to our Privacy Policy. Please also refer to our Disclaimer for additional information on DubiDo's liability with regard to recordings.
                        </p>
                    </div>
                )}

                {!showAddLaterMessage && !audioFile && !isRecording && (
                    <p className="tb_text-sm tb_text-gray-500 tb_text-center">
                        Please record your voice up to 20 seconds.
                    </p>
                )}
            </div>
        </div>
    );
}
