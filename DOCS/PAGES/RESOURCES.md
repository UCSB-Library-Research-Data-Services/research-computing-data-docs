# Add Resources to the Resource Page

## Add a Resource
- Go to Manage >> Content >> Add Content >> Resource
 

## Edit/Delete Community:
- Go to Manage >> Content
- In the filter, you can search for the title of the Community you are looking for.  You should also use the 'Content Type' dropdown and change it to 'Resource'.  

## Edit the Resource View:
- Go to Manage >> Structure >> Views >> Resources >> Edit
- Editing this may require a deeper understanding of Drupal.  I reviewed how I have structured this view in https://github.com/UCSB-Library-Research-Data-Services/researchdata-ucsb-edu-v01/blob/master/DOCS/DEVS/GETTING_STARTED.md in the section "Create Custom Display using views".  

## Change Resource View into a Basic Page
Note that the the Resource Page is not a content type of "Basic Page," but it is Drupal's View.  This means you can only add or remove resources from it, but you cannot add text or anything else to the page.  In order to change the Resource Page into a Basic Page instead of a view (ie so you can make edits to it like adding text above the resource list):
- Create a new content of Basic Page, and add the Resource View to the page.  Then you can add other blades to the page while including the resource view.  
- Note that the Resource view is currently taking path /resources, so you may have to change this if you want a Basic Page instead.
