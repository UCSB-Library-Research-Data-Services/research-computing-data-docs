# Editing the Data Literacy Series Page.


## Add a new DLS Post:
- Go to Manage >> Content >> Add Content >> DLS Post
- Title: Enter Name of DLS Post
- Body: Enter brief description, also a Perma Link (for more details about perma links, check below)
- DLS Tags: Add any tags for your DLS Post.  If the tag exists, Drupal should display it with its id number in parantheses.  To add multiple tags, click "Add Another Item" button
- - If you want to add a tag to the DLS post, but there is no existing tag of the same name, you will have to create a new tag.  This is very easy and it is showed how to do so below.
- DLS MEDIA PDF: This is the main PDF that will have its thumbnail displayed.  Click Add Media -> Choose File Then after uploading your file, you should be able to select the thumbnail for the PDF you uploaded.
- DLS File: Upload the alternative PDF file if there is one.
- DLS Date: put the date for the DLS document.
- Notes: DLS Media PDF is for adding the PDF that will be displayed in the post's thumbnail.  It will ask you to add or select the PDF.  You will first need to add the PDF, then it will appear for you to be able to select it.  Then, the DLS File input is for the alternate PDF file if there is one.  
- Once the DLS Post is added, you will be redirected to the Drupal Node.  This is not navigatable by a user on the website, so you can ignore the formatting.  To view the new DLS Post, you can go to the DLS page.

## Edit/Delete DLS Post:
- Go to Manage >> Content
- In the filter, you can search for the title of the DLS Post you are looking for.  You should also use the 'Content Type' dropdown and change it to 'DLS Post'.  

## Edit DLS Page:  
- The traditional Basic Page Edit for the DLS page is broken.  You must edit it this way:
- On the right hand side of the page, there is a gray Pencil icon.  If you do not see it, then go to the Drupal Tool Bar on the top, and click the "Edit" button on the far right of the toolbar.
- Click the pencil icon, then click "Quick Edit"
- Select where you want to edit.  For the DLS Page, you can edit the title and the starting text. 
- When you click the text of the page, there are two blades: the "Content" and the "View".  The view is the list of DLS posts that you have to edit a different way (below).
- Click on "Content" dropdown inside "Blades"
- Click the blue "Edit" button directly above it to edit it. 
- There will be 'columns' - drupal's way of organizing information even though there is only one column. Click the blue Edit button for the column.
- If you want to edit the HTML, click "Source"
- After making changes, make sure to save.

## Make a new Tag for a DLS post:  
- Go to Manage >> Structure >> Taxonomy >> DLS Vocabulary >> List Terms >> Add Term
- The Name field is the only field you have to do.  You can optionally add a description, but you shouldn't change anything else.

## Edit DLS View:
- Go to Manage >> Structure >> Views >> Data Literacy Series
- To edit the views requires a better understanding of drupal and html/css formatting.  There is more documentation under Devs/Getting_Started

## Edit Data Literacy Series Content Type / Node:
- Structure >> Content Types >> DLS Post

## Behind the Scenes: How the DLS Page is Built
- Everything on the DLS page comes from one of two places.  Knowing which one saves you from digging in the wrong spot:
- - The **Views exposed form** (edited in the Drupal admin, no code): the "Search Title" box, the "Sort" dropdown, the "Filter by Topic" box, and the list of posts itself.
- - A **JavaScript file** (code, not admin): the extra behavior layered on top - renaming "Advanced Search" to "Filter by Tag", the "Or browse all topics" dropdown, opening thumbnails in a new tab, and making each post's tags clickable.
- The JavaScript lives at code/web/themes/ucsbwebrdhsub/js/data-literacy-series.js .  It only runs on DLS pages: the theme file (ucsbwebrdhsub.theme) has a conditional that checks for the "Data Literacy Series" page and any path starting with /data-literacy-series/, and only then loads the script.  See DEVS/01_CONTENT_&_CODE for the general pattern of attaching a JS file to a single page.

## Changing the Search Bar, Sort, and Filter Text (Views)
- These texts are NOT in the code - they are labels on the view, changed in the admin UI.  Go to Manage >> Structure >> Views >> Data Literacy Series.
- "SEARCH TITLE" label and the search box: this is an exposed **filter criterion** on the title.  Click that filter, and its label is the "Filter identifier" / exposed label field.
- "SORT" dropdown and its options ("Newest First", etc.): these come from the exposed **sort criteria**.  Click the sort, and you can rename each option label.
- "Filter by Topic:" box: this is the exposed filter on the DLS tags (its internal name is `dls_tags_advanced_id` - that name matters, see below).  Its label is set here too.
- "Search the DLS handouts:" heading: this is page/header text, not a form label.  Edit it with Quick Edit (see "Edit DLS Page" above); if you can't find it there, check the view's **Header** section.
- Reminder from "Edit DLS View" above: changing the view itself (adding fields, changing what shows) needs a real understanding of Drupal Views + HTML/CSS.  Renaming a label is safe; rearranging the view is not a first-week task.

## Filter by Tag
- This is the toggle that opens the "Filter by Topic" box and the "Or browse all topics" dropdown.  It's a mix of a built-in Views feature and custom JavaScript, so here is what is really happening:
- Drupal/Views calls this toggle "Advanced Search" by default.  The JS simply **renames the button text** to "Filter by Tag" (the `dlsAdvancedSearchLabel` behavior).  If you ever want a different name, change the string 'Filter by Tag' in that behavior.
- When you open it, the "Filter by Topic:" box is a type-ahead (autocomplete) that comes straight from the Views exposed filter - that part is Drupal, not us.
- The JS then adds a **second control underneath**: the "Or browse all topics:" dropdown that lists every tag, so a user can pick one instead of typing.  Its hint text "Or browse all topics:" is set in the `buildDropdownBlock` function.
- Both the type-ahead and the dropdown do the exact same thing: they send you to the results filtered by the tag you chose.  Under the hood the tag name is put in the URL, e.g. `/data-literacy-series/search-view?dls_tags_advanced_id=Citation`, and Views filters the list by that tag.  This is why the filter's internal name `dls_tags_advanced_id` must stay the same in both the view and the JS - it's the link between them.
- Where the tag list comes from: the JS asks Drupal for every tag in the DLS tag vocabulary (through Drupal's JSON:API).  If that request ever fails, it falls back to just the tags already visible on the page, so the dropdown still works.
- Each post's little tag links are also rewritten by the JS to use this same filter, so clicking a tag on a post filters the list the same way the dropdown does.
- - If the dropdown ever fills with the wrong set of tags (e.g. tags from a different vocabulary), open the top of data-literacy-series.js and set the `TOPIC_VOCABULARY` value to the DLS tag vocabulary's machine name.  It's left blank on purpose so it auto-detects, but that's the override switch.
- - A couple of the behaviors in that file (the "collapse no fade" and the click handler at the top) exist only to fix Drupal/Bootstrap display quirks with this panel - if you touch them, read the comments above each one first; they explain exactly what breaks without them.

## URL problems in the thumbnail
- When clicking the thumbnail images, it will guide us to somewhere such as https://rcd.ucsb.edu/sites/default/files/2024-01/DLS-202401-Binder-navy.pdf.
- If the url doesn't start with https://rcd.ucsb.edu/sites, rebuild the site's cache and force the setting of any new database queries.
- If it doesn't work, then try see if any aggregation is turned on/off and re-configure that, especially if the module or theme uses a lot of javascript calls.
- If that still not working, it could be due to the module's code not properly redirecting the links once the page is saved.

## Perma Links
- Go to https://perma.cc/manage/create?folder=147717-227797, select UCSB Research Data Services -> Data Literacy Series - live branch
- Clone your latest content to the live branch. Clicking the image to go to the pdf of that file in the live branch, then copy the url and input to perma and click "create perma link."
- Make this perma link you just created visible to public.
- Copy the url of this perma link back to the content of that data literacy in the multi branch you were working on
- Clone to live branch again and you are now all set

