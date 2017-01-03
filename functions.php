<?php
    /**
     *  Disables PHP's and apache's output buffering.
     *
     *  This allows the progress of the g-major video maker to be displayed
     *  to the user in real time.
     *  If output buffering was enabled while the g-major video was being made, 
     *  it would look like the browser couldn't retreve the webpage and the 
     *  user would think the website was broken.
     *
     *  Note: This website was made before I knew about AJAX or WebSockets.
     *        If I was to further develop this website I would most likely
     *        use Node.js and socket.io to send the g-major video maker's 
     *        progress to the user.
     */
    function disable_ob() {
        // Turn off output buffering
        ini_set('output_buffering', 'off');
        // Turn off PHP output compression
        ini_set('zlib.output_compression', false);
        // Implicitly flush the buffer(s)
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);
        // Clear, and turn off output buffering
        while (ob_get_level() > 0) {
            // Get the curent level
            $level = ob_get_level();
            // End the buffering
            ob_end_clean();
            // If the current level has not changed, abort
            if (ob_get_level() == $level) break;
        }
        // Disable apache output buffering/compression
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
            apache_setenv('dont-vary', '1');
        }
    }
    
    /**
     *  Executes the MakeGMajor shell script and sends it's progress to the user.
     */
    function makeVideoGMajor() {
        // Flush and end all output buffers.
        while (@ ob_end_flush());
        
        // Executes the MakeGMajor shell script passing it the YouTube URL,
        // unique id and selected resolution as arguments.
        $cmd = 'scripts/MakeGMajor.sh '.escapeshellarg($_POST['ytUrl']).' '.escapeshellarg($_POST['id']). ' '.escapeshellarg($_POST['res']);
        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output = "";
        $duration = -2;
        $part = "";

        // While the g-major video is being made...
        while(!feof($proc)) {
            $live_output = fread($proc, 4096);
            $match = array();
            
            /** Get the current job and send it to the user. **/
            preg_match("/\|\s(.*)\s\|/", $live_output, $match);
            if(!empty($match)) {
                echo "<script>updateJob('$match[1]');updateProgress('');</script>";
                continue;
            }
            
            /** Get the duration of the video. **/
            preg_match("/\s*Duration:/", $live_output, $match);
            if(!empty($match) && $duration === -2) {
                // Signifies that the next line of live_output
                // will be the duration of the video (should only happen once).
                $duration = -1;
            }
            // Gets the duration of the video and converts it to seconds.
            if($duration === -1) {
                preg_match("/\s*([0-9][0-9]):([0-9][0-9]):([0-9][0-9])/", $live_output, $match);
                if(!empty($match)) {
                    $duration = (($match[1]*60*60)+($match[2]*60)+($match[3]));
                    continue;
                }
            }
            
            /** Get download progress and send it to the user. **/
            preg_match("/\[download\]\s*(1?[0-9]{1,2}\.[0-9]%).*ETA\s*([0-9][0-9]):([0-9][0-9])/", $live_output, $match);
            if(!empty($match)) {
                $prog = ($match[3] > 0 ? " [Time Remaining:".($match[2] > 0 ? " ".(int)$match[2]." minutes" : "")." ".(int)$match[3]." seconds]" : "");
                echo "<script>updateProgress('$prog');</script>";
                continue;
            }
            
            /** Get the part of the job that is currently being processed **/
            preg_match("/={5}\s(.*)\s={5}/", $live_output, $match);
            if(!empty($match)) {
                $part = ($match[1] == "DONE" ? "" : $match[1]."</br>");
                continue;
            }
            
            /** Get the current job's progress and send it to the user. **/
            preg_match("/time=\s*([0-9][0-9]):([0-9][0-9]):([0-9][0-9]).*speed=\s*([0-9]+(?:\.[0-9]+|))/", $live_output, $match);
            if(!empty($match)) {
                $seconds_processed = (($match[1]*60*60)+($match[2]*60)+($match[3]));
                $eta = (($duration-$seconds_processed)*(1/$match[4]))+1;
                $hours = $eta/3600.0;
                $minutes = ($hours - (int)$hours)*60.0;
                $seconds = ($minutes - (int)$minutes)*60.0;
                $prog = (/*(int)(($seconds_processed/$duration)*100)."%".*/($eta >= 1 ? "  [Time Remaining:".((int)$hours > 0 ? " ".(int)$hours." hours" : "").((int)$minutes > 0 ? " ".(int)$minutes." minutes" : "").((int)$seconds > 0 ? " ".(int)$seconds." seconds" : "")."]" : ""));
                echo "<script>updateProgress('$part$prog');</script>";
                continue;
            }
            // Flush system output buffer.
            // Note: Might not be needed.
            @ flush();
        }

        pclose($proc);
    }
    
    /**
     *  Gets the available resolutions (up to 4K) for the submitted YouTube video.
     */
    function getResolutions() {
        $exec_output = array();
        $num_created = 0;
        $html = "";
        
        // Executes the youtube-dl command to get all available resolutions for a video.
        exec("youtube-dl -F ".escapeshellarg($_POST['ytUrl']), $exec_output);
        foreach(array_reverse($exec_output) as $line) {
            // Adds a radio button for every resolution the submitted 
            // video is available in (up to 4K).
            switch(preg_replace('/\s+/', '', explode(" ", $line)[0])) {
                // 144p
                case "160":
                    $html = "<input type='radio' name='res' value='160' ".($num_created++ < 1 ? 'checked' : '').">144p".$html;
                    break;
                // 240p
                case "133":
                    $html = "<input type='radio' name='res' value='133' ".($num_created++ < 1 ? 'checked' : '').">240p".$html;
                    break;
                // 360p
                case "134":
                    $html = "<input type='radio' name='res' value='134' ".($num_created++ < 1 ? 'checked' : '').">360p".$html;
                    break;
                // 480p
                case "135":
                    $html = "<input type='radio' name='res' value='135' ".($num_created++ < 1 ? 'checked' : '').">480p".$html;
                    break;
                // 720p
                case "136":
                    $html = "<input type='radio' name='res' value='136' ".($num_created++ < 1 ? 'checked' : '').">720p".$html;
                    break;
                // 1080p
                case "137":
                    $html = "<input type='radio' name='res' value='137' ".($num_created++ < 1 ? 'checked' : '').">1080p".$html;
                    break;
                // 1440p
                case "264":
                    $html = "<input type='radio' name='res' value='264' ".($num_created++ < 1 ? 'checked' : '').">1440p".$html;
                    break;
                // 4k
                case "266":
                    $html = "<input type='radio' name='res' value='266' ".($num_created++ < 1 ? 'checked' : '').">4k".$html;
                    break;
            }
        }
        echo $html;
    }
    
    // Disable PHP's and apache's output buffering when index.php loads.
    disable_ob();
?>
