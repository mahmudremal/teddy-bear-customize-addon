import RecordRTC from 'recordrtc';
import WaveSurfer from 'wavesurfer.js';
import RecordPlugin from 'wavesurfer.js/dist/plugins/record.esm.js';

const voiceRecord = {
  
    init_recorder: (thisClass) => {
      var form, html, config, json;
      voiceRecord.popupCart = thisClass.popupCart;
      setInterval(() => {
        document.querySelectorAll('.do_recorder:not([data-handled])').forEach((el)=>{
          el.dataset.handled = true;
          voiceRecord.createButtonsAndField(el);
        });
      }, 3500);
      
    },

    createButtonsAndField: function (el) {
      const rootElement = el;
  
      // Create record button
      voiceRecord.recordButton = document.createElement('button');
      voiceRecord.recordButton.dataset.cost = rootElement.dataset.cost;
      voiceRecord.recordButton.textContent = '🎙️ ' + (voiceRecord.i18n?.record??'Record');voiceRecord.recordButton.type = 'button';
      // voiceRecord.recordButton.addEventListener('click', voiceRecord.startRecording);
      rootElement.appendChild(voiceRecord.recordButton);
  
      // Create stop button
      // const stopButton = document.createElement('button');
      // stopButton.textContent = '⏹ ' + (voiceRecord.i18n?.stop??'Stop');stopButton.type = 'button';
      // stopButton.addEventListener('click', voiceRecord.stopRecording);
      // rootElement.appendChild(stopButton);
  
      // Create release button
      voiceRecord.releaseButton = document.createElement('button');
      voiceRecord.releaseButton.textContent = voiceRecord.i18n?.remove??'Remove';voiceRecord.releaseButton.type = 'button';voiceRecord.releaseButton.style.display = 'none';
      voiceRecord.releaseButton.addEventListener('click', voiceRecord.releaseRecording);
      rootElement.appendChild(voiceRecord.releaseButton);

      // Create release button
      voiceRecord.playButton = document.createElement('button');
      voiceRecord.playButton.textContent = '⏹ ' + (voiceRecord.i18n?.play??'play');voiceRecord.playButton.type = 'button';
      voiceRecord.playButton.addEventListener('click', voiceRecord.playRecording);
      rootElement.appendChild(voiceRecord.playButton);

      // Download link
      voiceRecord.downloadButton = document.createElement('a');
      voiceRecord.downloadButton.textContent = voiceRecord.i18n?.download??'Download';
      voiceRecord.downloadButton.classList.add('button');
      voiceRecord.downloadButton.style.display = 'none';
      rootElement.appendChild(voiceRecord.downloadButton);

      // Upload Button
      voiceRecord.uploadButton = document.createElement('input');
      voiceRecord.uploadButton.type = 'file';
      voiceRecord.uploadButton.accept = 'audio/*;capture=microphone';
      voiceRecord.uploadButton.classList.add('thefileinput');
      voiceRecord.uploadButton.dataset.cost = rootElement.dataset.cost;
      voiceRecord.uploadButton.addEventListener('change', voiceRecord.uploadAudio);
      rootElement.appendChild(voiceRecord.uploadButton);
  
      // Create audio element for preview
      const audioPreview = document.createElement('audio');
      // audioPreview.controls = true;
      // audioPreview.autoplay = true;
      // audioPreview.playsinline = true;
      audioPreview.style.display = 'none';
      rootElement.appendChild(audioPreview);
  
      // Create audio element for preview
      const wavePreview = document.createElement('div');
      wavePreview.id = 'audio-preview';
      rootElement.appendChild(wavePreview);
  
      voiceRecord.audioPreview = audioPreview;
      voiceRecord.recorder = null;
      voiceRecord.recordedBlob = null;


      window.voiceRecord = voiceRecord;

      voiceRecord.wavesurfer = WaveSurfer.create({
        container: '#'+wavePreview.id,
        waveColor: '#de424b',
        progressColor: '#973137',
      });
      voiceRecord.record = voiceRecord.wavesurfer.registerPlugin(RecordPlugin.create())
      
      const recButton = voiceRecord.recordButton;
      recButton.onclick = () => {
        if (voiceRecord.wavesurfer.isPlaying()) {
          voiceRecord.wavesurfer.pause();
        }
      
        if (voiceRecord.record.isRecording()) {
          voiceRecord.record.stopRecording();
          recButton.textContent = 'Record';
          voiceRecord.playButton.disabled = false;
          voiceRecord.recordButton.dataset.cost = (
            voiceRecord.recordButton.dataset.cost == ''
          )?'0':voiceRecord.recordButton.dataset.cost;
          voiceRecord.popupCart.addAdditionalPrice(
            voiceRecord.i18n?.voice_record??'Voice Record',
            parseFloat(voiceRecord.recordButton.dataset.cost)
            );
          return;
        }
      
        recButton.disabled = true
      
        voiceRecord.record.startRecording().then(() => {
          recButton.textContent = '⏹ ' + (voiceRecord.i18n?.stop??'Stop');
          recButton.disabled = false;
        })
      }
      
      // Play/pause
      voiceRecord.wavesurfer.once('ready', () => {
        // voiceRecord.playButton.onclick = () => {}
        voiceRecord.wavesurfer.once('play', () => {
          voiceRecord.playButton.innerHTML = voiceRecord.i18n?.pause??'Pause';
        })
        voiceRecord.wavesurfer.once('pause', () => {
          voiceRecord.playButton.innerHTML = '⏹ ' + (voiceRecord.i18n?.play??'play');
        })
      })
      
      voiceRecord.record.on('stopRecording', () => {
        voiceRecord.downloadButton.href = voiceRecord.audioPreview.src = voiceRecord.record.getRecordedUrl();
        voiceRecord.downloadButton.download = voiceRecord.recordedFileName();voiceRecord.downloadButton.style.display = '';
      })
      voiceRecord.record.on('startRecording', () => {
        voiceRecord.downloadButton.href = voiceRecord.audioPreview.src = '';
        voiceRecord.downloadButton.download = '';voiceRecord.downloadButton.style.display = 'none';
      });
      
    },
  
    playRecording: () => {
      voiceRecord.wavesurfer.playPause();
    },
    startRecording: () => {
      navigator.mediaDevices.getUserMedia({ audio: true })
        .then((stream) => {
          voiceRecord.recorder = new RecordRTC(stream, { type: 'audio' });
          voiceRecord.recorder.startRecording();
        })
        .catch((error) => {
          console.error(voiceRecord.i18n?.erraccessmic??'Error accessing the microphone:', error);
        });
    },
  
    stopRecording: function () {
      if (voiceRecord.recorder) {
        voiceRecord.recorder.stopRecording(function () {
          voiceRecord.recordedBlob = voiceRecord.recorder.getBlob();
          voiceRecord.audioPreview.src = URL.createObjectURL(voiceRecord.recordedBlob);
    
          // Stop and destroy the wavesurfer instance
          if(voiceRecord.wavesurfer) {
            voiceRecord.wavesurfer.stop();
            voiceRecord.wavesurfer.destroy();
          }
    
          // Create a new wavesurfer instance
          voiceRecord.wavesurfer = WaveSurfer.create({
            container: '#audio-preview',
            waveColor: 'gray',
            progressColor: 'black',
            barWidth: 2,
            barHeight: 1,
            responsive: true,
            height: 100,
          });
    
          // Load the recorded audio blob into the wavesurfer
          voiceRecord.wavesurfer.load(voiceRecord.audioPreview.src);

          
          voiceRecord.isPlaying = false;
          voiceRecord.wavesurfer.once('finish', () => {
            voiceRecord.isPlaying = false;
          });
          voiceRecord.wavesurfer.once('interaction', () => {
            if(voiceRecord.isPlaying) {
              voiceRecord.wavesurfer.pause();
              voiceRecord.isPlaying = false;
            } else {
              voiceRecord.wavesurfer.play();
              voiceRecord.isPlaying = true;
            }
          });
        });
      }
    },
  
    releaseRecording: () => {
      if(voiceRecord.recorder) {
        voiceRecord.recorder.destroy();
        voiceRecord.recorder = null;
        voiceRecord.recordedBlob = null;
        voiceRecord.audioPreview.src = '';
      }
      voiceRecord.popupCart.removeAdditionalPrice(voiceRecord.i18n?.voice_record??'Voice Record');
    },
    recordedFileName: () => {
      const unique = (new Date()).getTime();
      // Math.floor(Math.random() * (999999999999 - 123 + 1) + 123);
      const prefix = 'recorded-';const suffix = '.webm';
      return prefix+unique+suffix;
    },

    uploadAudio: (event) => {
      var file = event.target.files[0];
      if (file) {
        const fileURL = URL.createObjectURL(file);
        voiceRecord.audioPreview.src = fileURL;
        voiceRecord.wavesurfer.load(fileURL);
      }
    },

};

export default voiceRecord;