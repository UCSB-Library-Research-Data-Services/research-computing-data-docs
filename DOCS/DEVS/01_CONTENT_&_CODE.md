# Requirements for content editors or developers for the Pantheon/Drupal Research Computing and Data Website

## Idea of Code vs Database / Files
- Before we go to the application, let's learn about the difference first.
- There is a strict distinction between the Code and the Database / Files on Pantheon
- Database / Files refers to any content changes you would make using Drupal (besides for installing modules and updates which requires installing to the code base).  For example, if you add a new Basic Page to the site, this would NOT be in the code, but it would be in the Database/Files
- Code is separate.  The code is what renders all the database / files so that they are presented a certain way. 
- Database / File changes are live.  If you use Drupal to add content, you do not have to push those changes to the site.  You would, however, need to replicate the database/files onto other sites if you want your changes to be seen on another site. More on this below.
- Code changes are not live, and, you have to push to the website to see any changes.  If you are using git, this is done by either pushing your local changes to the github master branch (view DEVS/GITWORKFLOW) or by pushing to any branch using the Pantheon remote git connection.  If you push the code directly to Pantheon, github will not have the code, so you should push to github as well. 
- Code changes with SFTP mode on Pantheon: On Pantheon, if you made code changes with SFTP, then you can push the code using Pantheon's interface.
- Similar to Database / Files, once you push the code to the site, the code changes are ONLY for that site, and so you would have to merge the code over to see it on another site.  For example, if you made code changes to a multidev, but now want your code changes to go to the dev site, you must merge the multidev code into the dev site.
- I will elaborate more below when you actually reach there.

## Content Editors:

### You need those first:

#### Need to be added as a 'team member' to the Pantheon site.
To add someone as a Team Member, you must also be a team member.  Go to the researchdata-ucsb-edu-v01 site dashboard on Pantheon.  On the top right, there is a "Team" button that you click to then add a team member by their email.  

#### Need to be added to the Drupal site.
To add someone on the Drupal website, first login on the Drupal DEV site, then go to Manage >> People.  You can use "Add CAS user" to add a user with their UCSB Net ID so that they can login with their UCSB authorization.  Or you can use the other 'Add User' button to add someone with a custom username and password.  For full permissions for editing the website, give them "Administrator" role.

#### Be added to the Github Repository
You will most likely not have to do a lot of work on the Github Repository, but it still may be useful to have the repo so that you can view the DOCS like this one.  Someone must add you to the repo at https://github.com/UCSB-Library-Research-Data-Services/researchdata-ucsb-edu-v01. 

### During the editing:
- When you use drupal to add content / edit pages etc., the database/files are automatically updated on the site that you are making edits on.  This means that you do NOT have to make any code pushes etc.  The changes you make are live for the site that you are editing.
- Changes you make on drupal are only seen for YOUR site.  For example, if you add content to the Dev site, that content is not yet on the Test, multidevs or Live sites.  Furthermore, if you add content to a MultiDev site, that content will not be seen on any other multi-dev nor the dev site etc.
- (This one might seem irreverent, but it took a while for me so I wanna document it for future reference) For changing the names in the navigation bar, you might notice that when you hover on the nav bar, there is a pencil (edit) part in the very right, but when you move to there, it is gone. Try right click in a random name in nav bar, and then go to the right side to click the pencil. In this case, you can edit it easily.




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

### Code

#### CSS Code
All the website's code is under the 'code' directory.  In code/web/themes/ucsbwebrdhsub is where you will make most of your edits.  This is the folder that contains the active sub-theme for the Research Computing and Data Website.  It inherits the UCSB Web theme, then it adds CSS as well as templates to it.  You can go ahead and open the 'styles.css' file with your preferred text editor, and this is all the CSS that is specifically for the Research Computing and Data Website, and it is where you will make all your CSS additions, too. 


#### Twig Templating: 
An important skill will be to know how to use Twig templating.  It is basically just HTML and PHP that you can use to overwrite the default templating that Drupal provides.  Quickly review https://www.drupal.org/docs/theming-drupal/twig-in-drupal/working-with-twig-templates to see how it works, but it will be most helpful to practice when you get to that stage.  https://sarahcodes.medium.com/getting-drupal-8-field-values-in-twig-22b80cb609bd is a great resource for knowing how to access the Drupal values (mostly the field values are important) within Twig. 
- Take a look at code/web/themes/ucsbwebrdhsub/templates/node--community.html.twig which is used for the Community display.  It is a good example of how PHP and HTML is used in Twig to create a custom display for a content type. I recommend playing around with it by opening the file with cyberduck, and making sure you know how everything works in that file.  When you save the file using Cyberduck, you will notice real time changes made to your multidev site on the Communities page after you clear caches.  The best way to clear cache is to use Devel >> Cache Clear on the Drupal toolbar.  If you make a change that crashes the Communities page, that's okay! Just undo the change using Cyberduck, then open a new window of the Research Computing and Data website that is NOT on the communities page, and clear cache again.


#### Javascript
Only use Javascript if you have exhausted every other option.  Try to use twig templating, installing modules, or other traditional Drupal methods before trying to use Javascript.  I have not tested the cost that additional scripts have on the website's speed, so try to use Javascript sparingly unless you have discovered optimizations with Javascript.  

- Add a Javascript file to render for only a single page: First, navigate to the code for the web subtheme.  (Open SFTP Connection with Cyberduck, navigate to the code >> web >> themes >> ucsbwebrdhusb)

- An example of how to do this is already here: the files you need to look at for an example are: ucsbwebrdhsub.theme, js/data-literacy-series.js, ucsbwenrdhsub.libraries.yml.

- How it works:
  First, look at what's in js/data-literacy-series.js.  My code is wrapped in two function definitions: The first one starts with
  ```javascript
  "\(function ($){...}"
  ```
  You can think of this as a requirement of drupal for you to wrap all your code in this function in order for Drupal to use your script.  Then inside that function is another definition: 
  ```javascript
  $(document).ready(function() {....})"
  ```
  This is defined by Drupal for to have your script render after the document is rendered.  In this example, after the document is rendered, the script finds all anchor tags of a certain description, and sets their attributes so that the link on the anchor tag opens in a new page.
- Finally, take a look at ucsbwebrdhsub.theme.  There is a preprocessing function for the website that has a conditional that searches for our page with title "Data Literacy Series," and then it adds the library we created to the library that renders for that page.  

- How to implement your own: First, create a new javascript file in the js folder, following the template of  js/data-literacy-series.js. Next, create your own library that has uses the new javascript file you created. Finally, in  ucsbwebrdhsub.theme, you can copy and paste a conditional just like the one already in there inside the same function.  Just make sure to change the title to the Title that your drupal page has,  and change the library name appropriately. 

- Further examples of using javascript: You can take a look at the code in the ucsbwebsub subtheme folder for more complex examples of rendering javascript for Drupal.













### Clear Caches
When editing the code using SFTP mode, the changes will most likely not appear right away because the website is cached.  In order to clear caches, the fastest way to do this is with Devel.  It should be a tab in the Drupal toolbar "Devel" and "Cache Clear" is the first option after you click on Devel.  

### Upstream Update
Swtich to Git if you were in SFTP mode. And then update in Pantheon. Do not update directly in drupal, which will cause the website (that branch) be blank. As usual, update in the test branch to make sure it work well before updating directly in the dev.

If there is an error when updating, look for the error message, e.g.:

Error message: "Merge conflict: Merge conflict detected:\nCONFLICT (content): Merge conflict in composer.lock".

The type of error will be enclosed in the parantheses(content), and you can troubleshoot from there.

### Content Update workflow
In Pantheon, a content-down code-up approach is followed, meaning that you will make ALL content updates DIRECTLY in the live branch. You should use the preview function to test how the changed content appears and ensure quality before saving. Additionally, content-down refers to the fact that when you alter the code, you will always clone the database/files from the live branch down to the branch you are working on (whether it be dev, multidev, or test).

Example of content-down:
- <img width="1176" height="608" alt="Screenshot 2025-08-06 at 11 09 02 AM" src="https://github.com/user-attachments/assets/34a4968b-59da-49ad-9fcc-0bb5caeedf0d" />

The reason for this approach is because when multiple developers are working on the site, having each developer change the sites content on their own multi-devs / developers site, and then proceed to clone upwards into live would almost certainly result in loss of content due to overwrites. Following this approach is best practice. 


### Code Update workflow
As mentioned above, Pantheon follows a content-down code-up philosophy, meaning that any code changes will start from the bottom, e.g.

First, we commit the change in our multidev branch when we are happy with the result. And then we move to the Dev branch, click "Merge" and select the branch you were working on as the image shows below.

<img width="1211" alt="Screenshot 2024-05-16 at 8 54 51 PM" src="https://github.com/UCSB-Library-Research-Data-Services/research-computing-data-docs/assets/91364746/76487279-f8d3-4657-969e-a0663fc12100">


Similarly, click "Site Admin" to make sure it work well. And then move to the "Test" tab to Deploy the code from dev branch. 
<img width="1202" alt="Screenshot 2024-05-16 at 8 56 00 PM" src="https://github.com/UCSB-Library-Research-Data-Services/research-computing-data-docs/assets/91364746/82887c47-e392-4e35-977b-125db15fadaf">

If it works well again here, we clone to the live branch too.

### Accessibility Testing / Broken Links
UCSB sites use the tool SiteImprove to check for broken links and test for accessibility. In order to gain access to this service, you will follow the instructions and fill out the form as explained [here](https://webguide.ucsb.edu/accessibility/siteimprove).
