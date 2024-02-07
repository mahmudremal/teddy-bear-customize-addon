import RecordRTC from 'recordrtc';
import WaveSurfer from 'wavesurfer.js';
import RecordPlugin from 'wavesurfer.js/dist/plugins/record.esm.js';
import icons from './icons';


const voiceRecord = {
  meta_tag: 'Voice', recordedBlob: null, duration: 20, rootElement: false, product_id: false,
  init_recorder: (thisClass) => {
    var form, html, config, json;
    voiceRecord.config = thisClass.config;
    voiceRecord.i18n = thisClass.i18n;
    voiceRecord.toastify = thisClass.toastify;
    voiceRecord.popupCart = thisClass.popupCart;
    voiceRecord.popupCart.cartIcon = icons.cart;
    voiceRecord.audioExtensions = 'wav,bwf,raw,aiff,flac,m4a,pac,tta,wv,ast,aac,mp2,mp3,mp4,amr,s3m,3gp,act,au,dct,dss,gsm,m4p,mmf,mpc,ogg,oga,opus,ra,sln,vox'.split(',');
    
    setInterval(() => {
      document.querySelectorAll('.do_recorder:not([data-handled])').forEach((el)=>{
        el.dataset.handled = true;
        voiceRecord.createButtonsAndField(el);
      });
    }, 3500);
    
  },
  createButtonsAndField: function (el) {
    const rootElement = el;
    voiceRecord.rootElement = rootElement;

    // Create record button
    voiceRecord.recordButton = document.createElement('button');
    voiceRecord.recordButton.dataset.cost = ((rootElement.dataset?.cost??0) != '')?(rootElement.dataset?.cost??0):0;
    voiceRecord.recordButton.type = 'button';
    voiceRecord.recordButton.classList.add('do_recorder__record');
    voiceRecord.recordButton.innerHTML = icons.misc + voiceRecord.i18n?.record??'Record';
    // voiceRecord.recordButton.addEventListener('click', voiceRecord.startRecording);
    rootElement.appendChild(voiceRecord.recordButton);

    // Create stop button
    // const stopButton = document.createElement('button');
    // stopButton.textContent = (voiceRecord.i18n?.stop??'Stop');stopButton.type = 'button';
    // stopButton.addEventListener('click', voiceRecord.stopRecording);
    // rootElement.appendChild(stopButton);

    // Create release button
    voiceRecord.playButton = document.createElement('button');
    voiceRecord.playButton.innerHTML = icons.play;
    voiceRecord.playButton.title = voiceRecord.i18n?.play??'Play';
    voiceRecord.playButton.type = 'button';
    voiceRecord.playButton.classList.add('do_recorder__play');
    voiceRecord.playButton.addEventListener('click', voiceRecord.playRecording);
    rootElement.appendChild(voiceRecord.playButton);

    // Download link
    voiceRecord.downloadButton = document.createElement('a');
    voiceRecord.downloadButton.innerHTML = voiceRecord.i18n?.download??'Download';
    voiceRecord.downloadButton.classList.add('button', 'do_recorder__download');
    voiceRecord.downloadButton.style.display = 'none';
    rootElement.appendChild(voiceRecord.downloadButton);

    // Upload Button
    voiceRecord.uploadButton = document.createElement('button');
    voiceRecord.uploadButton.classList.add('button', 'do_recorder__upload');
    voiceRecord.uploadButton.type = 'button';
    voiceRecord.uploadButton.innerHTML = icons.upload + (voiceRecord.i18n?.upload??'Upload');
    voiceRecord.uploadInput = document.createElement('input');
    voiceRecord.uploadInput.type = 'file';
    voiceRecord.uploadInput.accept = 'audio/*;capture=microphone';
    voiceRecord.uploadInput.dataset.cost = rootElement.dataset.cost;
    voiceRecord.uploadButton.addEventListener('click', (event) => {
      voiceRecord.uploadInput.click();
    });
    voiceRecord.uploadInput.addEventListener('change', voiceRecord.uploadAudio);
    voiceRecord.uploadInput.addEventListener('click', (event) => {
      var audioupload_instuction = voiceRecord.i18n?.audioupload_instuction??'You are permitted to record any message of your liking up to %d seconds, with the exclusion of profanity or copyrighted materials, which are prohibited. Please note your recording may be reviewed and screened (discreetly) by our DubiDo staff. We will not modify or edit your recording. In the event of copyright infringement, profanity, hate speech or recordings of the sort, we reserve the right to decline your recording and we will notify you of this decision within 48h of the submission of your recording. You will be given the opportunity to record a new message for additional review. For further information on your rights and privacy, please refer to our Privacy Policy. Please also refer to our Disclaimer for additional information on DubiDo’s liability with regard to recordings.';
      audioupload_instuction = audioupload_instuction.replace('%d', voiceRecord.duration);
      voiceRecord.audioInstructPreview.innerHTML = voiceRecord.nl2br(audioupload_instuction);
      voiceRecord.audioInstructPreview.classList.remove('d-none');
      rootElement.parentElement.parentElement.parentElement.classList.add('audio_instruction');
    });
    rootElement.appendChild(voiceRecord.uploadButton);

    // Create release button
    voiceRecord.releaseButton = document.createElement('button');
    // voiceRecord.releaseButton.innerHTML = voiceRecord.i18n?.ipreferrecordlater??'I prefer to add my voice later';
    voiceRecord.releaseButton.innerHTML = icons.mail + (voiceRecord.i18n?.add_later??'Add Later');
    voiceRecord.releaseButton.type = 'button'; // 'i_dont_prefer_voice',
    voiceRecord.releaseButton.classList.add('do_recorder__release');
    voiceRecord.releaseButton.addEventListener('click', voiceRecord.releaseRecording);
    rootElement.appendChild(voiceRecord.releaseButton);
    
    // Create skip button
    voiceRecord.skipButton = document.createElement('button');
    voiceRecord.skipButton.innerHTML = icons.skip + (voiceRecord.i18n?.skip??'Skip');
    voiceRecord.skipButton.type = 'button';
    voiceRecord.skipButton.classList.add('do_recorder__skip');
    voiceRecord.skipButton.addEventListener('click', (event) => {
      voiceRecord.playButtonAction('hide');
      voiceRecord.rootElement.classList.remove('visible_audio');
      voiceRecord.skipButton.classList.add('do_recorder__skipped');
      voiceRecord.releaseButton.classList.remove('do_recorder__released');
      voiceRecord.popupCart.removeAdditionalPrice(voiceRecord.meta_tag, parseFloat(voiceRecord.recordButton.dataset.cost), false, voiceRecord.product_id);
      voiceRecord.audioInstructPreview.classList.add('d-none');
      voiceRecord.rootElement.parentElement.parentElement.parentElement.classList.remove('audio_instruction');
    });
    rootElement.appendChild(voiceRecord.skipButton);

    // Create audio element for preview
    const audioPreview = document.createElement('audio');
    // audioPreview.controls = true;
    // audioPreview.autoplay = true;
    // audioPreview.playsinline = true;
    audioPreview.style.display = 'none';
    rootElement.appendChild(audioPreview);

    // Instruction for recording audio
    voiceRecord.audioInstructPreview = document.createElement('p');
    voiceRecord.audioInstructPreview.classList.add('audio-record-instruction', 'd-none');
    voiceRecord.audioInstructPreview.innerHTML = '';
    rootElement.appendChild(voiceRecord.audioInstructPreview);

    // Create audio element for preview
    voiceRecord.wavePreview = document.createElement('div');
    voiceRecord.wavePreview.id = 'audio-preview';
    voiceRecord.waveAudioPreview = document.createElement('div');
    voiceRecord.waveAudioPreview.classList.add('audiopreview');
    voiceRecord.waveAudioPreview.appendChild(voiceRecord.playButton);
    var timer = document.createElement('div');timer.classList.add('audiopreview__timer');
    var seconds = document.createElement('span');seconds.classList.add('audiopreview__seconds');
    seconds.dataset.timerType = 's';timer.appendChild(seconds);

    voiceRecord.waveAudioPreview.appendChild(voiceRecord.wavePreview);voiceRecord.waveAudioPreview.appendChild(timer);rootElement.appendChild(voiceRecord.waveAudioPreview);

    voiceRecord.audioPreview = audioPreview;
    voiceRecord.recordedBlob = null;


    window.voiceRecord = voiceRecord;

    voiceRecord.wavesurfer = WaveSurfer.create({
      container: '#'+voiceRecord.wavePreview.id,
      // waveColor: '#de424b',
      // progressColor: '#973137',

      
      waveColor: '#fec52e',
      progressColor: '#e63f51',
      barWidth: 5,
      // barHeight: 1,
      barRadius: 5,
      responsive: true,
      barGap: 1,
      height: 20,
    });
    voiceRecord.record = voiceRecord.wavesurfer.registerPlugin(RecordPlugin.create())
    
    const recButton = voiceRecord.recordButton;
    recButton.onclick = async () => {
      if (voiceRecord.wavesurfer.isPlaying()) {
        voiceRecord.wavesurfer.pause();
      }
      if(voiceRecord.record.isRecording()) {
        voiceRecord.record.stopRecording();
        voiceRecord.playButtonAction('show');
        recButton.innerHTML = icons.misc + voiceRecord.i18n?.record??'Record';
        voiceRecord.playButton.disabled = false;
        voiceRecord.recordButton.dataset.cost = (
          voiceRecord.recordButton.dataset.cost == ''
        )?'0':voiceRecord.recordButton.dataset.cost;
        voiceRecord.popupCart.addAdditionalPrice(voiceRecord.meta_tag, parseFloat(voiceRecord.recordButton.dataset.cost), false, voiceRecord.product_id);
        setTimeout(async () => {
          voiceRecord.recordedBlob = await fetch(voiceRecord.record.getRecordedUrl()).then(r => r.blob());
        }, 800);
        return;
      }
      recButton.disabled = true;
      /**Or I can set on on ** markeed places */
      
      voiceRecord.record.startRecording().then(() => {
        recButton.innerHTML = icons.stop + voiceRecord.i18n?.stop??'Stop';
        recButton.disabled = false;voiceRecord.playButtonAction('hide');
        voiceRecord.skipButton.classList.remove('do_recorder__skipped');
        voiceRecord.releaseButton.classList.remove('do_recorder__released');
        const audiorecord_instuction = voiceRecord.i18n?.audiorecord_instuction??'Please record your voice upto 20 seconds.';
        voiceRecord.audioInstructPreview.innerHTML = voiceRecord.nl2br(audiorecord_instuction);
        voiceRecord.audioInstructPreview.classList.remove('d-none');
        /** **here** */
      })
    }
    
    // Play/pause
    voiceRecord.wavesurfer.once('ready', () => {
      // voiceRecord.playButton.onclick = () => {voiceRecord.wavesurfer.playPause();}
      voiceRecord.wavesurfer.on('play', () => {
        voiceRecord.playButton.innerHTML = icons.pause;
        voiceRecord.playButton.title = voiceRecord.i18n?.pause??'Pause';
      });
      voiceRecord.wavesurfer.on('pause', () => {
        voiceRecord.playButton.innerHTML = icons.play;
        voiceRecord.playButton.title = voiceRecord.i18n?.play??'Play';
      });
      // voiceRecord.wavesurfer.once('interaction', () => {voiceRecord.wavesurfer.playPause()});
      voiceRecord.wavesurfer.on('finish', () => {
        voiceRecord.wavesurfer.setTime(0);
      });
    });
    voiceRecord.record.on('stopRecording', async () => {
      voiceRecord.downloadButton.href = voiceRecord.audioPreview.src = voiceRecord.record.getRecordedUrl();
      voiceRecord.downloadButton.download = await voiceRecord.recordedFileName();voiceRecord.downloadButton.style.display = '';
      if(voiceRecord?.recordInterval) {clearInterval(voiceRecord.recordInterval);}
    });
    voiceRecord.record.on('startRecording', () => {
      voiceRecord.rootElement.classList.add('visible_audio');
      voiceRecord.downloadButton.href = voiceRecord.audioPreview.src = '';
      voiceRecord.downloadButton.download = '';voiceRecord.downloadButton.style.display = 'none';
      let intervalSecond = 0,
      second = voiceRecord.waveAudioPreview.querySelector('[data-timer-type="s"]');
      if(second) {
        second.innerHTML = '';
        voiceRecord.recordInterval = setInterval(() => {
          intervalSecond++;second.innerHTML = (voiceRecord.duration - intervalSecond) + ':00';
        }, 1000);
      }
      setTimeout(() => {
        if(voiceRecord.record.isRecording()) {
          recButton.click();
        }
      }, (voiceRecord.duration * 1000));
    });
    // voiceRecord.record.on('record-start', () => {});
    // voiceRecord.record.on('record-end', () => {});
  },
  playRecording: (type = false) => {
    switch (type) {
      case 'stop':
        voiceRecord.wavesurfer.stop();
        break;
      case 'play':
        voiceRecord.wavesurfer.play();
        break;
      default:
        voiceRecord.wavesurfer.playPause();
        break;
    }
  },
  releaseRecording: () => {
    if(voiceRecord.wavesurfer) {
      // voiceRecord.wavesurfer.destroy()
    }
    voiceRecord.playButtonAction('hide');voiceRecord.playRecording('stop');
    voiceRecord.recordedBlob = null;voiceRecord.audioPreview.src = '';
    voiceRecord.popupCart.addAdditionalPrice(voiceRecord.meta_tag, parseFloat(voiceRecord.recordButton.dataset.cost), false, voiceRecord.product_id);
    // voiceRecord.popupCart.removeAdditionalPrice(voiceRecord.i18n?.voice??'Voice');
    // voiceRecord.releaseButton.innerHTML = icons.tick + voiceRecord.releaseButton.innerHTML;
    voiceRecord.skipButton.classList.remove('do_recorder__skipped');
    voiceRecord.releaseButton.classList.add('do_recorder__released');
    const audiolater_instuction = voiceRecord.i18n?.audiolater_instuction??'1. Receive instructions & button in order email.\n2. Upload audio file anytime later.\n3. we will ship when your audio file is received.';
    voiceRecord.audioInstructPreview.innerHTML = voiceRecord.nl2br(audiolater_instuction);
    voiceRecord.audioInstructPreview.classList.remove('d-none');
    voiceRecord.rootElement.classList.remove('visible_audio');
    voiceRecord.rootElement.parentElement.parentElement.parentElement.classList.remove('audio_instruction');
  },
  recordedFileName: async () => {
    const unique = (new Date()).getTime();const prefix = 'recorded-';
    // Math.floor(Math.random() * (999999999999 - 123 + 1) + 123);

    const mimeType = voiceRecord.recordedBlob?.type;
    const audioMimeTypeRegex = /^audio\//;
    const videoMimeTypeRegex = /^video\//;
    if (audioMimeTypeRegex.test(mimeType)) {
      const extension = mimeType.split('/')[1];
      const suffix = (extension.trim() == '')?'.webm':'.' + extension;return prefix+unique+suffix;
    } else if (videoMimeTypeRegex.test(mimeType)) {
      const extension = mimeType.split('/')[1];
      const suffix = (extension.trim() == '')?'.webm':'.' + extension;return prefix+unique+suffix;
    } else {
      const extension = 'webm';
      voiceRecord.recordedBlob = await new Blob([voiceRecord.recordedBlob], { type: 'audio/webm' });
      const suffix = (extension.trim() == '')?'.webm':'.' + extension;return prefix+unique+suffix;
    }
    // const suffix = (extension.trim() == '')?'.webm':'.' + extension;return prefix+unique+suffix;
  },
  uploadAudio: async (event) => {
    var file = event.target.files[0];
    // Max file size
    if (file) {
      voiceRecord.skipButton.classList.remove('do_recorder__skipped');
      voiceRecord.releaseButton.classList.remove('do_recorder__released');
      if(file.size > (1024 * 1024 * 20)) {
        var text = voiceRecord.i18n?.maxuploadmb??'Max uploaded file size is %s MB.';
        text = text.replace('%s', 20);
        voiceRecord.toastify({text: text,duration: 45000,close: true,gravity: "top",position: "right",stopOnFocus: true,style: {background: 'linear-gradient(to right, rgb(255 180 117), rgb(251, 122, 72))'}}).showToast();
        voiceRecord.recordedBlob = null;
        /**
         * Remove prices on upload voice
         */
        voiceRecord.popupCart.removeAdditionalPrice(voiceRecord.meta_tag, parseFloat(voiceRecord.recordButton.dataset.cost), false, voiceRecord.product_id);
      } else {

        /**
         * Check if file is invalid or something error happens.
         */
        try {
          /**
           * Make some preload before sleep operations.
           */
          voiceRecord.waveAudioPreview.classList.add('loading-file');

          if(! file.type.startsWith('video/') && ! file.type.startsWith('audio/')) {
            throw new Error('File is not audio, nor video.');
          }
          
          voiceRecord.recordedBlob = file;
          const fileURL = URL.createObjectURL(file);
          voiceRecord.audioPreview.src = fileURL;
          await voiceRecord.wavesurfer.load(fileURL);
          voiceRecord.rootElement.classList.add('visible_audio');
          var second = voiceRecord?.waveAudioPreview.querySelector('[data-timer-type="s"]');
          voiceRecord.waveAudioPreview.classList.remove('loading-file');
          /**
           * Reset Input value.
           */
          voiceRecord.uploadInput.value = '';
          
          if(second) {
            voiceRecord.playButtonAction('show');
            second.innerHTML = (voiceRecord.wavesurfer?.duration??0.00)?.toFixed(2).replace('.', ':')??'0:00';
          }
          if((voiceRecord.wavesurfer?.duration??0) > voiceRecord.duration) {
            voiceRecord.playButtonAction('hide');voiceRecord.playRecording('stop');
            var text = voiceRecord.i18n?.audioexcedduration??'Your selected audio file exceed maximum duration of %s sec.';
            text = text.replace('%s', voiceRecord.duration);
            voiceRecord.toastify({text: text,duration: 45000,close: true,gravity: "top",position: "right",stopOnFocus: true,style: {background: 'linear-gradient(to right, rgb(255 180 117), rgb(251, 122, 72))'}}).showToast();

            voiceRecord.wavesurfer.stop();
            voiceRecord.recordedBlob = null;
            // voiceRecord.wavesurfer.destroy();
            voiceRecord.rootElement.classList.remove('visible_audio');
            /**
             * Remove prices on upload voice
             */
            voiceRecord.popupCart.removeAdditionalPrice(voiceRecord.meta_tag, parseFloat(voiceRecord.recordButton.dataset.cost), false, voiceRecord.product_id);
          } else {
            /**
             * Add prices on upload voice
             */
            voiceRecord.popupCart.addAdditionalPrice(voiceRecord.meta_tag, parseFloat(voiceRecord.recordButton.dataset.cost), false, voiceRecord.product_id);
          }
        } catch (err) {
          console.error(err);// err.message is text print
          var text = voiceRecord.i18n?.audiofile_invalid??'Invalid file selected. It seems you didn\'t select a valid audio file or file is not in these following format (%s).';
          text = text.replace('%s', voiceRecord.audioExtensions.join(', '));
          voiceRecord.toastify({text: text,duration: 45000,close: true,gravity: "top",position: "right",stopOnFocus: true,style: {background: 'linear-gradient(to right, rgb(255 180 117), rgb(251, 122, 72))'}}).showToast();
          voiceRecord.waveAudioPreview.classList.remove('loading-file');
        }
      }
    }
  },
  playButtonAction: (type) => {
    switch(type) {
      case 'hide':
        voiceRecord.playButton.style.display = 'none';
        break;
      case 'show':
        voiceRecord.playButton.style.display = 'flex';
        break;
      default:
        break;
    }
  },
  nl2br: (str) => {
    if (typeof str === 'undefined' || str === null) {
      return '';
    }
    return (str + '').replaceAll(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2').replaceAll('\\n', '<br>').replaceAll('\n', '<br>').replaceAll('\ n', '<br>');
  }

};

export default voiceRecord;