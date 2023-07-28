# Editing the Campus Units Page.
#### (it used to be called Partner, so if u found this word instead of campus unit, dont be surprise. Sorry for any inconvenience : ))

## Add a new Campus Unit:
- Go to Manage >> Content >> Add Content >> Campus Unit
- Title is important for the Campus Unitname (ie so you can search for it in case you need to edit it later), but it will not be displayed on the Campus Unitpage.
- Name: just be same as title
- Destionation: long description for the Campus Unit.  
- URL: the website link of this Campus Unit.
  
## Edit/Delete Campus Unit:
- Go to Manage >> Content
- In the filter, you can search for the title of the Campus Unityou are looking for.  You should also use the 'Content Type' dropdown and change it to 'Campus Unit'.  

## Edit Campus Units Page:  
- Go to Manage >> Content
- In the Filter 'Content Type' dropdown, change it to 'Basic page'.  Then the title is 'Campus Units'  
- Alternatively, on the Campus Units page there is an Edit Button
- The other content is under Main Content, including the Campus Units View.


## Edit Campus UnitContent Type / Node:
- Structure >> Content Types >> Campus Unit

## Edit the Campus Units View:
- The view is the list of Campus Units.
- Structure >> Views >> Campus Units
- The view is mostly structured by custom templating and css.
- The template file is under code/web/themes/ucsbwebrdhsub/templates/node--partner.html.twig and the css is under code/web/themes/ucsbwebrdhsub/css.
- web/themes/ucsbwebrdhsub/ucsbwebrdhsub.theme is also necessary here. If u didnt run into trouble no need to modify this file. Nonetheless, if u run into trouble in twig, go check this file and you will know why. 
- (btw, as started earlier, the file name is partner cuz the view name is partner cuz it was named partner in the past : ))

