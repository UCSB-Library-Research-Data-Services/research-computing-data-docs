# Pantheon Workflow 

## Drupal modules installation / extension

If you are installing a new module on drupal, then make sure that the website is in SFTP mode on Pantheon

## Multi-dev
- I recommend having a multi-dev when making any changes to the website.  This is so that if your changes crash or ruin the website, you can just create a new multi-dev and delete the old one.
- Merging the code from multi-dev to dev should be self-explanatory.  But note that the Database/Files are not transferred over when you merge the multi-dev into dev.  For example, if you created a new content type in the multi-dev, then it will not show up in the dev.  To transfer these files, go to the Dev Data / Files >> Clone tab and clone from your multi-dev environment.  

## Backups / Crashes
- If your dev or multi-dev site ever crashes, just go to the backups tab and restore one of the backups.
- You should be making sure that scheduled Backups are always running on dev for this reason, and you can create a manual backup if necessary.
- Create backups in git mode.

## Status Errors and Updates
- From time to time there may be errors that show up in "System Status."  Common ones include: Public files directory / Error / Not fully protected and Webform: External libraries / Error / 19 libraries (2 installed; 1 excluded; 16 CDN)
- These ones are usually harmless, and they are fixed once upstream updates appear for the version of Drupal 9 / UCSB Web Theme (in the Code Tab of Panbtheon).  Once these updates are ready, and after you install them, they will update the drupal core for you and usually fix the System Status errors.  
- For this reason, you should not have to worry about updating the Drupal Core manually, even if you get an error telling you to do so.  I have had problems before where the drupal would not install the updates correctly.  Usually it is just better to wait for the upstream updates to appear for new version of  Drupal 9 / UCSB Web Theme, and installing those updates will update the Drupal Core for you. 