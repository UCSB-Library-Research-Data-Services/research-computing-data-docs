# Backups and Restores (Pantheon)

This document covers how the RCD site is backed up on Pantheon: what a backup contains, which environments are covered automatically, how to back up a Multidev environment (which is not covered by default), and how to restore. For the deployment flow these backups protect, see 06_UPSTREAM_UPDATES.md.

### What a Backup Contains

Pantheon backs up three parts of an environment, and each can be downloaded or restored separately:

* **Code** - the git repository: Drupal core, contrib modules, and our custom modules and theme.
* **Database** - all site content and configuration stored in the database: nodes (pages, instruments, facilities), users, menus, and views.
* **Files** - everything uploaded through Drupal: images, PDFs, and other media in the files directory.

Code lives in git and is therefore the easiest part to recover. The **database** is the part you cannot recreate from the repository, so it is the reason backups matter.

### Which Environments Are Covered

Pantheon runs backups **per environment**, and each environment has its own schedule:

* **Dev**, **Test**, and **Live** already have an automatic backup schedule configured. You do not need to do anything for these.
* **Multidev** environments (a feature branch environment such as `hk-02`) start with **no backup schedule at all**. A Multidev you create for your own work is not covered until you enable it yourself.

Pantheon warns you about this on the environment's **Status** tab

If you are working in a Multidev, treat that warning as a task to complete, not a notice to dismiss. Do both: take one manual backup now, then set a schedule so it keeps happening.

### Creating a Manual Backup

1. On the Pantheon dashboard, open the **researchdata-ucsb-edu-v01** site and select your environment (for example, the `hk-02` tab).
2. In the left sidebar, open **Backups >> History**.
3. In the **Create a new manual backup** panel, choose a retention period from the dropdown (for example, **Keep for 1 month**).
4. Click **Create**.

The backup takes a few minutes. When it finishes it appears in the **Backup History** table with its date, a type of **MANUAL**, and a separate download link for **Code**, **Database**, and **Files**, plus a **Restore** action.

### Scheduling Automatic Backups

A manual backup only protects the moment you took it. To keep an environment covered going forward:

1. Open **Backups >> Schedule**.
2. Set **Configure backups** to **Enabled**.
3. **Daily backup** stores code, database, and files for **one week**. The panel shows when the next daily backup will run.
4. **Weekly backup** stores code, database, and files for **one month**. Pick a day of the week from the dropdown.
5. Click **Update**.

### Retention

Know how long each kind of backup survives, so you do not assume something is still recoverable when it is not:

* **Daily** - kept for 1 week.
* **Weekly** - kept for 1 month.
* **Manual** - kept for the period you chose when you created it.

If you need to keep a specific state for longer than its retention window, download the backup files from the **Backup History** table and store them somewhere durable.

### When to Take a Manual Backup

Take one before any operation that is hard to undo, even on an environment that already has a schedule:

* Before applying upstream updates (see 06_UPSTREAM_UPDATES.md, which already asks you to back up **Live** first).
* Before a bulk content import, such as running the instrument importer (see 08_SHARED_INSTRUMENTATION.md).
* Before deleting content in bulk.
* Before restoring a backup, since a restore overwrites the environment.

The scheduled backup runs at a fixed time. Work you did since then is not protected, which is exactly what the "Work needs backup" warning is telling you.

### Restoring a Backup

1. Open **Backups >> History** for the environment you want to restore.
2. Find the backup by date and click **Restore**.
3. Confirm what you are restoring.

Two cautions:

* A restore **overwrites the current state** of that environment. Anything created since that backup is lost. Take a fresh manual backup first so you can get back to where you were.
* Restore into **Dev** or a **Multidev** to inspect the result when you can, rather than restoring **Live** directly. Live restores should be a last resort and worth telling your manager about.

Because code, database, and files are separate, you can restore just the part that broke. If a bad content import damaged content but the code is fine, restore only the **Database**.

### Related Docs

* 02_GIT_WORKFLOW.md - GitHub/Pantheon git integration and Multidev branching, which is how the environment you need to back up gets created.
* 06_UPSTREAM_UPDATES.md - the Dev -> Test -> Live workflow, which begins with a backup of Live.
* 08_SHARED_INSTRUMENTATION.md - the shared instrumentation features, including bulk imports worth backing up before.
