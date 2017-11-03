## =============================================================================
#  Duckietown @ TTIC - Surveillance Video - Post-Processing #1
## =============================================================================
#
#   Maintainer: Andrea F. Daniele
#               Toyota Technological Institute at Chicago
#               October 2017
#               afdaniele@ttic.edu
#
#   This script receives an input path inputPath (string) and an output path outputPath (string).
#   It reads all the files matching the pattern "%Y-%m-%d_%H.%M.%S.mp4" in inputPath in ascending temporal order.
#   For each video file in input, the script extract `process_frames_per_sec` per second.
#   For each couple of consecutive frames (f_t_1, f_t), the normalized difference in the pixel space is computed
#   and stored as the probability of observing a motion between the time t_1 and t. The activity history
#   is stored as a CSV file (one for each file in inputPath) in outputPath.
#
## =============================================================================

# define script parameters
process_frames_per_sec = 1
detection_min_area = 400
video_fps = 25
compare_frame_with_x_previous_frames = 1
thumbnail_records_per_video = 4
skip_initial_broken_frames = 5 * video_fps   # skip first 5 seconds

# define helper functions
def str2bool(v):
    if v.lower() in ('yes', 'true', 't', 'y', '1'):
        return True
    elif v.lower() in ('no', 'false', 'f', 'n', '0'):
        return False
    else:
        raise argparse.ArgumentTypeError('Boolean value expected.')

# define arguments
import argparse
parser = argparse.ArgumentParser(description="Surveillance Video - Post-Processing #1: Performs \
motion detection on a sequence of videos.")
parser.add_argument('inputPath', type=str, help='The input path containing the MP4 files to process')
parser.add_argument('outputPath', type=str, help='The destination path where to save the CSV files')
parser.add_argument('maskFile', type=str, help='The mask file to use for filtering out unwanted motions')
parser.add_argument('showWindow', type=str2bool, nargs='?', const=False, default=False, help='Whether to show a window with the video and the motion detected')

# read input arguments
args = parser.parse_args()
inputPath = args.inputPath
outputPath = args.outputPath
maskImagePath = args.maskFile
showWindow = args.showWindow

# check input path
from os.path import isdir, isfile
if not isdir(inputPath):
    print('The path "%s" does not exist. Please check and retry. The program will now close.' % inputPath)
    exit(1)

if not isdir(outputPath):
    print('The path "%s" does not exist. Please check and retry. The program will now close.' % outputPath)
    exit(2)

if not isfile(maskImagePath):
    print('The mask image "%s" does not exist. Please check and retry. The program will now close.' % maskImagePath)
    exit(3)

# get list of files
from re import match
from os import listdir
inputFiles = [
    filename for filename in listdir( inputPath )
    if  isfile( "%s/%s" % ( inputPath, filename ) ) and
        match( r'^web_\d{4}-\d{2}-\d{2}_\d{2}\.\d{2}\.mp4$', filename )
]
if( len(inputFiles) == 0 ):
    print 'Nothing to process. Exiting...'
    exit(0)
date = inputFiles[0].split('_')[1]
print 'Date: %s' % date
print '%d files to process.' % len(inputFiles)

# initialize libraries
import cv2
import datetime
import time
import numpy as np
import json

# load mask
maskImage = cv2.imread( maskImagePath, cv2.IMREAD_GRAYSCALE )
maskImage = cv2.bitwise_not ( maskImage, maskImage )

# create placeholder objects
day_thumbnail = {
    "date" : date,
    "fps" : video_fps,
    "total_frames" : 0,
    "thumbnails" : {}
}
camera = None
skip_frame_factor = float(video_fps) / float(process_frames_per_sec)
lastFrames = []
for videoName in inputFiles:
    videoPath = "%s/%s" % ( inputPath, videoName )
    hour = videoName.split('_')[2].split('.mp4')[0]
    camera = cv2.VideoCapture( videoPath )
    video_details = {
        "name" : videoName,
        "fps" : video_fps,
        "total_frames" : 0,
        "motion" : {
            "frame" : [],
            "activity" : [],
            "bbox" : {}
        }
    }
    # loop over the frames of the video
    i = 0
    thumb_last_frame = 0
    while True:
        i += 1
    	# grab the current frame and initialize the occupied/unoccupied text
    	(grabbed, frame) = camera.read()
    	text = "Unoccupied"
        # if the frame could not be grabbed, then we have reached the end of the video
    	if not grabbed:
    		break

        # skip first skip_initial_broken_frames frames
        if i < skip_initial_broken_frames:
            if i % skip_frame_factor == 0:
                # store no motion'
                video_details['motion']['frame'].append( i )
                video_details['motion']['activity'].append( 0 )
            continue

        # skip if it is not a frame of interest
        if i % skip_frame_factor != 0:
            continue

        # keep a copy of the original frame to show in a window
        frame_unmask = None
        if showWindow:
            frame_unmask = frame.copy()

        # apply mask
        frame = cv2.bitwise_and(frame, frame, mask=maskImage)

        # convert the frame to grayscale, and blur it
    	gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    	gray = cv2.GaussianBlur(gray, (21, 21), 0)

    	# if the first frame is None, initialize it
    	if len(lastFrames) == 0:
            lastFrames = [ gray ]
            continue

        # compute the absolute difference between the current frame and the last frames
        thresh = np.zeros( gray.shape )
        for j in range(1, compare_frame_with_x_previous_frames+1, 1):
            frameDelta = cv2.absdiff(lastFrames[-j], gray)
            thresh = cv2.threshold(frameDelta, 25, 255, cv2.THRESH_BINARY)[1]

            # dilate the thresholded image to fill in holes
            thresh += cv2.dilate(thresh, None, iterations=2)

        # find contours on thresholded image
    	img, cnts, hierarchy = cv2.findContours(thresh.copy(), cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

    	# loop over the contours
        cnts_num = 0
    	for c in cnts:
            # if the contour is too small, ignore it
            if cv2.contourArea(c) < detection_min_area:
                continue

            cnts_num += 1

            # compute the bounding box for the contour
            (x, y, w, h) = cv2.boundingRect(c)

            # draw it on the frame, and update the text
            if showWindow:
                cv2.rectangle(frame, (x, y), (x + w, y + h), (0, 255, 0), 2)
                cv2.rectangle(frame_unmask, (x, y), (x + w, y + h), (0, 255, 0), 2)
                text = "Occupied"

            # add the bounding box to the video details
            if i not in video_details['motion']['bbox']:
                video_details['motion']['bbox'][i] = []
            video_details['motion']['bbox'][i].append( [x, y, w, h] )

        # store info about the current frame
        video_details['motion']['frame'].append( i )
        video_details['motion']['activity'].append( cnts_num )

        if showWindow:
            # draw the text and timestamp on the frame
            cv2.putText(frame, "Room Status: {}".format(text), (10, 20), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 255), 2)
            cv2.putText(frame_unmask, "Room Status: {}".format(text), (10, 20), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 255), 2)

            # show the frame and record if the user presses a key
            cv2.imshow("Thresh", thresh)
            cv2.imshow("Frame Delta", frameDelta)
            cv2.imshow("Security Feed (masked)", frame)
            cv2.imshow("Security Feed", frame_unmask)

            key = cv2.waitKey(1) & 0xFF

            # if the `q` key is pressed, break from the loop
            if key == ord("q"):
                break

        lastFrames.append( gray )
        lastFrames = lastFrames[ -compare_frame_with_x_previous_frames : ]

    # cleanup the camera
    camera.release()

    # close any open windows
    if showWindow:
        cv2.destroyAllWindows()

    # complete the video descriptor
    video_details['total_frames'] = i
    day_thumbnail['total_frames'] += i

    # create thumbnail for this video
    video_sections_idx = np.array_split( video_details['motion']['frame'], thumbnail_records_per_video )
    video_sections_val = np.array_split( video_details['motion']['activity'], thumbnail_records_per_video )
    thumbnails_idx = [ np.mean(s) for s in video_sections_idx ]
    thumbnails_val = [ np.max(s) for s in video_sections_val ]
    day_thumbnail['thumbnails'][hour] = {
        'frame' : thumbnails_idx,
        'activity' : thumbnails_val
    }
    # store per video activity log
    video_log_file = '%s/%s_%s.json' % ( outputPath, date, hour )
    with open(video_log_file, 'w') as outfile:
        json.dump(video_details, outfile)


# store per day thumbnail
day_log_file = '%s/thumbnail.json' % ( outputPath )
with open(day_log_file, 'w') as outfile:
    json.dump(day_thumbnail, outfile)
