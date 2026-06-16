# Analytics Workflow (Google Analytics to Jira)

This document provides a general overview of how site analytics are collected for the RCD website and how they are automatically reported into Jira each quarter. For the related site-monitoring tool, see 07_SITEIMPROVE.md.

### Analytics Overview

The RCD website (https://rcd.ucsb.edu/) is tracked with Google Analytics.

GA measures, among other things:

* Active users and new users (how many people visit, and how many are first-timers)
* Sessions and page views (how much the site is used)
* Average session duration and engagement rate (whether visitors actually read or leave quickly)
* Traffic acquisition (how people arrive: search, direct, or referral)

The raw dashboard lives at https://analytics.google.com/. It also benchmarks the site against a peer median for Computer Science sites.

### What the Metrics Mean

A quick guide for anyone new to GA4:

* **Active users** - distinct people who visited during the period.
* **New users** - of those, how many were here for the first time. When new users is close to active users, the site is reaching newcomers but not bringing visitors back.
* **Sessions** - individual visits. One user can have several sessions.
* **Page views** - total pages loaded. High views with low users means people browse deeply.
* **Average session duration** - how long a visit lasts on average.
* **Engagement rate** - the share of sessions that were "engaged" (lasted past a few seconds, triggered a key event, or viewed more than one page). Higher is better.

A number on its own says little. Always compare it to the previous quarter to see the direction of travel.

### The Automated Reporting Workflow

Rather than checking GA by hand, a Google Apps Script reports the key numbers into Jira automatically.

What it does, each quarter:

1. Pulls the trailing 90 days of GA4 metrics for the RCD property.
2. Creates a subtask under the RCD website issue in Jira (RDS-436), titled for example "Q3 2026 - RCD site analytics review".
3. Writes the metrics into the subtask description as a plain-text snapshot.

The subtask serves two purposes at once: it is the recurring reminder to review analytics, and it already contains the headline numbers.

The script runs inside Google Apps Script (https://script.google.com/) under a Google account that has access to the GA property. It relies on two services:

* **Google Analytics Data API** - an Apps Script advanced service (identifier `AnalyticsData`) that reads the GA4 report data.
* **Jira Cloud REST API v2** - creates the subtask. Version 2 is used so the description can be plain text; version 3 would require Atlassian Document Format.

### Schedule

The script is on a time-based trigger that fires on the 1st of every month but only acts in quarter-start months (January, April, July, October), producing four reports a year. The trigger is installed by running the `installQuarterlyTrigger` function once. It can be viewed or removed under the **Triggers** (alarm-clock) panel in the Apps Script editor.

### Configuration

The script's `CONFIG` block holds the non-secret settings:

* GA4 property - find the numeric Property ID under GA4 **Admin >> Property settings**. It is NOT the "G-XXXXXXX" Measurement ID.
* Jira site URL: https://ucsb-atlas.atlassian.net
* Parent issue RDS-436 and project key RDS.
* Subtask issue type name (Jira shows it as "Sub-task").
* The Atlassian account email used to authenticate.

The Jira API token is **not** stored in the script or in this repo. It lives in the Apps Script editor under **Project Settings >> Script properties**, with the key `JIRA_API_TOKEN`. Generate a token at https://id.atlassian.com/manage-profile/security/api-tokens. Never commit a token to GitHub.

### The Quarterly Review

When the analytics subtask appears under RDS-436:

1. Read the numbers already filled into the description.
2. Open GA4 (https://analytics.google.com/) and check **Reports >> Top pages** and **Traffic acquisition** for context the snapshot does not capture.
3. Note anything notable (a spike, a drop, a newly popular page) in the subtask.
4. Mark the subtask resolved.

### Related Docs

* 07_SITEIMPROVE.md - the other site-monitoring workflow.
* RDS-436 - the Jira issue for the RCD website, where the quarterly subtasks appear.
