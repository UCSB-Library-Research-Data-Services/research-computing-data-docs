# Editing the Data Tool Explorer, Data Storage Finder, or Instrument Finder Pages - Editing the Finder Modules

We now have THREE finder pages, and all of them live in the same folder in the codebase: web/modules/custom/finders/
- Data Tool Explorer  -> web/modules/custom/finders/CD-finder-master (module name: 'finder')
- Data Storage Finder -> web/modules/custom/finders/CD-Finder-2-master (module name: 'finder2')
- Instrument Finder   -> web/modules/custom/finders/instrument_finder (module name: 'instrument_finder')

- To edit the Data Storage Finder page, it is the exact same as the Data Tool Explorer page, but most of the names have '2' at the end.  For example - Data Tool Explorer corresponds to 'finder', but Finder2 corresponds to 'finder2'.
- The Instrument Finder is a copy of the same module, but it works differently behind the scenes - see the "Instrument Finder" section at the bottom of this doc.

### Using git: Go to web/modules/custom/finders/ and pick the folder for the finder you want to edit
### With SFTP Connection (Recommended) - Go to code/web/modules/custom/finders/

### Also Recommend Starting your own Multi-Dev:  https://docs.pantheon.io/guides/multidev

## Edit Page content / text / template:
- In the directory for the module (Data Tools Explorer or Finder2 page), go to /templates/finder(2).html.twig
- Here is the HTML for the page.  If you are new to HTML, I recommend opening the corresponding web page on your browser (ie visiting the page Data Tools Explorer Page if that's the one you are editing), and see what text in the HTML corresponds to on the page, and change and edit the text.  
- To change the title, go to /finder{2}.routing.yml.  under finder(2).content: there is a variable called _title.  Change that variable value to your desired title.

## Edit the Page CSS:
- Most of the CSS is in /css/finder.css

## Change Page Routing (ie Change the path of /finder2 to be /stat-tool-explorer)
- Open the /finder{2}.routing.yml file.  
- Change the value of 'path:' to the path that you want.  
- You can also change the path of where the settings form appears: under 'finder{2}.settings' in the same file, there is another path variable that you can change that corresponds to the settings.

## More on what Finder and Finder2 are:
- They correspond to the Finder Module from Cornell University: https://finder.research.cornell.edu/.
- They did not intend for the Finder module to be used multiple times in a single website, so when installing their module, only 1 Finder page can be created.  It also creates content type: Service, Paragraph Type: Service Paragraphs, Taxonomy: Facets, and a Control Type vocabulary.  Their documentation is here: https://github.com/CU-CommunityApps/CD-finder
- To make Finder2, we had to edit the finder module to create a non-conflicting installation of a separate instance of the finder module.  To do that, we changed most of the names for nodes to have '2' at the end of it.  

# The Instrument Finder

The Instrument Finder (at /shared-instrumentation/instrument-finder) is the third copy of the finder module, made for the Shared Instrumentation part of the site.  The page looks and behaves like the other two finders, but the data side is DIFFERENT, so read this before assuming the Service2/Facet2 instructions apply.

## How it is different from Finder and Finder2:
- Finder and Finder2 get their data from their own content types (Service / Service2) and a Facets taxonomy that an editor maintains by hand.
- The Instrument Finder does NOT have its own content types or a Facets taxonomy.  Instead, it reads the regular 'Instrument' and 'Facility or Lab' content (the content types used by the Shared Instrumentation pages) and builds everything from those.
- This means there is nothing extra to maintain for the finder: when someone adds or edits an Instrument node in Drupal, the finder picks it up automatically.
- The answers under each question are collected automatically from the Department and Facility fields of all published Instrument nodes.  So to add a new answer, you don't touch a taxonomy - you just make sure an Instrument node references that department/facility.

## Where the comparison chart columns come from:
- Also built in code (same controller, see createinstrumentlist()).  The columns are: Department, Facility / Lab, Off-Campus Access, Primary Contact, and Website - each pulled from a field on the Instrument node.
- To add/remove a column, you edit the $s['field_data'] array in the controller.  (In Finder/Finder2 you would instead edit the Service Paragraphs paragraph type in the Drupal UI.)

## Editing the page text, CSS, and routing:
- Same idea as the other finders: the HTML is in templates/instrument_finder.html.twig, the CSS is in css/instrument_finder.css, and the path/title are in instrument_finder.routing.yml.
- Some of the headers/text can also be changed without code at the settings form: /admin/config/content/instrument-finder.

## The Statistics and Recently Added Instruments boxes:
- At the top of the Instrument Finder page there are two boxes: "Statistics" (facility and instrument counts) and "Recently Added Instruments".
- These are regular Drupal Views embedded into the finder page.  See 08_SHARED_INSTRUMENTATION.md for how they work and how to edit them.
