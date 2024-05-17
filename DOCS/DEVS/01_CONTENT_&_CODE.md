# Requirements for content editors or developers for the Pantheon/Drupal Research Computing and Data Website

## Content Editors:

### Need to be added as a 'team member' to the Pantheon site.
To add someone as a Team Member, you must also be a team member.  Go to the researchdata-ucsb-edu-v01 site dashboard on Pantheon.  On the top right, there is a "Team" button that you click to then add a team member by their email.  

### Need to be added to the Drupal site.
To add someone on the Drupal website, first login on the Drupal DEV site, then go to Manage >> People.  You can use "Add CAS user" to add a user with their UCSB Net ID so that they can login with their UCSB authorization.  Or you can use the other 'Add User' button to add someone with a custom username and password.  For full permissions for editing the website, give them "Administrator" role.

### Be added to the Github Repository
You will most likely not have to do a lot of work on the Github Repository, but it still may be useful to have the repo so that you can view the DOCS like this one.  Someone must add you to the repo at https://github.com/UCSB-Library-Research-Data-Services/researchdata-ucsb-edu-v01.  For more information on the Github workflow, go to DOCS/DEVS/GITWORKFLOW.md

## Devs:

### Make sure to have everything completed for 'Content Editors' listed above.

### Pantheon SSH Key:
For your computer to have access to make changes to the website, you must create a SSH Key for your computer with your Pantheon account to give your computer your Pantheon account's permissions.  Tutorial of how this is done: https://docs.pantheon.io/ssh-keys.


### Cyberduck and SFTP
Download and install Cyberduck: https://cyberduck.io/.  This is the best way to SFTP into the code for the website to make real=time edits and commits. 

### Visual Studio Code and SFTP
Download and install Visual Studio Code. For extensions, also install SFTP. You might find these links helpful: https://docs.pantheon.io/guides/sftp/vscode-sftp. https://docs.pantheon.io/guides/sftp/sftp-development#commit-sftp-changes. Following the instructions from the links should be stright forward. Now make a little change to check if the connection works well before you move on to the coding or debugging. Once it works, every time you simply save the changed in vs code, you should be able to see the change in Pantheon's website. (Don't forget to clear the cache EVERY TIME, which is very necessary for the changes to show up in Drupal.) Once you are happy with it, end the SFTP mode and swtich to GIT on Pantheon website, and then move to Dev to merge the code. 

In addition of connecting to SFTP, while in the sftp.json file, simply copy the host, protocal, port, username. Then, you should have something similar:
    
    "privateKeyPath": "C:/Users/ychen/.ssh/id_rsa",
    "remotePath": "/code",
    "uploadOnSave": true,
    "useTempFile": false,
    "openSsh": false

You are now all set.

In addition, if you have trouble using your password while connecting Pantheon to your local files, that might be because of your password. If you were using your UCSBNetId or Google account to log in to Pantheon, you might not have had your own Pantheon's password while registering because you were using your UCSBNetId SSO. If you also run into this situation, just go to personal setting => security => forgot my password, and then get a link to your email to set the new password. You can now use your password to connect to cyberduck or visual studio now.

### Visual Studio Code and Git
I recommend using SFTP mode instead of Git, because SFTP mode will save you lots of time in waiting the change in your code to show the change in the web. However, here are the instructions for the git if you still prefer git:
- In terminal of vs code, clone the git to your local environment (if it's from github: click code and copy the address, and then git clone +addressUJustCopied ; if it's from patheon: click clone with git and copy the command line)
- Besides, if u need to remove the old one to restart it (which happended a lot), make sure you already saved the change to somewhere else, such as a new random branch here. and then:  Remove-Item researchdata-ucsb-edu-v01 -Recurse -Force
- now you should be fine with the clone part
- Do the change you need in vs code
- cd "file_name" (open the file)
- git checkout -b "branch_name" (go to the branch to do the modify work. need to make sure that it work well in branch, and then merge it to the master one later to be safe)
- save the files (ctrl S)
- git add . (save it)
- git commit -m "the_message_u_wanna_commit"
- git push origin "branch_name"
- now go to pantheon website, click dev, click merge, and then merge


### Install your favorite text editor
I recommend VSCode. Cyberduck will allow you to open files live on the website into your chosen text editor. You will need this for mostly making CSS styles.

### Optional:
- Terminus is Pantheon's tool for facilitating commands that you can use for managing sites on Pantheon.  It is mostly helpful for installing modules and plugins for Drupal and Pantheon.  Docs: https://docs.pantheon.io/terminus.  An important note is that the only compatible operating systems are: MacOS; Windows 10 – WSL 2 Ubuntu 20.0; Ubuntu 20.0 – this would include Ubuntu under Docker or VirtualBox.  In addition, installing Terminus also requires installing PHP, Composer, and Git.
- For debugging, in addition to vscode, you can also debug the css with the help from chrome browser direcly. Hover to the part you wanna check, right click and go to inspect. For details, visit https://developer.chrome.com/docs/devtools/overview/. Happy coding and good luck!

### CSS Code
All the website's code is under the 'code' directory.  In code/web/themes/ucsbwebrdhsub is where you will make most of your edits.  This is the folder that contains the active sub-theme for the Research Computing and Data Website.  It inherits the UCSB Web theme, then it adds CSS as well as templates to it.  You can go ahead and open the 'styles.css' file with your preferred text editor, and this is all the CSS that is specifically for the Research Computing and Data Website, and it is where you will make all your CSS additions, too. 


#### Twig Templating: 
An important skill will be to know how to use Twig templating.  It is basically just HTML and PHP that you can use to overwrite the default templating that Drupal provides.  Quickly review https://www.drupal.org/docs/theming-drupal/twig-in-drupal/working-with-twig-templates to see how it works, but it will be most helpful to practice when you get to that stage.  https://sarahcodes.medium.com/getting-drupal-8-field-values-in-twig-22b80cb609bd is a great resource for knowing how to access the Drupal values (mostly the field values are important) within Twig. 
- Take a look at code/web/themes/ucsbwebrdhsub/templates/node--community.html.twig which is used for the Community display.  It is a good example of how PHP and HTML is used in Twig to create a custom display for a content type. I recommend playing around with it by opening the file with cyberduck, and making sure you know how everything works in that file.  When you save the file using Cyberduck, you will notice real time changes made to your multidev site on the Communities page after you clear caches.  The best way to clear cache is to use Devel >> Cache Clear on the Drupal toolbar.  If you make a change that crashes the Communities page, that's okay! Just undo the change using Cyberduck, then open a new window of the Research Computing and Data website that is NOT on the communities page, and clear cache again.

### Clear Caches
When editing the code using SFTP mode, the changes will most likely not appear right away because the website is cached.  In order to clear caches, the fastest way to do this is with Devel.  It should be a tab in the Drupal toolbar "Devel" and "Cache Clear" is the first option after you click on Devel.  

### Upstream Update
Swtich to Git if you were in SFTP mode. And then update in Pantheon. Do not update directly in drupal, which will cause the website (that branch) be blank. As usual, update in the test branch to make sure it work well before updating directly in the dev.

### Content Update workflow
In multidev of Pantheon, every developer has their own branch, such as nj-exp5 and yc-12. You will do all the changing in your branch. Once you are happy with the result in your multibranch, go to Pantheon, click "Dev" and as the image shown below, choose your own multidev from the environment, check all four boxes (the third and fourth ones are usually uncheck, but it would make the website work better if we check them too), and then clone. 

<img width="1204" alt="Screenshot 2024-05-16 at 8 47 28 PM" src="https://github.com/UCSB-Library-Research-Data-Services/research-computing-data-docs/assets/91364746/6027c24b-87d5-4e34-a148-9c79eb3dfe18">

After this process is done, Click SIte Admin again and go check if the changing also work well in the Dev. Normally, you are the only one who is working on this project, so it will just show whatever you have done in your branch. However, while working with others, everybody has their own branch, and when you push your contents to the dev, that might cause a conclict and you need to solve it.

After that, doing the same process to clone the contents to the test branch, and if everything works well, clone to the live branch eventually. 

Check in every step to make sure everything looks fine before we make the changes live!

### Code Update workflow
The workflow of code is pretty similar to the content one. First, we commit the change in our multidev branch when we are happy with the result. And then we move to the Dev brnach, click "Merge" and select the branch you were working on as the image shows below.

<img width="1211" alt="Screenshot 2024-05-16 at 8 54 51 PM" src="https://github.com/UCSB-Library-Research-Data-Services/research-computing-data-docs/assets/91364746/76487279-f8d3-4657-969e-a0663fc12100">


Similarly, click "Site Admin" to make sure it work well. And then move to the "Test" tab to Deploy the code from dev branch. 
<img width="1202" alt="Screenshot 2024-05-16 at 8 56 00 PM" src="https://github.com/UCSB-Library-Research-Data-Services/research-computing-data-docs/assets/91364746/82887c47-e392-4e35-977b-125db15fadaf">

If it works well again here, we clone to the live branch too.
