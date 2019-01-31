#!/bin/bash

# (c) 2019: 975L <contact@975l.com>
# (c) 2019: Laurent Marquet <laurent.marquet@laposte.net>
# @author Laurent Marquet <laurent.marquet@laposte.net>
#
# Script to backup the specified folders via rclone to external computer (SwissBackup - 975L.com)

Folder="/var/www/vhosts/appli-v.net";
Rclone="sb_project_SBI-LM966748:default";

#Synchronizes the appli-v.net folder
rclone sync $Folder/ $Rclone/appli-v.net --skip-links --exclude cache/ --exclude backup/ --exclude dev/ --delete-excluded;

#Copies the backup files (original are still on the server)
rclone copy $Folder/api.appli-v.net/var/backup $Rclone/backup;

######################################### Restore ###############################################
# The computer has to have a copy of the config file located in /root/.config/rclone/rclone.conf
#
# Full instructions available at https://rclone.org/commands/rclone/
#
# Check $Rclone value above
#
# How to restore appli-v.net folder
# rclone sync $Rclone/appli-v.net /path/for/restore/ --progress;
#
# How to list the content of the backup
# rclone ls $Rclone/appli-v.net;
#
# How to list only the directories of the content of the backup
# rclone lsd $Rclone/backup;
#
# How to purge folders
# rclone purge $Rclone/backup/2019/2019-01;
#################################################################################################

exit 0
