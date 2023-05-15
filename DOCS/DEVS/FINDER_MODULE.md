# Editing the Data Tool Explorer Page or the Finder2 page - Editing the Finder Module

### Using git: Go to web/modules/CD-Finder-2-master to edit the Finder2 Page, or web/modules/CD-finder-master to edit the Data Tools Explorer Page
### With SFTP Connection (Recommended) - Go to code/web/modules/CD-Finder-2-master or code/web/modules/CD-finder-master

### Also Recommend Starting your own Multi-Dev:  https://docs.pantheon.io/guides/multidev

## Edit Page content / text / template:
- In the directory for the module (Data Tools Explorer or Finder2 page), go to /templates/finder(2).html.twig
- Here is the HTML for the page.  If you are new to HTML, I recommend opening the corresponding web page on your browser (ie visiting the page Data Tools Explorer Page if that's the one you are editing), and see what text in the HTML corresponds to on the page, and change and edit the text.  

## Edit the Page CSS:
- Most of the CSS is in /css/finder.css

## Change Page Routing (ie Change the path of /finder2 to be /stat-tool-explorer)
- Open the /finder{2}.routing.yml file.  
- Change the value of 'path:' to the path that you want.  
- You can also change the path of where the settings form appears: under 'finder{2}.settings' in the same file, there is another path variable that you can change that corresponds to the settings.
