#!/bin/bash

# (c) 2019: 975L <contact@975l.com>
# (c) 2019: Laurent Marquet <laurent.marquet@laposte.net>
# @author Laurent Marquet <laurent.marquet@laposte.net>
#
# Script to backup the specified folders via rclone to external computer (SwissBackup - 975L.com)

Hosts="/var/www/vhosts";
Rclone="sb_project_SBI-LM966748:default";

#Synchronizes the appli-v.net folder
rclone sync \
    $Hosts/appli-v.net \
    $Rclone/appli-v.net \
    --skip-links \
    --tpslimit 18 \
    --tpslimit-burst 1 \
    --exclude cache/ \
    --exclude backup/ \
    --exclude dev/ \
    --delete-excluded;

#Synchronizes the energykidsacademy.net folder
rclone sync \
    $Hosts/energykidsacademy.net \
    $Rclone/energykidsacademy.net \
    --skip-links \
    --tpslimit 18 \
    --tpslimit-burst 1 \
    --exclude cache/ \
    --exclude dev/ \
    --delete-excluded;

#Copies the backup files (original are still on the server)
rclone copy \
    $Hosts/appli-v.net/api.appli-v.net/var/backup \
    $Rclone/backup \
    --tpslimit 18 \
    --tpslimit-burst 1;

######################################### Restore ###############################################
# The computer has to have a copy of the config file located in /root/.config/rclone/rclone.conf
#
# Full instructions available at https://rclone.org/commands/rclone/
#
# How to restore appli-v.net folder
# rclone sync sb_project_SBI-LM966748:default/appli-v.net /path/for/restore/ --progress;
#
# How to list the content of the backup
# rclone ls sb_project_SBI-LM966748:default/appli-v.net;
#
# How to list only the directories of the content of the backup
# rclone lsd sb_project_SBI-LM966748:default/backup;
#
# How to purge folders
# rclone purge sb_project_SBI-LM966748:default/backup/2019/2019-01;
#################################################################################################

exit 0
