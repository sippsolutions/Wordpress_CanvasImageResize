/**
 * Modify plupload to resize images before checking the image size
 */


if(window['plupload']) {

	// Copied from plupload source code as this function is inaccessible
	function resizeImage(blob, params, cb) {
		var img = new mOxie.Image(); // o in source code = mOxie in global

		try {
			img.onload = function() {
				// no manipulation required if...
				if (params.width > this.width &&
					params.height > this.height &&
					params.quality === undef &&
					params.preserve_headers &&
					!params.crop
				) {
					this.destroy();
					return cb(blob);
				}
				// otherwise downsize
				img.downsize(params.width, params.height, params.crop, params.preserve_headers);
			};

			img.onresize = function() {
				cb(this.getAsBlob(blob.type, params.quality));
				this.destroy();
			};

			img.onerror = function() {
				cb(blob);
			};

			img.load(blob);
		} catch(ex) {
			console.log(ex);
			cb(blob);
		}
	}

	// Bit of a hack to actually modify plupload inline
	let oldInit = plupload.Uploader;
	plupload.Uploader = function() {

		oldInit.apply(this, arguments);
		
		// Hook into pluploader here
		if(this['addFile']) {
			let oldAddFile = this.addFile;
			// Override addFile to resize the image first!
			this.addFile = function(files, fileName) {
				let self = this;

				// Always assume arrays are being processed
				if(mOxie.typeOf(files) !== 'array') {
					files = [files];
				}

				mOxie.each(files, function(file){
					// Resize them using pluploader resizer code
					resizeImage.call(this, file, self.settings.resize, function(resizedBlob) {
						// Now pass the new resized file down into the original function which should
						// allow most large images to upload without issues
						oldAddFile.call(self, resizedBlob, fileName);
					});
				});
				
			};
		}

	}
	plupload.Uploader.prototype = oldInit.prototype;


}
