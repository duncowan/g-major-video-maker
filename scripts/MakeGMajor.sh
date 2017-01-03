#!/bin/bash

if [ -n "$2" ]; then
    cd /var/www/html/GMajor/CreatedVideos
    mkdir $2
    cd $2
fi

if [ -n "$3" ]
then
    echo -e "-----------------------| Downloading video |----------------------\n"
    youtube-dl -f "$3"+140 --newline --no-playlist "$1" -o "raw_video"
else
    echo -e "-----------------------| Downloading video |----------------------\n"
    youtube-dl -f bestvideo+bestaudio --newline --no-playlist "$1" -o "raw_video"
fi
echo "Done."

echo -e "\n-------------------| Getting audio from video |-------------------\n"
ffmpeg -i raw_video.* -vn -ac 2 -ar 44100 raw_audio.wav -hide_banner
echo "Done."

echo -e "\n----------------------| Making audio GMajor |---------------------\n"

echo "===== Part 1 of 7 ====="
cp raw_audio.wav o0.wav
echo "Done."
echo "===== Part 2 of 7 ====="
ffmpeg -i raw_audio.wav -filter:a atempo=1/1.25,asetrate=44100*1.25 ./o1.wav -hide_banner
echo "Done."
echo "===== Part 3 of 7 ====="
ffmpeg -i raw_audio.wav -filter:a atempo=1/1.49,asetrate=44100*1.49 ./o2.wav -hide_banner
echo "Done."
echo "===== Part 4 of 7 ====="
ffmpeg -i raw_audio.wav -filter:a asetrate=44100*0.75,atempo=1/0.75 ./o3.wav -hide_banner
echo "Done."
echo "===== Part 5 of 7 ====="
ffmpeg -i raw_audio.wav -filter:a asetrate=44100*0.50,atempo=1/0.50 ./o4.wav -hide_banner
echo "Done."
echo "===== Part 6 of 7 ====="
ffmpeg -i raw_audio.wav -filter:a atempo=1/1.98,asetrate=44100*1.98 ./o5.wav -hide_banner
echo "Done."

echo "===== Part 7 of 7 ====="
ffmpeg -i o0.wav -i o1.wav -i o2.wav -i o3.wav -i o4.wav -i o5.wav -filter_complex "[0:a][1:a][2:a][3:a][4:a][5:a]amerge=inputs=6[aout]" -map "[aout]" -ac 2 gmajor_audio.wav -hide_banner
echo "Done."
echo "===== DONE ====="

echo "Removing tracks..."
rm o0.wav
rm o1.wav
rm o2.wav
rm o3.wav
rm o4.wav
rm o5.wav
echo "Done."

echo "Removing raw audio file..."
rm raw_audio.wav
echo "Done"

echo "GMajor audio file made!"

echo -e "\n--------------------| Inverting video colours |-------------------\n"
ffmpeg -i raw_video.* -an -vf lutrgb="r=negval:g=negval:b=negval" -c:v libx264 -pix_fmt yuv420p -profile:v main -preset ultrafast inverted_video.mp4 -hide_banner
echo "Done."

echo "Removing raw video file..."
rm raw_video.*
echo "Done."

echo -e "\n-----------| Merging audio and video |----------\n"
rm gmajor_final.mkv
ffmpeg -i inverted_video.mp4 -i gmajor_audio.wav -c:v copy -c:a libfdk_aac -b:a 128k gmajor_final.mp4 -hide_banner
echo "Done."

echo "Removing inverted video and GMajor audio files..."
rm inverted_video.mp4
rm gmajor_audio.wav
echo "Done."

echo -e "\nGMajor video has been made!\n"







