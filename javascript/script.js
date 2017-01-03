/**
 *  Insures that the submitted video is from YouTube.
 */
function validateForm() {
    /* YouTube URL regex from: http://stackoverflow.com/questions/2964678/jquery-youtube-url-validation-with-regex#answer-10315969 */
    var patt = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/i;
    var urlTextbox = document.getElementById('ytUrl');
    
    if(patt.test(urlTextbox.value)) {
        urlTextbox.style.border = "1px solid rgba(0,0,0,0.4)";
        document.getElementById('errors').innerHTML = "";
        return true;
    } else {
        urlTextbox.style.border = "2px solid red";
        document.getElementById('errors').innerHTML = "Enter Valid YouTube URL";
        document.getElementById('resolutions').innerHTML = "<legend id='resTitle'></legend>";
        document.getElementById('stage').value = "";
        return false;
    }
}

/**
 *  Called whenever something is pasted or changed in the URL textbox.
 */
function getRes() {
    // setTimeout of 0 is used to insure that the pasted text
    // is actually in the URL textbox before validating.
    setTimeout(function(){
        if(validateForm()) {
            document.getElementById('stage').value = "get_res";
            document.getElementById('urlForm').submit();
        }
    }, 0);
}

/**
 *  Used by the makeVideoGMajor PHP function to display the job that the
 *  g-major video maker is currently doing (e.g. Downloading video, 
 *  Making audio GMajor, etc).
 */
function updateJob(p) { 
    document.getElementById('currentJob').innerHTML = p;
}

/**
 *  Used by the makeVideoGMajor PHP function to display the time 
 *  remaining for the job that the g-major video maker is currently doing.
 */
function updateProgress(p) { 
    document.getElementById('progress').innerHTML = p; 
    changeColour(); 
}

/**
 *  Adds a random colour filter to the videoConversionProgress
 *  div whenever the current job does something.
 */
function changeColour() {
    var stepSize = 8;
    var rotation = Math.floor(((Math.random() * stepSize) + 1)*(360.0/stepSize));
    document.getElementById('videoConversionProgress').setAttribute('style', '-webkit-filter: hue-rotate('+rotation+'deg); filter: hue-rotate('+rotation+'deg);');
}
