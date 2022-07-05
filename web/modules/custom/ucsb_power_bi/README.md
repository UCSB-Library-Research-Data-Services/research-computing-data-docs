# UCSB Power BI for Drupal 9

This module integrates with Microsoft Power BI to allow Drupal users to
embed Power BI reports with a new Paragraph type.

Row-level security can be implemented by adding Dataset ID and Role values
to the appropriate field in the Power BI Paragraph. Reports are fetched via
the Service Principal method using the Power BI REST API.

Steps to configure the UCSB Power BI module:

* Enable the UCSB Power BI module at /admin/modules.
* Existing(authenticated) users will be granted the "View Power BI Reports" permission by default when the module is installed.
* Add the permission "View Power BI Reports" to any specific user role(s).
* User allowed to access the reports need to be added to the specific role with "View Power BI Reports" permission.
* Add the Client ID, Tenant ID and Power BI secret at 
  /admin/config/ucsb_power_bi/ucsb_power_bi.
* Enable the new Paragraph type where you would like it to be available
  (e.g. /admin/structure/paragraphs_type/blade/fields/paragraph.blade.field_columns).
* Create a page containing the new Power BI Paragraph.

Please note that you will need to delete the PowerBI report field at /admin/structure/paragraphs_type/ucsb_power_bi/fields before you can uninstall the module. 
