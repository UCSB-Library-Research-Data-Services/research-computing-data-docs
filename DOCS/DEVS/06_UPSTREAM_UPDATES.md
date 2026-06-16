# UCSB Web Theme Upstream (Pantheon Integrated Composer)

This is a collaborative effort from SIS&T and the Office of Public Affairs & Communications to bring the campus one unified CMS template. The upstream was originally built for Drupal 9; our site (`researchdata-ucsb-edu-v01`) now runs Drupal 10 on the same upstream.

* SIS&T = Student Information Systems & Technology
* OPAC = Office of Public Affairs & Communications
* ETS = Pantheon admins for UCSB

We used Pantheon's recommended starting point for forking new Drupal upstreams that work with the Platform's Integrated Composer build process. The custom upstream includes UCSB's Web theme and several custom modules.

### You will need:

* A GitHub account connected to the [UCSB organization](https://github.com/ucsb/github-guide) if you plan on contributing to this upstream.

* A Pantheon account set up using the EDU pathway during [registration](https://pantheon.io/register).

* [Terminus, the Pantheon command line tool](https://pantheon.io/docs/terminus/install/) (recommended for developers).

* The codebase uses a CSS-preprocessor called Sass for our styles. We recommend all style changes be made using Sass and compiled accordingly.

* We use a custom development workflow while implementing new features for the upstream. Please contact us to request more information.

### About the upstream:

* Pantheon automatically runs Composer. The build results are added to your repository as a tag; they are not pushed to your development branch. When you update a site from the dashboard, Pantheon updates the git repository from the upstream and then runs Composer to get any updates to core, modules, or themes.

* You generally do not need to update core in the custom upstream any longer. Pantheon / Composer updates core when new versions are available, without requiring changes to the upstream.

* Do not require a different set of modules on different sites. This does not work well with Composer, which is designed to manage dependencies for a single application. Instead, include the superset of modules needed in the upstream, and only enable the ones needed on a given site.

* If you use any module that does not belong to UCSB's module set, keep it inside the `custom` folder so it is not lost when updating the upstream or moving to a newer Drupal version.

* If an upstream update fails, look in `composer.lock` and related files for commented-out code that, when uncommented, can help resolve the failure.

## Upstream Update Workflow

When SIS&T / OPAC publish new upstream updates, Pantheon makes them available to our site. Updates move through the environments in one direction only:

**Dev -> Test -> Live**

Never apply updates directly to Test or Live. Everything starts on Dev, gets verified, and is promoted forward. Active feature branches (Multidev) are then synced so they don't fall behind.

### Before you start

* Confirm the **Dev** environment is in **Git** connection mode, not SFTP (see 02_GIT_WORKFLOW.md). Applying updates in SFTP mode can discard uncommitted work.
* Commit or stash any pending work on Dev so nothing is lost.
* Take a backup of **Live** from the **Backups** tab so you can roll back if an update breaks the site.

### Step by step (Pantheon dashboard)

1. **See the updates (Dev).** Open the **Dev** environment, then the **Code** tab. When updates exist, Pantheon shows a count (for example, "9 updates available") with an **Apply updates** button. If it shows "Site is up-to-date," click **Check for updates** first.

2. **Set the update options and apply (Dev).** Next to **Apply updates**, open the **Update options** dropdown and check both boxes:
   * **Apply update.php** - runs Drupal's database updates for you, automatically, right after the code is applied. Always leave this checked.
   * **Auto-resolve conflicts** - resolves any conflicts in favor of the upstream Git repository. This is safe for us because we do not patch Drupal core.

   Then click **Apply updates**. Pantheon merges the upstream, runs Composer for any new core/module/theme versions, and (with the box checked) runs update.php.

3. **Check on Dev.** Click **Clear caches**, then open the **Dev site** and smoke-test: load the home page and a few key pages, and confirm the theme and finder modules still work (see 03_FINDER_MODULE.md, 08_SHARED_INSTRUMENTATION.md). The database updates already ran as part of step 2, so there is nothing to run by hand.

4. **Deploy Dev -> Test.** Open the **Test** environment, then the **Deploys** tab, and deploy the code from Dev. In the deploy panel, check **Run update.php** and **Clear caches**, and add a note such as "Update upstream." Optionally clone Live content and files down to Test so you test against realistic data, then click **Deploy**.

5. **Test on Test.** Re-check the same key pages on the **Test site**. Test is meant to mirror Live, so this is where you confirm the update is safe before it reaches the public site.

6. **Deploy Test -> Live.** Open the **Live** environment, then the **Deploys** tab, and deploy the code from Test. Check **Run update.php** and **Clear caches**, add a note, then click **Deploy**. Verify the live site.

7. **Sync any active Multidev.** A feature branch (Multidev, for example `hk-02`) that is mid-development is now behind Dev. Merge Dev into it so it picks up the upstream updates and avoids conflicts later. Do this only for Multidev environments that need to catch up.

### If an update fails

* Read the error in the dashboard workflow log to see which step failed.
* If "Auto-resolve conflicts" could not resolve everything, you will need to resolve the conflict manually: clone the repo, add Pantheon's upstream as a remote, merge, fix the conflict, and push. See 02_GIT_WORKFLOW.md for working with remotes.
* Check `composer.lock` and related files for commented-out code that can resolve failed upstream updates (see "About the upstream" above).
* Make sure any non-UCSB modules are inside the `custom` folder so they survive the update.

### Related docs

* 02_GIT_WORKFLOW.md - GitHub/Pantheon git integration, remotes, and Multidev branching.
* 03_FINDER_MODULE.md / 08_SHARED_INSTRUMENTATION.md - custom features to smoke-test after an update.
