# Editing the Learning Resources Page.


## Add a new Learning Resource Post:
- Go to Manage >> Content >> Add Content >> LearningResourcesType
- Title: Enter Name of Learning Resource
- Body: Enter brief description
- Learning Resource Tags: Add any tags for your Learning Resource Post.  If the tag exists, Drupal should display it with its id number in parantheses.  To add multiple tags, click "Add Another Item" button
- - If you want to add a tag to the Learning Resource post, but there is no existing tag of the same name, you will have to create a new tag.  This is very easy and it is showed how to do so below.
- Learning Resource Types: Similar to "Learning Resource Tags," just imput the format of this resource.
- Learning Resource Date: put the date for the Learning Resource document.
- Once the Learning Resource Post is added, you will be redirected to the Drupal Node. This is not navigatable by a user on the website, so you can ignore the formatting.  To view the new Learning Resource Post, you can go to the Learning Resource page.

## Edit/Delete Learning Resource:
- Go to Manage >> Content
- In the filter, you can search for the title of the LearningResourcesType you are looking for. You should also use the 'Content Type' dropdown and change it to 'LearningResourcesType'.  

## Edit Learning Resource Page:  
- In /learning-resources, click Edit and you can edit it like other basic pages.

## Make a new Tag or Type for a Learning Resource post:  
- Go to Manage >> Structure >> Taxonomy >> LR Vocabulary / LearningResourcesTypeVocab >> List Terms >> Add Term
- The Name field is the only field you have to do.  You can optionally add a description, but you shouldn't change anything else.

## Edit Learning Resource View:
- Go to Manage >> Structure >> Views >> LearningResources View
- To edit the views requires a better understanding of drupal and html/css formatting.  There is more documentation under Devs/Getting_Started

## Edit Data Literacy Series Content Type / Node:
- Structure >> Content Types >> LearningResourcesType
 
## Edit twig file code for Learning Resources page:
- Go to web/themes/ucsbwebrdhsub/templates/node--learningresourcestype.html.twig, you can now work on there. Everytime you made a change, if you are in SFTP mode, save the code and in the website, Decel >> Cache clear, and then you can see the difference. For more information, see this repo's DEVS/01_CONTENT_&_CODE.md
- Similarly, go to web/themes/ucsbwebrdhsub/css/style.css for the css changes.
