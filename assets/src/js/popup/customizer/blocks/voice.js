import React, { useState, useRef, useEffect } from 'react';
import { Mic, Play, Pause, Square, Upload, Mail, SkipForward } from 'lucide-react';
import WaveSurfer from 'wavesurfer.js';
import RecordPlugin from 'wavesurfer.js/dist/plugins/record.esm.js';
// import { sprintf } from 'sprintf-js';

const BUTTON_STATES = {
  NONE: 'none',
  RECORDING: 'recording',
  UPLOADED: 'uploaded',
  ADD_LATER: 'add_later',
  SKIPPED: 'skipped'
};

const MAX_FILE_SIZE = 20 * 1024 * 1024; // 20MB

export default function Voice({ currentField, setError, combinedCart, updateObjRows }) {
  const { setInTotal, setBlobFiles } = combinedCart;

  const [activeButton, setActiveButton] = useState(BUTTON_STATES.NONE);
  const [isRecording, setIsRecording] = useState(false);
  const [audioFile, setAudioFile] = useState(null);
  const [recordingStatus, setRecordingStatus] = useState('');
  const [isPlaying, setIsPlaying] = useState(false);
  const [timer, setTimer] = useState(0);
  const [hasVoiceOption, setHasVoiceOption] = useState(false);

  const audioContainerRef = useRef(null);
  const waveAudioRef = useRef(null);
  const recordPluginRef = useRef(null);
  const timerIntervalRef = useRef(null);

  useEffect(() => {
    const shouldCharge = activeButton !== BUTTON_STATES.NONE && activeButton !== BUTTON_STATES.SKIPPED;
    if (!hasVoiceOption && shouldCharge) {
      setHasVoiceOption(true);
      setInTotal((prevTotal) => prevTotal + parseFloat(currentField?.cost ?? '0'));
    } else if (hasVoiceOption && !shouldCharge) {
      setHasVoiceOption(false);
      setInTotal((prevTotal) => prevTotal - parseFloat(currentField?.cost ?? '0'));
    }
  }, [activeButton, hasVoiceOption]);

  useEffect(() => {
    if (!waveAudioRef.current) {
      waveAudioRef.current = WaveSurfer.create({
        container: audioContainerRef.current,
        waveColor: '#fec52e',
        progressColor: '#e63f51',
        cursorColor: 'transparent',
        barWidth: 2,
        barRadius: 3,
        barGap: 3,
        height: 40,
        responsive: true,
        interact: true,
      });

      waveAudioRef.current.on('play', () => setIsPlaying(true));
      waveAudioRef.current.on('pause', () => setIsPlaying(false));
      waveAudioRef.current.on('finish', () => setIsPlaying(false));
    }

    return () => {
      if (waveAudioRef.current) {
        waveAudioRef.current.destroy();
        waveAudioRef.current = null;
      }
    };
  }, []);

  const startRecording = async () => {
    try {
      if (!recordPluginRef.current) {
        recordPluginRef.current = RecordPlugin.create({
          mediaRecorder: {
            audioBitsPerSecond: 128000,
            mimeType: 'audio/wav',
          },
        });
        waveAudioRef.current.registerPlugin(recordPluginRef.current);

        recordPluginRef.current.on('record-start', () => {
          setIsRecording(true);
          setRecordingStatus(__('recstarted', 'Recording started...'));
          startTimer();
        });

        recordPluginRef.current.on('record-end', async (blob) => {
          clearInterval(timerIntervalRef.current);
          const audioUrl = URL.createObjectURL(blob);
          setAudioFile(audioUrl);
          setError(null);
          setIsRecording(false);
          setRecordingStatus('Recording saved!');
          handleVoiceRecord(audioUrl);
          waveAudioRef.current.load(audioUrl);
        });
      }

      await recordPluginRef.current.startRecording();
      setActiveButton(BUTTON_STATES.RECORDING);
      setRecordingStatus(sprintf(__('audiorecord_instuction', `Please record your voice up to %s seconds.`), currentField.duration));
    } catch (err) {
      console.error('Error accessing microphone:', err);
      setRecordingStatus(__('mic_erraccess', 'Error accessing microphone. Please ensure microphone permissions are granted.'));
      setIsRecording(false);
    }
  };

  const stopRecording = () => {
    if (recordPluginRef.current && isRecording) {
      recordPluginRef.current.stopRecording();
      clearInterval(timerIntervalRef.current);
    }
  };

  const startTimer = () => {
    const duration = parseFloat(currentField.duration);
    setTimer(duration);
    const startTime = Date.now();

    timerIntervalRef.current = setInterval(() => {
      const currentTime = (Date.now() - startTime) / 1000;
      const remainingTime = duration - currentTime;
      setTimer(Math.max(0, remainingTime));

      if (remainingTime <= 0) {
        stopRecording();
        clearInterval(timerIntervalRef.current);
      }
    }, 100);
  };

  const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    try {
      if (file.size > MAX_FILE_SIZE) {
        throw new Error('sizeover');
      }

      const audioUrl = URL.createObjectURL(file);
      setAudioFile(audioUrl);
      setError(null);
      setActiveButton(BUTTON_STATES.UPLOADED);
      setRecordingStatus('Audio file uploaded!');
      waveAudioRef.current.load(audioUrl);
    } catch (err) {
      if (err?.message == 'sizeover') {
        setError(__('maxuploadmb', 'File size must be up to 20Mb'));
      } else {
        setError(__('erroruploadvoice', "Oopsi, we couldn't load your file"));
      }
      console.error('Error uploading file:', err);
    }
  };
  // 
  const handleVoiceRecord = async (audioBlobUrl) => {
    if (!audioBlobUrl) {
      updateObjRows(currentField, { attached: null });
      return;
    }
    // 
    const timestamp = Date.now();
    const isLater = audioBlobUrl === 'later';
    // 
    // const audioBlobArr = await fetch(audioBlobUrl).then(r => r.blob());
    const response = (isLater || audioBlobUrl === null) ? {ok: true} : await fetch(audioBlobUrl);
    if (!response.ok) throw new Error('Failed to fetch audio blob');
    if (!isLater && response) {
      const audioBlob = await response.blob();
      const blobName = `${timestamp}-${audioBlobUrl.includes('/') ? 'recording.mp3' : 'recording.wav'}`;
      // 
      // const blobName = `${timestamp}-${audioBlobUrl.includes('/') ? 'recording.mp3' : audioBlobUrl.split('/').pop()}`;
      // const audioBlob = new Blob([audioBlobArr], {
      //   type: 'audio/mpeg',
      // });
      // 
      Object.defineProperty(audioBlob, 'name', {
        value: blobName,
        writable: false
      });
      // 
      setBlobFiles(audioBlob);
    } else {
      // console.log('Unfortunately this has been skipped')
    }

    updateObjRows(currentField, {
      attached: isLater ? { later: true } : {
        blob: `${timestamp}-${audioBlobUrl.includes('/') ? 'recording.mp3' : audioBlobUrl.split('/').pop()}`,
        method: audioBlobUrl.includes('/') ? 'record' : 'upload'
      }
    });
  };

  const renderActionButton = (icon, label, onClick, isActive) => (
    <div className="tb_flex tb_flex-col tb_items-center">
      <button
        onClick={onClick}
        className={`tb_flex tb_items-center tb_justify-center tb_w-16 tb_h-16 tb_rounded-lg 
          ${isActive ? 'tb_border-2 tb_border-primary-500' : 'tb_bg-gray-200'} 
          tb_text-gray-600 tb_shadow-sm`}
      >
        {icon}
      </button>
      <span className="tb_mt-2 tb_text-xs tb_text-gray-600">{label}</span>
    </div>
  );

  const shouldShowWaveform = ![
    BUTTON_STATES.ADD_LATER,
    BUTTON_STATES.SKIPPED,
    BUTTON_STATES.NONE
  ].includes(activeButton);


  return (
    <div className="tb_p-2">
      <div className="tb_space-y-4">
        <div className="tb_grid tb_grid-cols-4 tb_gap-5 tb_justify-items-center">
          {renderActionButton(
            isRecording ? <Square className="tb_w-6 tb_h-6" /> : <Mic className="tb_w-6 tb_h-6" />,
            __('record', 'Record'),
            isRecording ? stopRecording : startRecording,
            activeButton === BUTTON_STATES.RECORDING
          )}
          
          <div className="tb_flex tb_flex-col tb_items-center">
            <label className="tb_cursor-pointer">
              <div className={`tb_w-16 tb_h-16 tb_flex tb_items-center tb_justify-center tb_rounded-lg 
                ${activeButton === BUTTON_STATES.UPLOADED ? 'tb_border-2 tb_border-primary-500' : 'tb_bg-gray-200'}`}>
                <Upload className="tb_w-6 tb_h-6 tb_text-gray-600" />
              </div>
              <span className="tb_mt-2 tb_text-xs tb_text-gray-600 tb_block tb_text-center">{ __('upload', 'Upload') }</span>
              <input
                type="file"
                // accept="audio/*"
                accept=".mp3,.wav,.aac,.m4a,.ogg,.opus,.flac,.alac,.aiff,.amr,.wma"
                onChange={handleFileUpload} className="tb_hidden"
              />
            </label>
          </div>

          {renderActionButton(
            <Mail className="tb_w-6 tb_h-6" />,
            __('add_later', 'Add Later'),
            () => {
              setActiveButton(BUTTON_STATES.ADD_LATER);
              handleVoiceRecord('later');
            },
            activeButton === BUTTON_STATES.ADD_LATER
          )}

          {renderActionButton(
            <SkipForward className="tb_w-6 tb_h-6" />,
            __('skip', 'Skip'),
            () => {
              setActiveButton(BUTTON_STATES.SKIPPED);
              handleVoiceRecord(null);
            },
            activeButton === BUTTON_STATES.SKIPPED
          )}
        </div>

        {recordingStatus && (
          <p className="tb_text-sm tb_text-gray-600 tb_text-center">{recordingStatus}</p>
        )}

        {activeButton === BUTTON_STATES.ADD_LATER && (
          <p
            className="tb_text-sm tb_text-gray-600"
            dangerouslySetInnerHTML={{ __html: __('audiolater_instuction', '1. Receive instructions & button in order email.\n2. Upload audio file anytime later.\n3. We will ship when your audio file is received.').replace(/\\n/g, '<br />') }}
          >
          </p>
        )}

        <div className={`tb_flex tb_items-center tb_gap-4 ${ ! shouldShowWaveform && 'tb_hidden' }`}>
          {audioFile && !isRecording && (
            <button 
              onClick={() => waveAudioRef.current?.[isPlaying ? 'pause' : 'play']()}
              className="tb_w-10 tb_h-10 tb_flex tb_items-center tb_justify-center tb_rounded-full tb_bg-gray-200"
            >
              {isPlaying ? <Pause className="tb_w-5 tb_h-5" /> : <Play className="tb_w-5 tb_h-5" />}
            </button>
          )}
          <div ref={audioContainerRef} className="tb_w-full tb_h-[40px]" id="audio-container-ref" />
          <span className="tb_text-sm tb_text-gray-600">
            {`${Math.floor(timer)}:${('00' + Math.floor((timer % 1) * 1000)).slice(-2)}`}
          </span>
        </div>

        {isRecording && (
          <div className="tb_max-h-36 tb_overflow-y-auto tb_text-sm tb_text-gray-500 tb_mt-4">
            <p>
              {sprintf(__('audioupload_instuction', 'You are permitted to record any message of your liking up to %s seconds, with the exclusion of profanity or copyrighted materials, which are prohibited.'), currentField.duration)}
            </p>
          </div>
        )}

        {activeButton === BUTTON_STATES.SKIPPED && (
          <p
            className="tb_text-sm tb_text-primary-500 tb_text-center"
            dangerouslySetInnerHTML={{ __html: `
              ${ __('rusurenot2advoice', "Are you sure you don't want to add your voice?\nBy clicking on skip, you choose to not have your voice recording").replace(/\\n/g, '<br />') }
            `}}
          >
          </p>
        )}

        {activeButton === BUTTON_STATES.NONE && (
          <p className="tb_text-sm tb_text-primary-500 tb_text-center">
            {__('plsrecvoice', 'Please record your voice.')}
          </p>
        )}
      </div>
    </div>
  );
}