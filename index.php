<!--
    This is the main (and only) web page.
    
    It allows the user to enter the YouTube URL of the video they want to g-major-ify
    and shows the progress of each stage in the conversion. When the conversion completes,
    the final g-major-ified video is displayed.
    
    Note: This website is still under development.
 -->
 
<!-- 
    Include the PHP functions that get the available resolutions for the submitted 
    video and make the g-major video.
 -->
<?php include('functions.php'); ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Online G-Major Video Maker</title>
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <script type="text/javascript" src="javascript/script.js"></script>
        <script>
            /**
             *  Removes any unneeded elements from the last stage and gets
             *  things ready for the next stage.
             */
            function cleanUp() {
                // If the last stage was making the g-major video, 
                // then remove the video conversion progress text so 
                // the final g-major video can be displayed.
                <?php if(isset($_POST['stage']) && $_POST['stage'] == "make") { ?>
                    var cont = document.getElementById("videoConversionProgress");
                    cont.parentNode.removeChild(cont);
                    
                // If the last stage was getting the available resolutions for
                // the submitted video, then clear the "Getting Resolutions..."
                // text from the screen and enable the submit button so the user
                // can select a resolution and submit the video to be converted into g-major.
                <?php } else if(isset($_POST['stage']) && $_POST['stage'] == "get_res") { ?>
                    document.getElementById('sub').disabled = false;
                    document.getElementById('resTitle').innerHTML = "";
                    
                // If there was no last stage (i.e. the when the page is first requested),
                // then enable the submit button. 
                <?php } else { ?>    
                    document.getElementById('sub').disabled = false;
                <?php } ?>
            }
        </script>
    </head>

    <body onload="cleanUp()">
        <!-- 
            If the current stage is making the g-major video, then call the
            makeVideoGMajor PHP function and display it's progress.
            Once the final g-major video is made, display it.
         --> 
        <?php if(isset($_POST['stage']) && $_POST['stage'] == "make") { ?>
            <div id='videoConversionProgress'>
                <p id='currentJob'></p>
                <p id='progress'></p>
                <!-- 
                    Call the PHP function that makes the g-major video
                    and displays it's progress.
                     
                    Note: Any HTML after this function call will only
                          be sent to the user AFTER the g-major video 
                          has been made. This means the g-major video 
                          won't be displayed and the cleanUp function 
                          won't be called while the g-major video is 
                          being made.   
                -->    
                <?php makeVideoGMajor(); ?>
            </div>
            
            <video width="720" height="480" controls>
                <source src=<?php echo "'CreatedVideos/".$_POST['id']."/gmajor_final.mp4'"; ?> type="video/mp4">
            </video>
            
        <!--
            If the current stage is not making the g-major video, 
            then show the form where the user can submit the URL of 
            the YouTube video they want to g-major-ify.
        -->    
        <?php } else { ?>
            <form id="urlForm" action="index.php" method="post" autocomplete="off" onsubmit="return validateForm()">
                <span id="errors"></span></br>
                <input type="url" id="ytUrl" name="ytUrl" placeholder="YouTube URL"
                       <?php echo (isset($_POST['ytUrl']) && $_POST['ytUrl'] != "" ? "value=".$_POST['ytUrl'] : ""); ?> 
                       onpaste="getRes()" onchange="getRes()">
                <input type="hidden" id="stage" name="stage" value="make">
                <input type="hidden" name="id" value=<?php echo uniqid(); ?>>
                <input type="submit" id="sub" value="GO" disabled></br>
                <fieldset id="resolutions">
                    <!-- 
                        If the current stage is getting the available
                        resolutions for the submitted video, then display 
                        the text "Getting Resolutions..." and call 
                        the getResolutions PHP function.
                    -->
                    <legend id="resTitle"><?php echo (isset($_POST['stage']) && $_POST['stage'] == "get_res" ? "Getting Resolutions..." : "") ?></legend>
                    <?php if(isset($_POST['stage']) && $_POST['stage'] == "get_res")
                        /* 
                            Call the PHP function that gets the available
                            resolutions for the submitted video.
                             
                            Note: Any HTML after this function call will only
                                  be sent to the user AFTER the available
                                  resolutions have been retrieved. This means 
                                  the cleanUp function won't be called while 
                                  the resolutions are being retrieved.   
                        */
                        getResolutions();
                    ?>
                </fieldset>
            </form>
        <?php } ?>
    </body>
</html>
