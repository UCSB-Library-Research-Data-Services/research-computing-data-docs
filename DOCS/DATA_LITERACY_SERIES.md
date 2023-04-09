# Editing the Data Literacy Series Page.

# Add a new DLS Post:
- Go to Manage >> Content >> Add Content >> DLS Post
- Notes: DLS Media PDF is for adding the PDF that will be displayed in the post's thumbnail.  It will ask you to add or select the PDF.  You will first need to add the PDF, then it will appear for you to be able to select it.  Then, the DLS File input is for the alternate PDF file if there is one.  

# Edit/Delete DLS Post:
- Go to Manage >> Content
- In the filter, you can search for the title of the DLS Post you are looking for.  You should also use the 'Content Type' dropdown and change it to 'DLS Post'.  

# Edit DLS Page:  
- Go to Manage >> Content
- In the Filter 'Content Type' dropdown, change it to 'Basic page'.  Then the title is 'Data Literacy Series.'  

# Make a new Tag for a DLS post:  
- Go to Manage >> Structure >> Taxonomy >> DLS Vocabulary >> List Terms >> Add Term
- Once you add the term.  You must find the term ID. To find the term ID, go back to DLS Vocabulary >> List Terms, find the term you just added, and click on it to view it.  In the URL, it should have an ending path of  "taxonomy/term/{term id}".  You should just copy this whole path.
- Then, go to Manage >> Configuration >>  URL Redirects (Under Search and Metadata) >> Add Redirect
- For Path, enter: "taxonomy/term/{term id}"
- For TO*, enter: "/data-literacy-series/search-view?dls_tags_advanced_id={Term Name}"
You have to do all these steps in order to make the link to the tag on the DLS page redirect to the correct page.